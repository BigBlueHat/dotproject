<?php /* CLASSES $Id$ */
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}

/**
* @package dotproject
* @subpackage utilites
* @file date.class.php
* @brief An extension of the PEAR date class
*/

require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
/** @defgroup dateformatcontstants Date formatting constants */
/*@{*/
/** @enum FMT_DATEISO ISO Date Format, Example: 20070320T121545  */
/** @enum FMT_DATELDAP LDAP Date Format, Example: 20070320121615Z  */
/** @enum FMT_DATETIME_MYSQL MySQL Datetime Format, Example: 2007-03-20 12:17:07 */
/** @enum FMT_DATERFC822 RFC822 Date Format, Example: Tue, 20 Mar 2007 12:17:54 */
/** @enum FMT_TIMESTAMP Timestamp Format, Example: 20070320121837 */
/** @enum FMT_TIMESTAMP_DATE Timestamp Date-only Format, Example: 20070320 */
/** @enum FMT_TIMESTAMP_TIME Timestamp Time-only Format, Example: 122012 */
/*@}*/
define( 'FMT_DATEISO', '%Y%m%dT%H%M%S' );
define( 'FMT_DATELDAP', '%Y%m%d%H%M%SZ' );
define( 'FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S' );
define( 'FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S' );
define( 'FMT_TIMESTAMP', '%Y%m%d%H%M%S' );
define( 'FMT_TIMESTAMP_DATE', '%Y%m%d' );
define( 'FMT_TIMESTAMP_TIME', '%H%M%S' );
define( 'FMT_UNIX', '3' ); // This only actually outputs the number 3??
define( 'WDAY_SUNDAY',    0 );
define( 'WDAY_MONDAY',    1 );
define( 'WDAY_TUESDAY',   2 );
define( 'WDAY_WEDNESDAY',  3 );
define( 'WDAY_THURSDAY',  4 );
define( 'WDAY_FRIDAY',    5 );
define( 'WDAY_SATURDAY',  6 );
define( 'SEC_MINUTE',    60 );
define( 'SEC_HOUR',    3600 );
define( 'SEC_DAY',    86400 );

/**  
 * dotProject implementation of the Pear Date class
 *
 * This provides customised extensions to the Date class to leave the
 * Date package as 'pure' as possible
 */
class CDate extends Date {

	/** CDate constructor 
	 * @param $date A date in any of the supported formats, or NULL for todays date
	 */ 
	function CDate($date = null)
	{
		global $AppUI;
		
		$tz = $AppUI->getPref('TIMEZONE');
		
		$this->tz = new Date_TimeZone($tz);
		
		if (is_null($date)) {
			$this->setDate(date('Y-m-d H:i:s'));
		} elseif (is_object($date) && (get_class($date) == get_class($date))) {
			$this->setDate($date->getDate());
		} elseif (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $date)) {
    	$this->tz = new Date_TimeZone('UTC');
    	$d = new Date();
			$d->convertTZ(new Date_TimeZone('UTC'));
			$d->setDate($date);
			$this->setDate($d->getDate());
			if ($tz)
	 			$this->convertTZ(new Date_TimeZone($tz));	
		} else {
			parent::Date($date);
		}
	}

	/**
	 * Overloaded compare method
	 *
	 * The convertTZ calls are time intensive calls.  When a compare call is
	 * made in a recussive loop the lag can be significant.
	 * @param $d1 A date to compare 
	 * @param $d2 Date to compare to $d1
	 * @param $convertTZ Convert timezones of date parameters, default is false
	 * @return -1 if the second date is newer, 1 if the first date is newer, 0 if the dates are equal
	 */
	function compare($d1, $d2, $convertTZ=false)
	{
		if ($convertTZ) {
			$d1->convertTZ(new Date_TimeZone('UTC'));
			$d2->convertTZ(new Date_TimeZone('UTC'));
		}
		$days1 = Date_Calc::dateToDays($d1->day, $d1->month, $d1->year);
		$days2 = Date_Calc::dateToDays($d2->day, $d2->month, $d2->year);
		if($days1 < $days2) return -1;
		if($days1 > $days2) return 1;
		if($d1->hour < $d2->hour) return -1;
		if($d1->hour > $d2->hour) return 1;
		if($d1->minute < $d2->minute) return -1;
		if($d1->minute > $d2->minute) return 1;
		if($d1->second < $d2->second) return -1;
		if($d1->second > $d2->second) return 1;
		return 0;
	}

	/**
	 * Adds (+/-) a number of days to the current date.
	 * @param $n Positive or negative number of days
	 * @author J. Christopher Pereira <kripper@users.sf.net>
	 */
	function addDays( $n )
	{
		$timeStamp = $this->getTime();
		$oldHour = $this->getHour();
		$this->setDate( $timeStamp + SEC_DAY * ceil($n), DATE_FORMAT_UNIXTIME);
		
		if(($oldHour - $this->getHour()) || !is_int($n)) {
			$timeStamp += ($oldHour - $this->getHour()) * SEC_HOUR;
			$this->setDate( $timeStamp + SEC_DAY * $n, DATE_FORMAT_UNIXTIME);
		}
	}

	/**
	 * Adds (+/-) a number of months to the current date.
	 * @param $n Positive or negative number of months
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	function addMonths( $n )
	{
		$an = abs( $n );
		$years = floor( $an / 12 );
		$months = $an % 12;
		
		if ($n < 0) {
			$this->year -= $years;
			$this->month -= $months;
			if ($this->month < 1) {
				$this->year--;
				$this->month = 12 + $this->month;
			}
		} else {
			$this->year += $years;
			$this->month += $months;
			if ($this->month > 12) {
				$this->year++;
				$this->month -= 12;
			}
		}
	}

	/**
	 * New method to get the difference in days the stored date
	 * @param $when The date to compare to
	 * @return The difference in days
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	function dateDiff( $when ) 
	{
		return Date_calc::dateDiff(
			$this->getDay(), $this->getMonth(), $this->getYear(),
			$when->getDay(), $when->getMonth(), $when->getYear()
		);
	}

	/**
	 * New method that sets hour, minute and second in a single call
	 * @param $h hour
	 * @param $m minute
	 * @param $s second
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	function setTime( $h=0, $m=0, $s=0 )
	{
		$this->setHour( $h );
		$this->setMinute( $m );
		$this->setSecond( $s );
	}

	/** Determine if this date is a working day
	 * 
	 * Based on dotProjects configured working days
	 * @return Boolean indicating if this day is in the working week
	 */
	function isWorkingDay()
	{
	  global $AppUI;
	
	  $working_days = dPgetConfig('cal_working_days');
	  if(is_null($working_days)){
	    $working_days = array('1','2','3','4','5');
	  } else {
	    $working_days = explode(',', $working_days);
	  }
	
	  return in_array($this->getDayOfWeek(), $working_days);
	}

	/** Determine the 12 hour time suffix of this date
	 * @return "am" or "pm"
	 */
	function getAMPM()
	{
		if ( $this->getHour() > 11 ) {
			return "pm";
		} else {
			return "am";
		}
	}

	/**
	 * Get date for the end of the next working day
	 * @param $preserveHours Boolean, Determine whether to set time to start of day or preserve the time of the given object
	 * @return A date object set to the next working day
	 */ 
	function next_working_day( $preserveHours = false ) {
		global $AppUI;
		$do = $this;
		$end = intval(dPgetConfig('cal_day_end'));
		$start = intval(dPgetConfig('cal_day_start'));
		while ( ! $this->isWorkingDay() || $this->getHour() > $end ||
					( $preserveHours == false && $this->getHour() == $end && $this->getMinute() == '0' ) ) {
			$this->addDays(1);
			$this->setTime($start, '0', '0');
		}
		
		if ($preserveHours)
			$this->setTime($do->getHour(), '0', '0');
		
		return $this;
	}

	/**
	 *  Return date obj for the end of the previous working day
	 * @param $preserveHours Determine whether to set time to end of day or preserve the time of the given object
	 * @return A CDate object containing the previous working day
	 */ 
	function prev_working_day( $preserveHours = false ) {
		global $AppUI;
		$do = $this;
		$end = intval(dPgetConfig('cal_day_end'));
		$start = intval(dPgetConfig('cal_day_start'));
		while ( ! $this->isWorkingDay() || ( $this->getHour() < $start ) ||
					( $this->getHour() == $start && $this->getMinute() == '0' ) ) {
			$this->addDays(-1);
			$this->setTime($end, '0', '0');
		}
		if ($preserveHours)
			$this->setTime($do->getHour(), '0', '0');
		
		return $this;
	}

	/** 
	 * Calculating _robustly_ a date from a given date and duration
	 * Works in both directions: forwards/prospective and backwards/retrospective
	 * Respects non-working days
	 * @param	$duration	(positive = forward, negative = backward)
	 * @param	$durationType  1 = hour; 24 = day;
	 * @return	A Date object with the specified duration added.
	 */ 
	function addDuration( $duration = '8', $durationType ='1') {
		// using a sgn function lets us easily cover 
		// prospective and retrospective calcs at the same time
	
		// get signum of the duration
		$sgn = dPsgn($duration);
		
		// make duration positive
		$duration = abs($duration);
	
		// in case the duration type is 24 resp. full days
		// we're finished very quickly
		if ($durationType == '24') {
			$full_working_days = $duration;
		}
		
		// durationType is 1 hour
		else if ($durationType == '1') {
			// get dP time constants
	  	$cal_day_start = intval(dPgetConfig( 'cal_day_start' ));
	    $cal_day_end = intval(dPgetConfig( 'cal_day_end' ));
	    $dwh = intval(dPgetConfig( 'daily_working_hours' ));
	
			// move to the next working day if the first day is a non-working day
			($sgn > 0) ? $this->next_working_day() : $this->prev_working_day();
	
			// calculate the hours spent on the first day	
			$firstDay = ($sgn > 0) ? min($cal_day_end - $this->hour, $dwh) : min($this->hour - $cal_day_start, $dwh);
	
			/*
			** Catch some possible inconsistencies:
			** If we're later than cal_end_day or sooner than cal_start_day
			** just move by one day without subtracting any time from duration 
			*/
			if ($firstDay < 0)
				$firstDay = 0;
	
			// Intraday additions are handled easily by just changing the hour value
			if ($duration <= $firstDay) {
				($sgn > 0) ? $this->setHour($this->hour+$duration) : $this->setHour($this->hour-$duration);
				return $this;
			}
	
			// the effective first day hours value
			$firstAdj = min($dwh, $firstDay);
	
			// subtract the first day hours from the total duration
			$duration -= $firstAdj;
			
			// we've already processed the first day; move by one day!
			$this->addDays(1 * $sgn);
			
			// make sure that we didn't move to a non-working day
			($sgn > 0) ? $this->next_working_day() : $this->prev_working_day();
		
		// end of proceeding the first day
				
			// calc the remaining time and the full working days part of this residual
			$hoursRemaining = ($duration > $dwh) ? ($duration % $dwh) : $duration;
			$full_working_days = round(($duration - $hoursRemaining) / $dwh);
	    
	    // (proceed the full days later)
	
		// proceed the last day now
	
			// we prefer wed 16:00 over thu 08:00 as end date :)
			if ($hoursRemaining == 0){
				$full_working_days--;
				($sgn > 0) ? $this->setHour($cal_day_start+$dwh) : $this->setHour($cal_day_end-$dwh);
			} else
				($sgn > 0) ? $this->setHour($cal_day_start+$hoursRemaining) : $this->setHour($cal_day_end-$hoursRemaining);
		//end of proceeding the last day
	}
	
	// proceeding the fulldays finally which is easy
		// Full days
		for ( $i = 0 ; $i < $full_working_days ; $i++ ) {
			$this->addDays(1 * $sgn);
			if ( !$this->isWorkingDay() )
				// just 'ignore' this non-working day		
				$full_working_days++;
		}
	//end of proceeding the fulldays
		
		return $this->next_working_day();
	}

	/** 
	 * Calculating _robustly_ the working duration between two dates
	 *
	 * Works in both directions: forwards/prospective and backwards/retrospective
	 * Respects non-working days
	 *
	 *
	 * @param	$e	DateObject	may be viewed as end date
	 * @return	Working duration as an integer in hours
	 */ 
	function calcDuration($e) {
		
		// since one will alter the date ($this) one better copies it to a new instance
		$s = new CDate();
		$s->copy($this);
		
		// get dP time constants
		$cal_day_start = intval(dPgetConfig( 'cal_day_start' ));
		$cal_day_end = intval(dPgetConfig( 'cal_day_end' ));
		$dwh = intval(dPgetConfig( 'daily_working_hours' ));

		// assume start is before end and set a default signum for the duration	
		$sgn = 1;

		// check whether start before end, interchange otherwise
		if ($e->before($s)) {
			// calculated duration must be negative, set signum appropriately
			$sgn = -1;

			$dummy = $s;
			$s->copy($e);	
			$e = $dummy;
		}    
		
		// determine the (working + non-working) day difference between the two dates
		$days = $e->dateDiff($s);

		// if it is an intraday difference one is finished very easily
		if($days == 0)
			return min($dwh, abs($e->hour - $s->hour))*$sgn;

		// initialize the duration var
    $duration = 0;

	// process the first day
	
		// take into account the first day if it is a working day!
		$duration += $s->isWorkingDay() ? min($dwh, abs($cal_day_end - $s->hour)) : 0;
		$s->addDays(1);

	// end of processing the first day

		// calc workingdays between start and end
		for ($i=1; $i < $days; $i++) {
			$duration += $s->isWorkingDay() ? $dwh : 0;
			$s->addDays(1);
		}
		
		// take into account the last day in span only if it is a working day!
		$duration += $s->isWorkingDay() ? min($dwh, abs($e->hour - $cal_day_start)) : 0;

		return $duration*$sgn;
	}	

	/** Get the date as a string using specified formatting
	 * @param $format The formatting string
	 * @param $convert Convert to UTC timezone
	 * @return Formatted date string
	 */
	function format($format = null, $convert = null)
	{
		global $AppUI;
	
		$local_date = new Date();
		$local_date->copy($this);
		
		if (($format == FMT_DATETIME_MYSQL || $format == FMT_DATE_MYSQL) && $convert == null)
  		$local_date->convertTZ(new Date_TimeZone('UTC'));
  		
  	return $local_date->format($format);
	}

	/** Get the number of working days between this CDate object and another CDate object
	 * @param $e CDate object to compare to
	 * @return Number of working days as integer.
	 */
	function workingDaysInSpan($e){
		global $AppUI;
		
		// assume start is before end and set a default signum for the duration	
		$sgn = 1;

		// check whether start before end, interchange otherwise
		if ($e->before($this)) {
			// duration is negative, set signum appropriately
			$sgn = -1;
		}    
		
		$wd = 0;
		$days = $e->dateDiff($this);
		$start = $this;

		for ( $i = 0 ; $i <= $days ; $i++ ){
		        if ( $start->isWorkingDay())
		        	$wd++;
			$start->addDays(1 * $sgn);
		}

		return $wd;
	}
}
?>
