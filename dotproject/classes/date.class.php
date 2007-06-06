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

/** "YYYY-MM-DD HH:MM:SS" */
define('DATE_FORMAT_ISO', 1);
/** "YYYYMMDDHHMMSS" */
define('DATE_FORMAT_TIMESTAMP', 2);
/** long int, seconds since the unix epoch */
define('DATE_FORMAT_UNIXTIME', 3);

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
class CDate {

	/**
	 * the year
	 * @var int
	 */
	var $year;
	/**
	 * the month
	 * @var int
	 */
	var $month;
	/**
	 * the day
	 * @var int
	 */
	var $day;
	/**
	 * the hour
	 * @var int
	 */
	var $hour;
	/**
	 * the minute
	 * @var int
	 */
	var $minute;
	/**
	 * the second
	 * @var int
	 */
	var $second;
	/**
	 * timezone for this date
	 * @var object Date_TimeZone
	 */
	var $tz;
	
	/** CDate constructor 
	 * @param $date A date in any of the supported formats, or NULL for todays date
	 */ 
	function CDate($date = null)
	{
		global $AppUI;
		
		$tz = $AppUI->getPref('TIMEZONE');
		
		$this->setTZ($tz);
		
		if (empty($date)) {
			$this->setDate(date('Y-m-d H:i:s'));
		} elseif (is_object($date) && (get_class($this) == get_class($date))) {
			$this->setDate($date->getDate());
		} elseif (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $date)) {
    	$this->setTZ('UTC');
    	$d = new CDate();
			$d->convertTZ('UTC');
			$d->setDate($date);
			$this->setDate($d->getDate());
			if ($tz)
	 			$this->convertTZ($tz);	
		} elseif (is_null($date)) {
      $this->setDate(date('Y-m-d H:i:s'));
// following line has been modified by Andrew Eddie to support extending the Date class
    //} elseif (is_object($date) && (get_class($date) == 'date')) {
    } elseif (is_object($date) && (get_class($date) == get_class($this))) {
        $this->copy($date);
    } elseif (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $date)) {
        $this->setDate($date);
    } elseif (preg_match('/\d{14}/',$date)) {
        $this->setDate($date,DATE_FORMAT_TIMESTAMP);
    } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
        $this->setDate($date.' 00:00:00');
    } elseif (preg_match('/\d{8}/',$date)) {
        $this->setDate($date.'000000',DATE_FORMAT_TIMESTAMP);
    } else {
        $this->setDate($date,DATE_FORMAT_UNIXTIME);
    }
	}
	
	/**
	 * Copy values from another Date object
	 *
	 * Makes this Date a copy of another Date object.
	 *
	 * @access public
	 * @param object Date $date Date to copy from
	 */
	function copy($date)
	{
		$this->year = $date->year;
		$this->month = $date->month;
		$this->day = $date->day;
		$this->hour = $date->hour;
		$this->minute = $date->minute;
		$this->second = $date->second;
		$this->tz = $date->tz;
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
	function compare($d1, $d2 = null, $convertTZ=false)
	{
		if ($d2 === null) {
			$d2 = $d1;
			$d1 = new CDate($this);
			if ($convertTZ) {
				$d1->convertTZ('UTC');
				$d2->convertTZ('UTC');
			}
		}
		
		$days1 = $d1->getTime();
		$days2 = $d2->getTime();
		
		return floor(($days1 - $days2) / SEC_DAY);
	}
	
	/**
	 * Test if this date/time is before a certian date/time
	 *
	 * Test if this date/time is before a certian date/time
	 *
	 * @access public
	 * @param object Date $when the date to test against
	 * @return boolean true if this date is before $when
	 */
	function before($when)
	{
		return ($this->compare($this,$when) < 0);
	}
	
	function after($date)
	{
		return ($this->compare($this,$date) > 0);
	}
	
	function equals($date)
	{
		return ($this->compare($this, $date) == 0);
	}

	/**
	 * Get this date/time in Unix time() format
	 *
	 * Get a representation of this date in Unix time() format.  This may only be
	 * valid for dates from 1970 to ~2038.
	 *
	 * @access public
	 * @return int number of seconds since the unix epoch
	 */
	function getTime()
	{
		return $this->getDate(DATE_FORMAT_UNIXTIME);
	}

	/**
	 * Sets the time zone of this Date
	 *
	 * Sets the time zone of this date with the given
	 * Timezone id.  Does not alter the date/time,
	 * only assigns a new time zone.  For conversion, use
	 * convertTZ().
	 *
	 * @access public
	 * @param string $tz the timezone to use
	 */
	function setTZ($tz)
	{
		$tz_array = $GLOBALS['_DATE_TIMEZONE_DATA'][$tz];
		$tz_array['id'] = $tz;
		$this->tz = $tz_array;
	}
    
	/**
	 * Adds (+/-) a number of days to the current date.
	 * @param $n Positive or negative number of days
	 * @author J. Christopher Pereira <kripper@users.sf.net>
	 */
	function addDays( $n )
	{
		$timeStamp = $this->getTime();
		$oldHour = $this->hour;
		$this->setDate( $timeStamp + SEC_DAY * ceil($n), DATE_FORMAT_UNIXTIME);
		
		if(($oldHour - $this->hour) || !is_int($n)) {
			$timeStamp += ($oldHour - $this->hour) * SEC_HOUR;
			$this->setDate( $timeStamp + SEC_DAY * $n, DATE_FORMAT_UNIXTIME);
		}
	}
	
	function addSeconds( $n )
	{
		$this->setDate( $this->getTime() + $n, DATE_FORMAT_UNIXTIME);
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
	 * New method that sets hour, minute and second in a single call
	 * @param $h hour
	 * @param $m minute
	 * @param $s second
	 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
	 */
	function setTime( $h=0, $m=0, $s=0 )
	{
		$this->hour = $h;
		$this->minute = $m;
		$this->second = $s;
	}

	/** Determine if this date is a working day
	 * 
	 * Based on dotProjects configured working days
	 * @return Boolean indicating if this day is in the working week
	 */
	function isWorkingDay()
	{
	  $working_days = dPgetConfig('cal_working_days');
	  if(is_null($working_days)){
	    $working_days = array('1','2','3','4','5');
	  } else {
	    $working_days = explode(',', $working_days);
	  }
	
	  return in_array($this->format('%w'), $working_days);
	}

	/** Determine the 12 hour time suffix of this date
	 * @return "am" or "pm"
	 */
	function getAMPM()
	{
		if ( $this->hour > 11 ) {
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
		while ( ! $this->isWorkingDay() || $this->hour > $end ||
					( $preserveHours == false && $this->hour == $end && $this->minute == '0' ) ) {
			$this->addDays(1);
			$this->setTime($start, '0', '0');
		}
		
		if ($preserveHours)
			$this->setTime($do->hour, '0', '0');
		
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
		while ( ! $this->isWorkingDay() || ( $this->hour < $start ) ||
					( $this->hour == $start && $this->minute == '0' ) ) {
			$this->addDays(-1);
			$this->setTime($end, '0', '0');
		}
		if ($preserveHours)
			$this->setTime($do->hour, '0', '0');
		
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
		$days = $e->compare($s);

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
	/**
	 *  Date pretty printing, similar to strftime()
	 *
	 *  Formats the date in the given format, much like
	 *  strftime().  Most strftime() options are supported.<br><br>
	 *
	 *  formatting options:<br><br>
	 *
	 *  <code>%a  </code>  abbreviated weekday name (Sun, Mon, Tue) <br>
	 *  <code>%A  </code>  full weekday name (Sunday, Monday, Tuesday) <br>
	 *  <code>%b  </code>  abbreviated month name (Jan, Feb, Mar) <br>
	 *  <code>%B  </code>  full month name (January, February, March) <br>
	 *  <code>%C  </code>  century number (the year divided by 100 and truncated to an integer, range 00 to 99) <br>
	 *  <code>%d  </code>  day of month (range 00 to 31) <br>
	 *  <code>%D  </code>  same as "%m/%d/%y" <br>
	 *  <code>%e  </code>  day of month, single digit (range 0 to 31) <br>
	 *  <code>%E  </code>  number of days since unspecified epoch (integer) <br>
	 *  <code>%H  </code>  hour as decimal number (00 to 23) <br>
	 *  <code>%I  </code>  hour as decimal number on 12-hour clock (01 to 12) <br>
	 *  <code>%j  </code>  day of year (range 001 to 366) <br>
	 *  <code>%m  </code>  month as decimal number (range 01 to 12) <br>
	 *  <code>%M  </code>  minute as a decimal number (00 to 59) <br>
	 *  <code>%n  </code>  newline character (\n) <br>
	 *  <code>%O  </code>  dst-corrected timezone offset expressed as "+/-HH:MM" <br>
	 *  <code>%o  </code>  raw timezone offset expressed as "+/-HH:MM" <br>
	 *  <code>%p  </code>  either 'am' or 'pm' depending on the time <br>
	 *  <code>%P  </code>  either 'AM' or 'PM' depending on the time <br>
	 *  <code>%r  </code>  time in am/pm notation, same as "%I:%M:%S %p" <br>
	 *  <code>%R  </code>  time in 24-hour notation, same as "%H:%M" <br>
	 *  <code>%S  </code>  seconds as a decimal number (00 to 59) <br>
	 *  <code>%t  </code>  tab character (\t) <br>
	 *  <code>%T  </code>  current time, same as "%H:%M:%S" <br>
	 *  <code>%w  </code>  weekday as decimal (0 = Sunday) <br>
	 *  <code>%U  </code>  week number of current year, first sunday as first week <br>
	 *  <code>%y  </code>  year as decimal (range 00 to 99) <br>
	 *  <code>%Y  </code>  year as decimal including century (range 0000 to 9999) <br>
	 *  <code>%%  </code>  literal '%' <br>
	 * <br>
	 *
	 * @access public
	 * @param string format the format string for returned date/time
	 * @return string date/time in given format
	 */

	function format($format = null, $convert = null)
	{
		global $AppUI;
		
		$local_date = new CDate($this);
		
		if (($format == FMT_DATETIME_MYSQL || $format == FMT_DATE_MYSQL) && $convert == null)
  		$local_date->convertTZ('UTC');
		return strftime($format, mktime($local_date->hour, $local_date->minute, $local_date->second, $local_date->month, $local_date->day, $local_date->year));
	}

	/** Get the number of working days between this CDate object and another CDate object
	 * @param $e CDate object to compare to
	 * @return Number of working days as integer.
	 */
	function workingDaysInSpan($e){
		$wd = 0;

		$days = abs($e->compare($this));
		$start = $e->before($this) ? new CDate($e) : new CDate($this);
		for ( $i = 0 ; $i <= $days ; $i++ ){
			if ( $start->isWorkingDay())
				$wd++;
			$start->addDays(1);
		}

		return $wd;
	}
	
	/**
	 * Returns the list of valid time zone id strings
	 *
	 * Returns the list of valid time zone id strings
	 *
	 * @access public
	 * @return mixed an array of strings with the valid time zone IDs
	 */
	function getAvailableTimezones()
	{
		global $_DATE_TIMEZONE_DATA;
		return array_keys($_DATE_TIMEZONE_DATA);
	}
	
	function getWeekdayAbbrname($day = false) 
	{
		if ($day === false)
			$day = $this->format('%w');
		$time = strtotime('this Sunday');
		$time += SEC_DAY * $day;
		return strftime('%a', $time);
	}
	
	function getWeek()
	{
		$format = LOCALE_FIRST_DAY == 0 ? '%U' : '%W';
		return $this->format($format);
	}

    /**
     * Returns the year field of the date object
     *
     * Returns the year field of the date object
     *
     * @access public
     * @return int the year
     */
    function getYear()
    {
        return $this->year;
    }

    /**
     * Returns the month field of the date object
     *
     * Returns the month field of the date object
     *
     * @access public
     * @return int the month
     */
    function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns the day field of the date object
     *
     * Returns the day field of the date object
     *
     * @access public
     * @return int the day
     */
    function getDay()
    {
        return $this->day;
    }
    
    /**
     * Set the year field of the date object
     *
     * Set the year field of the date object, invalid years (not 0-9999) are set to 0.
     *
     * @access public
     * @param int $y the year
     */
    function setYear($y)
    {
        if($y < 0 || $y > 9999) {
            $this->year = 0;
        } else {
            $this->year = $y;
        }
    }

    /**
     * Set the month field of the date object
     *
     * Set the month field of the date object, invalid months (not 1-12) are set to 1.
     *
     * @access public
     * @param int $m the month
     */
    function setMonth($m)
    {
        if($m < 1 || $m > 12) {
            $this->month = 1;
        } else {
            $this->month = $m;
        }
    }

    /**
     * Set the fields of a Date object based on the input date and format
     *
     * Set the fields of a Date object based on the input date and format,
     * which is specified by the DATE_FORMAT_* constants.
     *
     * @access public
     * @param string $date input date
     * @param int $format format constant (DATE_FORMAT_*) of the input date
     */
    function setDate($date, $format = DATE_FORMAT_ISO)
    {
        switch($format) {
            case DATE_FORMAT_ISO:
                if (ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})",$date,$regs)) {
                    $this->year   = $regs[1];
                    $this->month  = $regs[2];
                    $this->day    = $regs[3];
                    $this->hour   = $regs[4];
                    $this->minute = $regs[5];
                    $this->second = $regs[6];
                } else {
                    $this->year   = 0;
                    $this->month  = 1;
                    $this->day    = 1;
                    $this->hour   = 0;
                    $this->minute = 0;
                    $this->second = 0;
                }
                break;
            case DATE_FORMAT_TIMESTAMP:
                if (ereg("([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})",$date,$regs)) {
                    $this->year   = $regs[1];
                    $this->month  = $regs[2];
                    $this->day    = $regs[3];
                    $this->hour   = $regs[4];
                    $this->minute = $regs[5];
                    $this->second = $regs[6];
                } else {
                    $this->year   = 0;
                    $this->month  = 1;
                    $this->day    = 1;
                    $this->hour   = 0;
                    $this->minute = 0;
                    $this->second = 0;
                }
                break;
            case DATE_FORMAT_UNIXTIME:
                $this->setDate(date("Y-m-d H:i:s", $date));
                break;
        }
    }
    
    /**
     * Get a string (or other) representation of this date
     *
     * Get a string (or other) representation of this date in the
     * format specified by the DATE_FORMAT_* constants.
     *
     * @access public
     * @param int $format format constant (DATE_FORMAT_*) of the output date
     * @return string the date in the requested format
     */
    function getDate($format = DATE_FORMAT_ISO)
    {
        switch($format) {
            case DATE_FORMAT_ISO:
                return $this->format("%Y-%m-%d %T");
                break;
            case DATE_FORMAT_TIMESTAMP:
                return $this->format("%Y%m%d%H%M%S");
                break;
            case DATE_FORMAT_UNIXTIME:
                return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
                break;
        }
    }
    
    /**
     * Set the day field of the date object
     *
     * Set the day field of the date object, invalid days (not 1-31) are set to 1.
     *
     * @access public
     * @param int $d the day
     */
    function setDay($d)
    {
        if($d > 31 || $d < 1) {
            $this->day = 1;
        } else {
            $this->day = $d;
        }
    }

    /**
     * Set the hour field of the date object
     *
     * Set the hour field of the date object in 24-hour format.
     * Invalid hours (not 0-23) are set to 0.
     *
     * @access public
     * @param int $h the hour
     */
    function setHour($h)
    {
        if($h > 23 || $h < 0) {
            $this->hour = 0;
        } else {
            $this->hour = $h;
        }
    }

    /**
     * Set the minute field of the date object
     *
     * Set the minute field of the date object, invalid minutes (not 0-59) are set to 0.
     *
     * @access public
     * @param int $m the minute
     */
    function setMinute($m)
    {
        if($m > 59 || $m < 0) {
            $this->minute = 0;
        } else {
            $this->minute = $m;
        }
    }

    /**
     * Set the second field of the date object
     *
     * Set the second field of the date object, invalid seconds (not 0-59) are set to 0.
     *
     * @access public
     * @param int $s the second
     */
    function setSecond($s) {
        if($s > 59 || $s < 0) {
            $this->second = 0;
        } else {
            $this->second = $s;
        }
    }
    
	function beginOfWeek()
	{
		$date = new CDate($this);
		$weekday = $date->format('%w');
		$date->addDays(LOCALE_FIRST_DAY - $weekday);
		
		return $date;
	}
	
	function getCalendarMonth($date)
	{
		$ts = $date->getTime();
		$daysInMonth = date('t', $ts);
		$monthDay = new CDate($date);
		$monthDay->setDay(1);
		$weekday = $monthDay->format('%w');
		$monthDay->addDays(LOCALE_FIRST_DAY - $weekday);
		$month = array();
		for ($day = 0; ($day < $daysInMonth + $weekday - LOCALE_FIRST_DAY || $day % 7 != 0); $day++) {
			$month[$day / 7][$day % 7] = $monthDay->format('%Y%m%d%w');
			$monthDay->addDays(1);
		}
				
		return $month;
	}
	
	/**
	 * Gets the full name or abbriviated name of this month
	 *
	 * Gets the full name or abbriviated name of this month
	 *
	 * @access public
	 * @param boolean $abbr abbrivate the name
	 * @return string name of this month
	 */
	function getMonthName($abbr = false)
	{
		$format = $abbr ? '%b' : '%B';
		return $this->format($format);
	}
	
	/**
	 * Gets the full name or abbriviated name of this weekday
	 *
	 * Gets the full name or abbriviated name of this weekday
	 *
	 * @access public
	 * @param boolean $abbr abbrivate the name
	 * @return string name of this day
	 */
	function getDayName($abbr = false)
	{
		$format = $abbr ? '%a' : '%A';
		
		return $this->format($format);
	}
	

	/**
	 * Converts this date to a new time zone
	 *
	 * Converts this date to a new time zone.
	 * WARNING: This may not work correctly if your system does not allow
	 * putenv() or if localtime() does not work in your environment. 
	 *
	 * @access public
	 * @param string $tz the time zone ID - index in $GLOBALS['_DATE_TIMEZONE_DATA']
	 */
	function convertTZ($tz)
	{
		// convert to UTC
		$offset = intval($this->tz['offset'] / 1000);
		if ($this->tz['hasdst'])
			$offset += 3600;
		$this->addSeconds(0 - $offset);
		// convert UTC to new timezone
		$offset = intval($GLOBALS['_DATE_TIMEZONE_DATA'][$tz]['offset'] / 1000);
		if ($this->tz['hasdst'])
			$offset += 3600;
		$this->addSeconds($offset);
		$this->setTZ($tz);
	}
}

// Not used
class CDateSpan {

}

$GLOBALS['_DATE_TIMEZONE_DATA'] = array(
    'Etc/GMT+12' => array(
        'offset' => -43200000,
        'longname' => "GMT-12:00",
        'shortname' => 'GMT-12:00',
        'hasdst' => false ),
    'Etc/GMT+11' => array(
        'offset' => -39600000,
        'longname' => "GMT-11:00",
        'shortname' => 'GMT-11:00',
        'hasdst' => false ),
    'MIT' => array(
        'offset' => -39600000,
        'longname' => "West Samoa Time",
        'shortname' => 'WST',
        'hasdst' => false ),
    'Pacific/Apia' => array(
        'offset' => -39600000,
        'longname' => "West Samoa Time",
        'shortname' => 'WST',
        'hasdst' => false ),
    'Pacific/Midway' => array(
        'offset' => -39600000,
        'longname' => "Samoa Standard Time",
        'shortname' => 'SST',
        'hasdst' => false ),
    'Pacific/Niue' => array(
        'offset' => -39600000,
        'longname' => "Niue Time",
        'shortname' => 'NUT',
        'hasdst' => false ),
    'Pacific/Pago_Pago' => array(
        'offset' => -39600000,
        'longname' => "Samoa Standard Time",
        'shortname' => 'SST',
        'hasdst' => false ),
    'Pacific/Samoa' => array(
        'offset' => -39600000,
        'longname' => "Samoa Standard Time",
        'shortname' => 'SST',
        'hasdst' => false ),
    'US/Samoa' => array(
        'offset' => -39600000,
        'longname' => "Samoa Standard Time",
        'shortname' => 'SST',
        'hasdst' => false ),
    'America/Adak' => array(
        'offset' => -36000000,
        'longname' => "Hawaii-Aleutian Standard Time",
        'shortname' => 'HAST',
        'hasdst' => true,
        'dstlongname' => "Hawaii-Aleutian Daylight Time",
        'dstshortname' => 'HADT' ),
    'America/Atka' => array(
        'offset' => -36000000,
        'longname' => "Hawaii-Aleutian Standard Time",
        'shortname' => 'HAST',
        'hasdst' => true,
        'dstlongname' => "Hawaii-Aleutian Daylight Time",
        'dstshortname' => 'HADT' ),
    'Etc/GMT+10' => array(
        'offset' => -36000000,
        'longname' => "GMT-10:00",
        'shortname' => 'GMT-10:00',
        'hasdst' => false ),
    'HST' => array(
        'offset' => -36000000,
        'longname' => "Hawaii Standard Time",
        'shortname' => 'HST',
        'hasdst' => false ),
    'Pacific/Fakaofo' => array(
        'offset' => -36000000,
        'longname' => "Tokelau Time",
        'shortname' => 'TKT',
        'hasdst' => false ),
    'Pacific/Honolulu' => array(
        'offset' => -36000000,
        'longname' => "Hawaii Standard Time",
        'shortname' => 'HST',
        'hasdst' => false ),
    'Pacific/Johnston' => array(
        'offset' => -36000000,
        'longname' => "Hawaii Standard Time",
        'shortname' => 'HST',
        'hasdst' => false ),
    'Pacific/Rarotonga' => array(
        'offset' => -36000000,
        'longname' => "Cook Is. Time",
        'shortname' => 'CKT',
        'hasdst' => false ),
    'Pacific/Tahiti' => array(
        'offset' => -36000000,
        'longname' => "Tahiti Time",
        'shortname' => 'TAHT',
        'hasdst' => false ),
    'SystemV/HST10' => array(
        'offset' => -36000000,
        'longname' => "Hawaii Standard Time",
        'shortname' => 'HST',
        'hasdst' => false ),
    'US/Aleutian' => array(
        'offset' => -36000000,
        'longname' => "Hawaii-Aleutian Standard Time",
        'shortname' => 'HAST',
        'hasdst' => true,
        'dstlongname' => "Hawaii-Aleutian Daylight Time",
        'dstshortname' => 'HADT' ),
    'US/Hawaii' => array(
        'offset' => -36000000,
        'longname' => "Hawaii Standard Time",
        'shortname' => 'HST',
        'hasdst' => false ),
    'Pacific/Marquesas' => array(
        'offset' => -34200000,
        'longname' => "Marquesas Time",
        'shortname' => 'MART',
        'hasdst' => false ),
    'AST' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'America/Anchorage' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'America/Juneau' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'America/Nome' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'America/Yakutat' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'Etc/GMT+9' => array(
        'offset' => -32400000,
        'longname' => "GMT-09:00",
        'shortname' => 'GMT-09:00',
        'hasdst' => false ),
    'Pacific/Gambier' => array(
        'offset' => -32400000,
        'longname' => "Gambier Time",
        'shortname' => 'GAMT',
        'hasdst' => false ),
    'SystemV/YST9' => array(
        'offset' => -32400000,
        'longname' => "Gambier Time",
        'shortname' => 'GAMT',
        'hasdst' => false ),
    'SystemV/YST9YDT' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'US/Alaska' => array(
        'offset' => -32400000,
        'longname' => "Alaska Standard Time",
        'shortname' => 'AKST',
        'hasdst' => true,
        'dstlongname' => "Alaska Daylight Time",
        'dstshortname' => 'AKDT' ),
    'America/Dawson' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Ensenada' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Los_Angeles' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Tijuana' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Vancouver' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Whitehorse' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'Canada/Pacific' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'Canada/Yukon' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'Etc/GMT+8' => array(
        'offset' => -28800000,
        'longname' => "GMT-08:00",
        'shortname' => 'GMT-08:00',
        'hasdst' => false ),
    'Mexico/BajaNorte' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'PST' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'PST8PDT' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'Pacific/Pitcairn' => array(
        'offset' => -28800000,
        'longname' => "Pitcairn Standard Time",
        'shortname' => 'PST',
        'hasdst' => false ),
    'SystemV/PST8' => array(
        'offset' => -28800000,
        'longname' => "Pitcairn Standard Time",
        'shortname' => 'PST',
        'hasdst' => false ),
    'SystemV/PST8PDT' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'US/Pacific' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'US/Pacific-New' => array(
        'offset' => -28800000,
        'longname' => "Pacific Standard Time",
        'shortname' => 'PST',
        'hasdst' => true,
        'dstlongname' => "Pacific Daylight Time",
        'dstshortname' => 'PDT' ),
    'America/Boise' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Cambridge_Bay' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Chihuahua' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Dawson_Creek' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'America/Denver' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Edmonton' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Hermosillo' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'America/Inuvik' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Mazatlan' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Phoenix' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'America/Shiprock' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Yellowknife' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'Canada/Mountain' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'Etc/GMT+7' => array(
        'offset' => -25200000,
        'longname' => "GMT-07:00",
        'shortname' => 'GMT-07:00',
        'hasdst' => false ),
    'MST' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'MST7MDT' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'Mexico/BajaSur' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'Navajo' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'PNT' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'SystemV/MST7' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'SystemV/MST7MDT' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'US/Arizona' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => false ),
    'US/Mountain' => array(
        'offset' => -25200000,
        'longname' => "Mountain Standard Time",
        'shortname' => 'MST',
        'hasdst' => true,
        'dstlongname' => "Mountain Daylight Time",
        'dstshortname' => 'MDT' ),
    'America/Belize' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Cancun' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Chicago' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Costa_Rica' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/El_Salvador' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Guatemala' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Managua' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Menominee' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Merida' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Mexico_City' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Monterrey' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/North_Dakota/Center' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Rainy_River' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Rankin_Inlet' => array(
        'offset' => -21600000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Regina' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Swift_Current' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Tegucigalpa' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'America/Winnipeg' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'CST' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'CST6CDT' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'Canada/Central' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'Canada/East-Saskatchewan' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Canada/Saskatchewan' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Chile/EasterIsland' => array(
        'offset' => -21600000,
        'longname' => "Easter Is. Time",
        'shortname' => 'EAST',
        'hasdst' => true,
        'dstlongname' => "Easter Is. Summer Time",
        'dstshortname' => 'EASST' ),
    'Etc/GMT+6' => array(
        'offset' => -21600000,
        'longname' => "GMT-06:00",
        'shortname' => 'GMT-06:00',
        'hasdst' => false ),
    'Mexico/General' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Pacific/Easter' => array(
        'offset' => -21600000,
        'longname' => "Easter Is. Time",
        'shortname' => 'EAST',
        'hasdst' => true,
        'dstlongname' => "Easter Is. Summer Time",
        'dstshortname' => 'EASST' ),
    'Pacific/Galapagos' => array(
        'offset' => -21600000,
        'longname' => "Galapagos Time",
        'shortname' => 'GALT',
        'hasdst' => false ),
    'SystemV/CST6' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'SystemV/CST6CDT' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'US/Central' => array(
        'offset' => -21600000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Bogota' => array(
        'offset' => -18000000,
        'longname' => "Colombia Time",
        'shortname' => 'COT',
        'hasdst' => false ),
    'America/Cayman' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Detroit' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Eirunepe' => array(
        'offset' => -18000000,
        'longname' => "Acre Time",
        'shortname' => 'ACT',
        'hasdst' => false ),
    'America/Fort_Wayne' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Grand_Turk' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Guayaquil' => array(
        'offset' => -18000000,
        'longname' => "Ecuador Time",
        'shortname' => 'ECT',
        'hasdst' => false ),
    'America/Havana' => array(
        'offset' => -18000000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'America/Indiana/Indianapolis' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Indiana/Knox' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Indiana/Marengo' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Indiana/Vevay' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Indianapolis' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Iqaluit' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Jamaica' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Kentucky/Louisville' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Kentucky/Monticello' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Knox_IN' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Lima' => array(
        'offset' => -18000000,
        'longname' => "Peru Time",
        'shortname' => 'PET',
        'hasdst' => false ),
    'America/Louisville' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Montreal' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Nassau' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/New_York' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Nipigon' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Panama' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Pangnirtung' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Port-au-Prince' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'America/Porto_Acre' => array(
        'offset' => -18000000,
        'longname' => "Acre Time",
        'shortname' => 'ACT',
        'hasdst' => false ),
    'America/Rio_Branco' => array(
        'offset' => -18000000,
        'longname' => "Acre Time",
        'shortname' => 'ACT',
        'hasdst' => false ),
    'America/Thunder_Bay' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'Brazil/Acre' => array(
        'offset' => -18000000,
        'longname' => "Acre Time",
        'shortname' => 'ACT',
        'hasdst' => false ),
    'Canada/Eastern' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'Cuba' => array(
        'offset' => -18000000,
        'longname' => "Central Standard Time",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Daylight Time",
        'dstshortname' => 'CDT' ),
    'EST' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'EST5EDT' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'Etc/GMT+5' => array(
        'offset' => -18000000,
        'longname' => "GMT-05:00",
        'shortname' => 'GMT-05:00',
        'hasdst' => false ),
    'IET' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'Jamaica' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'SystemV/EST5' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'SystemV/EST5EDT' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'US/East-Indiana' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'US/Eastern' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'US/Indiana-Starke' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => false ),
    'US/Michigan' => array(
        'offset' => -18000000,
        'longname' => "Eastern Standard Time",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Daylight Time",
        'dstshortname' => 'EDT' ),
    'America/Anguilla' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Antigua' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Aruba' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Asuncion' => array(
        'offset' => -14400000,
        'longname' => "Paraguay Time",
        'shortname' => 'PYT',
        'hasdst' => true,
        'dstlongname' => "Paraguay Summer Time",
        'dstshortname' => 'PYST' ),
    'America/Barbados' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Boa_Vista' => array(
        'offset' => -14400000,
        'longname' => "Amazon Standard Time",
        'shortname' => 'AMT',
        'hasdst' => false ),
    'America/Caracas' => array(
        'offset' => -14400000,
        'longname' => "Venezuela Time",
        'shortname' => 'VET',
        'hasdst' => false ),
    'America/Cuiaba' => array(
        'offset' => -14400000,
        'longname' => "Amazon Standard Time",
        'shortname' => 'AMT',
        'hasdst' => true,
        'dstlongname' => "Amazon Summer Time",
        'dstshortname' => 'AMST' ),
    'America/Curacao' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Dominica' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Glace_Bay' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'America/Goose_Bay' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'America/Grenada' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Guadeloupe' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Guyana' => array(
        'offset' => -14400000,
        'longname' => "Guyana Time",
        'shortname' => 'GYT',
        'hasdst' => false ),
    'America/Halifax' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'America/La_Paz' => array(
        'offset' => -14400000,
        'longname' => "Bolivia Time",
        'shortname' => 'BOT',
        'hasdst' => false ),
    'America/Manaus' => array(
        'offset' => -14400000,
        'longname' => "Amazon Standard Time",
        'shortname' => 'AMT',
        'hasdst' => false ),
    'America/Martinique' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Montserrat' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Port_of_Spain' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Porto_Velho' => array(
        'offset' => -14400000,
        'longname' => "Amazon Standard Time",
        'shortname' => 'AMT',
        'hasdst' => false ),
    'America/Puerto_Rico' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Santiago' => array(
        'offset' => -14400000,
        'longname' => "Chile Time",
        'shortname' => 'CLT',
        'hasdst' => true,
        'dstlongname' => "Chile Summer Time",
        'dstshortname' => 'CLST' ),
    'America/Santo_Domingo' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/St_Kitts' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/St_Lucia' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/St_Thomas' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/St_Vincent' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Thule' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Tortola' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'America/Virgin' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'Antarctica/Palmer' => array(
        'offset' => -14400000,
        'longname' => "Chile Time",
        'shortname' => 'CLT',
        'hasdst' => true,
        'dstlongname' => "Chile Summer Time",
        'dstshortname' => 'CLST' ),
    'Atlantic/Bermuda' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'Atlantic/Stanley' => array(
        'offset' => -14400000,
        'longname' => "Falkland Is. Time",
        'shortname' => 'FKT',
        'hasdst' => true,
        'dstlongname' => "Falkland Is. Summer Time",
        'dstshortname' => 'FKST' ),
    'Brazil/West' => array(
        'offset' => -14400000,
        'longname' => "Amazon Standard Time",
        'shortname' => 'AMT',
        'hasdst' => false ),
    'Canada/Atlantic' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'Chile/Continental' => array(
        'offset' => -14400000,
        'longname' => "Chile Time",
        'shortname' => 'CLT',
        'hasdst' => true,
        'dstlongname' => "Chile Summer Time",
        'dstshortname' => 'CLST' ),
    'Etc/GMT+4' => array(
        'offset' => -14400000,
        'longname' => "GMT-04:00",
        'shortname' => 'GMT-04:00',
        'hasdst' => false ),
    'PRT' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'SystemV/AST4' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'SystemV/AST4ADT' => array(
        'offset' => -14400000,
        'longname' => "Atlantic Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Atlantic Daylight Time",
        'dstshortname' => 'ADT' ),
    'America/St_Johns' => array(
        'offset' => -12600000,
        'longname' => "Newfoundland Standard Time",
        'shortname' => 'NST',
        'hasdst' => true,
        'dstlongname' => "Newfoundland Daylight Time",
        'dstshortname' => 'NDT' ),
    'CNT' => array(
        'offset' => -12600000,
        'longname' => "Newfoundland Standard Time",
        'shortname' => 'NST',
        'hasdst' => true,
        'dstlongname' => "Newfoundland Daylight Time",
        'dstshortname' => 'NDT' ),
    'Canada/Newfoundland' => array(
        'offset' => -12600000,
        'longname' => "Newfoundland Standard Time",
        'shortname' => 'NST',
        'hasdst' => true,
        'dstlongname' => "Newfoundland Daylight Time",
        'dstshortname' => 'NDT' ),
    'AGT' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Araguaina' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'America/Belem' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => false ),
    'America/Buenos_Aires' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Catamarca' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Cayenne' => array(
        'offset' => -10800000,
        'longname' => "French Guiana Time",
        'shortname' => 'GFT',
        'hasdst' => false ),
    'America/Cordoba' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Fortaleza' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'America/Godthab' => array(
        'offset' => -10800000,
        'longname' => "Western Greenland Time",
        'shortname' => 'WGT',
        'hasdst' => true,
        'dstlongname' => "Western Greenland Summer Time",
        'dstshortname' => 'WGST' ),
    'America/Jujuy' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Maceio' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'America/Mendoza' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Miquelon' => array(
        'offset' => -10800000,
        'longname' => "Pierre & Miquelon Standard Time",
        'shortname' => 'PMST',
        'hasdst' => true,
        'dstlongname' => "Pierre & Miquelon Daylight Time",
        'dstshortname' => 'PMDT' ),
    'America/Montevideo' => array(
        'offset' => -10800000,
        'longname' => "Uruguay Time",
        'shortname' => 'UYT',
        'hasdst' => false ),
    'America/Paramaribo' => array(
        'offset' => -10800000,
        'longname' => "Suriname Time",
        'shortname' => 'SRT',
        'hasdst' => false ),
    'America/Recife' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'America/Rosario' => array(
        'offset' => -10800000,
        'longname' => "Argentine Time",
        'shortname' => 'ART',
        'hasdst' => false ),
    'America/Sao_Paulo' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'BET' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'Brazil/East' => array(
        'offset' => -10800000,
        'longname' => "Brazil Time",
        'shortname' => 'BRT',
        'hasdst' => true,
        'dstlongname' => "Brazil Summer Time",
        'dstshortname' => 'BRST' ),
    'Etc/GMT+3' => array(
        'offset' => -10800000,
        'longname' => "GMT-03:00",
        'shortname' => 'GMT-03:00',
        'hasdst' => false ),
    'America/Noronha' => array(
        'offset' => -7200000,
        'longname' => "Fernando de Noronha Time",
        'shortname' => 'FNT',
        'hasdst' => false ),
    'Atlantic/South_Georgia' => array(
        'offset' => -7200000,
        'longname' => "South Georgia Standard Time",
        'shortname' => 'GST',
        'hasdst' => false ),
    'Brazil/DeNoronha' => array(
        'offset' => -7200000,
        'longname' => "Fernando de Noronha Time",
        'shortname' => 'FNT',
        'hasdst' => false ),
    'Etc/GMT+2' => array(
        'offset' => -7200000,
        'longname' => "GMT-02:00",
        'shortname' => 'GMT-02:00',
        'hasdst' => false ),
    'America/Scoresbysund' => array(
        'offset' => -3600000,
        'longname' => "Eastern Greenland Time",
        'shortname' => 'EGT',
        'hasdst' => true,
        'dstlongname' => "Eastern Greenland Summer Time",
        'dstshortname' => 'EGST' ),
    'Atlantic/Azores' => array(
        'offset' => -3600000,
        'longname' => "Azores Time",
        'shortname' => 'AZOT',
        'hasdst' => true,
        'dstlongname' => "Azores Summer Time",
        'dstshortname' => 'AZOST' ),
    'Atlantic/Cape_Verde' => array(
        'offset' => -3600000,
        'longname' => "Cape Verde Time",
        'shortname' => 'CVT',
        'hasdst' => false ),
    'Etc/GMT+1' => array(
        'offset' => -3600000,
        'longname' => "GMT-01:00",
        'shortname' => 'GMT-01:00',
        'hasdst' => false ),
    'Africa/Abidjan' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Accra' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Bamako' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Banjul' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Bissau' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Casablanca' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => false ),
    'Africa/Conakry' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Dakar' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/El_Aaiun' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => false ),
    'Africa/Freetown' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Lome' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Monrovia' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Nouakchott' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Ouagadougou' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Sao_Tome' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Africa/Timbuktu' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'America/Danmarkshavn' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Atlantic/Canary' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'Atlantic/Faeroe' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'Atlantic/Madeira' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'Atlantic/Reykjavik' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Atlantic/St_Helena' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Eire' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "Irish Summer Time",
        'dstshortname' => 'IST' ),
    'Etc/GMT' => array(
        'offset' => 0,
        'longname' => "GMT+00:00",
        'shortname' => 'GMT+00:00',
        'hasdst' => false ),
    'Etc/GMT+0' => array(
        'offset' => 0,
        'longname' => "GMT+00:00",
        'shortname' => 'GMT+00:00',
        'hasdst' => false ),
    'Etc/GMT-0' => array(
        'offset' => 0,
        'longname' => "GMT+00:00",
        'shortname' => 'GMT+00:00',
        'hasdst' => false ),
    'Etc/GMT0' => array(
        'offset' => 0,
        'longname' => "GMT+00:00",
        'shortname' => 'GMT+00:00',
        'hasdst' => false ),
    'Etc/Greenwich' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Etc/UCT' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Etc/UTC' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Etc/Universal' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Etc/Zulu' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Europe/Belfast' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "British Summer Time",
        'dstshortname' => 'BST' ),
    'Europe/Dublin' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "Irish Summer Time",
        'dstshortname' => 'IST' ),
    'Europe/Lisbon' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'Europe/London' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "British Summer Time",
        'dstshortname' => 'BST' ),
    'GB' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "British Summer Time",
        'dstshortname' => 'BST' ),
    'GB-Eire' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => true,
        'dstlongname' => "British Summer Time",
        'dstshortname' => 'BST' ),
    'GMT' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'GMT0' => array(
        'offset' => 0,
        'longname' => "GMT+00:00",
        'shortname' => 'GMT+00:00',
        'hasdst' => false ),
    'Greenwich' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Iceland' => array(
        'offset' => 0,
        'longname' => "Greenwich Mean Time",
        'shortname' => 'GMT',
        'hasdst' => false ),
    'Portugal' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'UCT' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'UTC' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Universal' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'WET' => array(
        'offset' => 0,
        'longname' => "Western European Time",
        'shortname' => 'WET',
        'hasdst' => true,
        'dstlongname' => "Western European Summer Time",
        'dstshortname' => 'WEST' ),
    'Zulu' => array(
        'offset' => 0,
        'longname' => "Coordinated Universal Time",
        'shortname' => 'UTC',
        'hasdst' => false ),
    'Africa/Algiers' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => false ),
    'Africa/Bangui' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Brazzaville' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Ceuta' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Africa/Douala' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Kinshasa' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Lagos' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Libreville' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Luanda' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Malabo' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Ndjamena' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Niamey' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Porto-Novo' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => false ),
    'Africa/Tunis' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => false ),
    'Africa/Windhoek' => array(
        'offset' => 3600000,
        'longname' => "Western African Time",
        'shortname' => 'WAT',
        'hasdst' => true,
        'dstlongname' => "Western African Summer Time",
        'dstshortname' => 'WAST' ),
    'Arctic/Longyearbyen' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Atlantic/Jan_Mayen' => array(
        'offset' => 3600000,
        'longname' => "Eastern Greenland Time",
        'shortname' => 'EGT',
        'hasdst' => true,
        'dstlongname' => "Eastern Greenland Summer Time",
        'dstshortname' => 'EGST' ),
    'CET' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'ECT' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Etc/GMT-1' => array(
        'offset' => 3600000,
        'longname' => "GMT+01:00",
        'shortname' => 'GMT+01:00',
        'hasdst' => false ),
    'Europe/Amsterdam' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Andorra' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Belgrade' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Berlin' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Bratislava' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Brussels' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Budapest' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Copenhagen' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Gibraltar' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Ljubljana' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Luxembourg' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Madrid' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Malta' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Monaco' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Oslo' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Paris' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Prague' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Rome' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/San_Marino' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Sarajevo' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Skopje' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Stockholm' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Tirane' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Vaduz' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Vatican' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Vienna' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Warsaw' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Zagreb' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'Europe/Zurich' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'MET' => array(
        'offset' => 3600000,
        'longname' => "Middle Europe Time",
        'shortname' => 'MET',
        'hasdst' => true,
        'dstlongname' => "Middle Europe Summer Time",
        'dstshortname' => 'MEST' ),
    'Poland' => array(
        'offset' => 3600000,
        'longname' => "Central European Time",
        'shortname' => 'CET',
        'hasdst' => true,
        'dstlongname' => "Central European Summer Time",
        'dstshortname' => 'CEST' ),
    'ART' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Africa/Blantyre' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Bujumbura' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Cairo' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Africa/Gaborone' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Harare' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Johannesburg' => array(
        'offset' => 7200000,
        'longname' => "South Africa Standard Time",
        'shortname' => 'SAST',
        'hasdst' => false ),
    'Africa/Kigali' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Lubumbashi' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Lusaka' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Maputo' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'Africa/Maseru' => array(
        'offset' => 7200000,
        'longname' => "South Africa Standard Time",
        'shortname' => 'SAST',
        'hasdst' => false ),
    'Africa/Mbabane' => array(
        'offset' => 7200000,
        'longname' => "South Africa Standard Time",
        'shortname' => 'SAST',
        'hasdst' => false ),
    'Africa/Tripoli' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => false ),
    'Asia/Amman' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Beirut' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Damascus' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Gaza' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Istanbul' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Jerusalem' => array(
        'offset' => 7200000,
        'longname' => "Israel Standard Time",
        'shortname' => 'IST',
        'hasdst' => true,
        'dstlongname' => "Israel Daylight Time",
        'dstshortname' => 'IDT' ),
    'Asia/Nicosia' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Asia/Tel_Aviv' => array(
        'offset' => 7200000,
        'longname' => "Israel Standard Time",
        'shortname' => 'IST',
        'hasdst' => true,
        'dstlongname' => "Israel Daylight Time",
        'dstshortname' => 'IDT' ),
    'CAT' => array(
        'offset' => 7200000,
        'longname' => "Central African Time",
        'shortname' => 'CAT',
        'hasdst' => false ),
    'EET' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Egypt' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Etc/GMT-2' => array(
        'offset' => 7200000,
        'longname' => "GMT+02:00",
        'shortname' => 'GMT+02:00',
        'hasdst' => false ),
    'Europe/Athens' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Bucharest' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Chisinau' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Helsinki' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Istanbul' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Kaliningrad' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Kiev' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Minsk' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Nicosia' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Riga' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Simferopol' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Sofia' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Tallinn' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => false ),
    'Europe/Tiraspol' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Uzhgorod' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Europe/Vilnius' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => false ),
    'Europe/Zaporozhye' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Israel' => array(
        'offset' => 7200000,
        'longname' => "Israel Standard Time",
        'shortname' => 'IST',
        'hasdst' => true,
        'dstlongname' => "Israel Daylight Time",
        'dstshortname' => 'IDT' ),
    'Libya' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => false ),
    'Turkey' => array(
        'offset' => 7200000,
        'longname' => "Eastern European Time",
        'shortname' => 'EET',
        'hasdst' => true,
        'dstlongname' => "Eastern European Summer Time",
        'dstshortname' => 'EEST' ),
    'Africa/Addis_Ababa' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Asmera' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Dar_es_Salaam' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Djibouti' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Kampala' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Khartoum' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Mogadishu' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Africa/Nairobi' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Antarctica/Syowa' => array(
        'offset' => 10800000,
        'longname' => "Syowa Time",
        'shortname' => 'SYOT',
        'hasdst' => false ),
    'Asia/Aden' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'Asia/Baghdad' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => true,
        'dstlongname' => "Arabia Daylight Time",
        'dstshortname' => 'ADT' ),
    'Asia/Bahrain' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'Asia/Kuwait' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'Asia/Qatar' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'Asia/Riyadh' => array(
        'offset' => 10800000,
        'longname' => "Arabia Standard Time",
        'shortname' => 'AST',
        'hasdst' => false ),
    'EAT' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Etc/GMT-3' => array(
        'offset' => 10800000,
        'longname' => "GMT+03:00",
        'shortname' => 'GMT+03:00',
        'hasdst' => false ),
    'Europe/Moscow' => array(
        'offset' => 10800000,
        'longname' => "Moscow Standard Time",
        'shortname' => 'MSK',
        'hasdst' => true,
        'dstlongname' => "Moscow Daylight Time",
        'dstshortname' => 'MSD' ),
    'Indian/Antananarivo' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Indian/Comoro' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'Indian/Mayotte' => array(
        'offset' => 10800000,
        'longname' => "Eastern African Time",
        'shortname' => 'EAT',
        'hasdst' => false ),
    'W-SU' => array(
        'offset' => 10800000,
        'longname' => "Moscow Standard Time",
        'shortname' => 'MSK',
        'hasdst' => true,
        'dstlongname' => "Moscow Daylight Time",
        'dstshortname' => 'MSD' ),
    'Asia/Riyadh87' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Asia/Riyadh88' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Asia/Riyadh89' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Mideast/Riyadh87' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Mideast/Riyadh88' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Mideast/Riyadh89' => array(
        'offset' => 11224000,
        'longname' => "GMT+03:07",
        'shortname' => 'GMT+03:07',
        'hasdst' => false ),
    'Asia/Tehran' => array(
        'offset' => 12600000,
        'longname' => "Iran Time",
        'shortname' => 'IRT',
        'hasdst' => true,
        'dstlongname' => "Iran Sumer Time",
        'dstshortname' => 'IRST' ),
    'Iran' => array(
        'offset' => 12600000,
        'longname' => "Iran Time",
        'shortname' => 'IRT',
        'hasdst' => true,
        'dstlongname' => "Iran Sumer Time",
        'dstshortname' => 'IRST' ),
    'Asia/Aqtau' => array(
        'offset' => 14400000,
        'longname' => "Aqtau Time",
        'shortname' => 'AQTT',
        'hasdst' => true,
        'dstlongname' => "Aqtau Summer Time",
        'dstshortname' => 'AQTST' ),
    'Asia/Baku' => array(
        'offset' => 14400000,
        'longname' => "Azerbaijan Time",
        'shortname' => 'AZT',
        'hasdst' => true,
        'dstlongname' => "Azerbaijan Summer Time",
        'dstshortname' => 'AZST' ),
    'Asia/Dubai' => array(
        'offset' => 14400000,
        'longname' => "Gulf Standard Time",
        'shortname' => 'GST',
        'hasdst' => false ),
    'Asia/Muscat' => array(
        'offset' => 14400000,
        'longname' => "Gulf Standard Time",
        'shortname' => 'GST',
        'hasdst' => false ),
    'Asia/Tbilisi' => array(
        'offset' => 14400000,
        'longname' => "Georgia Time",
        'shortname' => 'GET',
        'hasdst' => true,
        'dstlongname' => "Georgia Summer Time",
        'dstshortname' => 'GEST' ),
    'Asia/Yerevan' => array(
        'offset' => 14400000,
        'longname' => "Armenia Time",
        'shortname' => 'AMT',
        'hasdst' => true,
        'dstlongname' => "Armenia Summer Time",
        'dstshortname' => 'AMST' ),
    'Etc/GMT-4' => array(
        'offset' => 14400000,
        'longname' => "GMT+04:00",
        'shortname' => 'GMT+04:00',
        'hasdst' => false ),
    'Europe/Samara' => array(
        'offset' => 14400000,
        'longname' => "Samara Time",
        'shortname' => 'SAMT',
        'hasdst' => true,
        'dstlongname' => "Samara Summer Time",
        'dstshortname' => 'SAMST' ),
    'Indian/Mahe' => array(
        'offset' => 14400000,
        'longname' => "Seychelles Time",
        'shortname' => 'SCT',
        'hasdst' => false ),
    'Indian/Mauritius' => array(
        'offset' => 14400000,
        'longname' => "Mauritius Time",
        'shortname' => 'MUT',
        'hasdst' => false ),
    'Indian/Reunion' => array(
        'offset' => 14400000,
        'longname' => "Reunion Time",
        'shortname' => 'RET',
        'hasdst' => false ),
    'NET' => array(
        'offset' => 14400000,
        'longname' => "Armenia Time",
        'shortname' => 'AMT',
        'hasdst' => true,
        'dstlongname' => "Armenia Summer Time",
        'dstshortname' => 'AMST' ),
    'Asia/Kabul' => array(
        'offset' => 16200000,
        'longname' => "Afghanistan Time",
        'shortname' => 'AFT',
        'hasdst' => false ),
    'Asia/Aqtobe' => array(
        'offset' => 18000000,
        'longname' => "Aqtobe Time",
        'shortname' => 'AQTT',
        'hasdst' => true,
        'dstlongname' => "Aqtobe Summer Time",
        'dstshortname' => 'AQTST' ),
    'Asia/Ashgabat' => array(
        'offset' => 18000000,
        'longname' => "Turkmenistan Time",
        'shortname' => 'TMT',
        'hasdst' => false ),
    'Asia/Ashkhabad' => array(
        'offset' => 18000000,
        'longname' => "Turkmenistan Time",
        'shortname' => 'TMT',
        'hasdst' => false ),
    'Asia/Bishkek' => array(
        'offset' => 18000000,
        'longname' => "Kirgizstan Time",
        'shortname' => 'KGT',
        'hasdst' => true,
        'dstlongname' => "Kirgizstan Summer Time",
        'dstshortname' => 'KGST' ),
    'Asia/Dushanbe' => array(
        'offset' => 18000000,
        'longname' => "Tajikistan Time",
        'shortname' => 'TJT',
        'hasdst' => false ),
    'Asia/Karachi' => array(
        'offset' => 18000000,
        'longname' => "Pakistan Time",
        'shortname' => 'PKT',
        'hasdst' => false ),
    'Asia/Samarkand' => array(
        'offset' => 18000000,
        'longname' => "Turkmenistan Time",
        'shortname' => 'TMT',
        'hasdst' => false ),
    'Asia/Tashkent' => array(
        'offset' => 18000000,
        'longname' => "Uzbekistan Time",
        'shortname' => 'UZT',
        'hasdst' => false ),
    'Asia/Yekaterinburg' => array(
        'offset' => 18000000,
        'longname' => "Yekaterinburg Time",
        'shortname' => 'YEKT',
        'hasdst' => true,
        'dstlongname' => "Yekaterinburg Summer Time",
        'dstshortname' => 'YEKST' ),
    'Etc/GMT-5' => array(
        'offset' => 18000000,
        'longname' => "GMT+05:00",
        'shortname' => 'GMT+05:00',
        'hasdst' => false ),
    'Indian/Kerguelen' => array(
        'offset' => 18000000,
        'longname' => "French Southern & Antarctic Lands Time",
        'shortname' => 'TFT',
        'hasdst' => false ),
    'Indian/Maldives' => array(
        'offset' => 18000000,
        'longname' => "Maldives Time",
        'shortname' => 'MVT',
        'hasdst' => false ),
    'PLT' => array(
        'offset' => 18000000,
        'longname' => "Pakistan Time",
        'shortname' => 'PKT',
        'hasdst' => false ),
    'Asia/Calcutta' => array(
        'offset' => 19800000,
        'longname' => "India Standard Time",
        'shortname' => 'IST',
        'hasdst' => false ),
    'IST' => array(
        'offset' => 19800000,
        'longname' => "India Standard Time",
        'shortname' => 'IST',
        'hasdst' => false ),
    'Asia/Katmandu' => array(
        'offset' => 20700000,
        'longname' => "Nepal Time",
        'shortname' => 'NPT',
        'hasdst' => false ),
    'Antarctica/Mawson' => array(
        'offset' => 21600000,
        'longname' => "Mawson Time",
        'shortname' => 'MAWT',
        'hasdst' => false ),
    'Antarctica/Vostok' => array(
        'offset' => 21600000,
        'longname' => "Vostok time",
        'shortname' => 'VOST',
        'hasdst' => false ),
    'Asia/Almaty' => array(
        'offset' => 21600000,
        'longname' => "Alma-Ata Time",
        'shortname' => 'ALMT',
        'hasdst' => true,
        'dstlongname' => "Alma-Ata Summer Time",
        'dstshortname' => 'ALMST' ),
    'Asia/Colombo' => array(
        'offset' => 21600000,
        'longname' => "Sri Lanka Time",
        'shortname' => 'LKT',
        'hasdst' => false ),
    'Asia/Dacca' => array(
        'offset' => 21600000,
        'longname' => "Bangladesh Time",
        'shortname' => 'BDT',
        'hasdst' => false ),
    'Asia/Dhaka' => array(
        'offset' => 21600000,
        'longname' => "Bangladesh Time",
        'shortname' => 'BDT',
        'hasdst' => false ),
    'Asia/Novosibirsk' => array(
        'offset' => 21600000,
        'longname' => "Novosibirsk Time",
        'shortname' => 'NOVT',
        'hasdst' => true,
        'dstlongname' => "Novosibirsk Summer Time",
        'dstshortname' => 'NOVST' ),
    'Asia/Omsk' => array(
        'offset' => 21600000,
        'longname' => "Omsk Time",
        'shortname' => 'OMST',
        'hasdst' => true,
        'dstlongname' => "Omsk Summer Time",
        'dstshortname' => 'OMSST' ),
    'Asia/Thimbu' => array(
        'offset' => 21600000,
        'longname' => "Bhutan Time",
        'shortname' => 'BTT',
        'hasdst' => false ),
    'Asia/Thimphu' => array(
        'offset' => 21600000,
        'longname' => "Bhutan Time",
        'shortname' => 'BTT',
        'hasdst' => false ),
    'BST' => array(
        'offset' => 21600000,
        'longname' => "Bangladesh Time",
        'shortname' => 'BDT',
        'hasdst' => false ),
    'Etc/GMT-6' => array(
        'offset' => 21600000,
        'longname' => "GMT+06:00",
        'shortname' => 'GMT+06:00',
        'hasdst' => false ),
    'Indian/Chagos' => array(
        'offset' => 21600000,
        'longname' => "Indian Ocean Territory Time",
        'shortname' => 'IOT',
        'hasdst' => false ),
    'Asia/Rangoon' => array(
        'offset' => 23400000,
        'longname' => "Myanmar Time",
        'shortname' => 'MMT',
        'hasdst' => false ),
    'Indian/Cocos' => array(
        'offset' => 23400000,
        'longname' => "Cocos Islands Time",
        'shortname' => 'CCT',
        'hasdst' => false ),
    'Antarctica/Davis' => array(
        'offset' => 25200000,
        'longname' => "Davis Time",
        'shortname' => 'DAVT',
        'hasdst' => false ),
    'Asia/Bangkok' => array(
        'offset' => 25200000,
        'longname' => "Indochina Time",
        'shortname' => 'ICT',
        'hasdst' => false ),
    'Asia/Hovd' => array(
        'offset' => 25200000,
        'longname' => "Hovd Time",
        'shortname' => 'HOVT',
        'hasdst' => false ),
    'Asia/Jakarta' => array(
        'offset' => 25200000,
        'longname' => "West Indonesia Time",
        'shortname' => 'WIT',
        'hasdst' => false ),
    'Asia/Krasnoyarsk' => array(
        'offset' => 25200000,
        'longname' => "Krasnoyarsk Time",
        'shortname' => 'KRAT',
        'hasdst' => true,
        'dstlongname' => "Krasnoyarsk Summer Time",
        'dstshortname' => 'KRAST' ),
    'Asia/Phnom_Penh' => array(
        'offset' => 25200000,
        'longname' => "Indochina Time",
        'shortname' => 'ICT',
        'hasdst' => false ),
    'Asia/Pontianak' => array(
        'offset' => 25200000,
        'longname' => "West Indonesia Time",
        'shortname' => 'WIT',
        'hasdst' => false ),
    'Asia/Saigon' => array(
        'offset' => 25200000,
        'longname' => "Indochina Time",
        'shortname' => 'ICT',
        'hasdst' => false ),
    'Asia/Vientiane' => array(
        'offset' => 25200000,
        'longname' => "Indochina Time",
        'shortname' => 'ICT',
        'hasdst' => false ),
    'Etc/GMT-7' => array(
        'offset' => 25200000,
        'longname' => "GMT+07:00",
        'shortname' => 'GMT+07:00',
        'hasdst' => false ),
    'Indian/Christmas' => array(
        'offset' => 25200000,
        'longname' => "Christmas Island Time",
        'shortname' => 'CXT',
        'hasdst' => false ),
    'VST' => array(
        'offset' => 25200000,
        'longname' => "Indochina Time",
        'shortname' => 'ICT',
        'hasdst' => false ),
    'Antarctica/Casey' => array(
        'offset' => 28800000,
        'longname' => "Western Standard Time (Australia)",
        'shortname' => 'WST',
        'hasdst' => false ),
    'Asia/Brunei' => array(
        'offset' => 28800000,
        'longname' => "Brunei Time",
        'shortname' => 'BNT',
        'hasdst' => false ),
    'Asia/Chongqing' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Chungking' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Harbin' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Hong_Kong' => array(
        'offset' => 28800000,
        'longname' => "Hong Kong Time",
        'shortname' => 'HKT',
        'hasdst' => false ),
    'Asia/Irkutsk' => array(
        'offset' => 28800000,
        'longname' => "Irkutsk Time",
        'shortname' => 'IRKT',
        'hasdst' => true,
        'dstlongname' => "Irkutsk Summer Time",
        'dstshortname' => 'IRKST' ),
    'Asia/Kashgar' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Kuala_Lumpur' => array(
        'offset' => 28800000,
        'longname' => "Malaysia Time",
        'shortname' => 'MYT',
        'hasdst' => false ),
    'Asia/Kuching' => array(
        'offset' => 28800000,
        'longname' => "Malaysia Time",
        'shortname' => 'MYT',
        'hasdst' => false ),
    'Asia/Macao' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Manila' => array(
        'offset' => 28800000,
        'longname' => "Philippines Time",
        'shortname' => 'PHT',
        'hasdst' => false ),
    'Asia/Shanghai' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Singapore' => array(
        'offset' => 28800000,
        'longname' => "Singapore Time",
        'shortname' => 'SGT',
        'hasdst' => false ),
    'Asia/Taipei' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Asia/Ujung_Pandang' => array(
        'offset' => 28800000,
        'longname' => "Central Indonesia Time",
        'shortname' => 'CIT',
        'hasdst' => false ),
    'Asia/Ulaanbaatar' => array(
        'offset' => 28800000,
        'longname' => "Ulaanbaatar Time",
        'shortname' => 'ULAT',
        'hasdst' => false ),
    'Asia/Ulan_Bator' => array(
        'offset' => 28800000,
        'longname' => "Ulaanbaatar Time",
        'shortname' => 'ULAT',
        'hasdst' => false ),
    'Asia/Urumqi' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Australia/Perth' => array(
        'offset' => 28800000,
        'longname' => "Western Standard Time (Australia)",
        'shortname' => 'WST',
        'hasdst' => false ),
    'Australia/West' => array(
        'offset' => 28800000,
        'longname' => "Western Standard Time (Australia)",
        'shortname' => 'WST',
        'hasdst' => false ),
    'CTT' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Etc/GMT-8' => array(
        'offset' => 28800000,
        'longname' => "GMT+08:00",
        'shortname' => 'GMT+08:00',
        'hasdst' => false ),
    'Hongkong' => array(
        'offset' => 28800000,
        'longname' => "Hong Kong Time",
        'shortname' => 'HKT',
        'hasdst' => false ),
    'PRC' => array(
        'offset' => 28800000,
        'longname' => "China Standard Time",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Singapore' => array(
        'offset' => 28800000,
        'longname' => "Singapore Time",
        'shortname' => 'SGT',
        'hasdst' => false ),
    'Asia/Choibalsan' => array(
        'offset' => 32400000,
        'longname' => "Choibalsan Time",
        'shortname' => 'CHOT',
        'hasdst' => false ),
    'Asia/Dili' => array(
        'offset' => 32400000,
        'longname' => "East Timor Time",
        'shortname' => 'TPT',
        'hasdst' => false ),
    'Asia/Jayapura' => array(
        'offset' => 32400000,
        'longname' => "East Indonesia Time",
        'shortname' => 'EIT',
        'hasdst' => false ),
    'Asia/Pyongyang' => array(
        'offset' => 32400000,
        'longname' => "Korea Standard Time",
        'shortname' => 'KST',
        'hasdst' => false ),
    'Asia/Seoul' => array(
        'offset' => 32400000,
        'longname' => "Korea Standard Time",
        'shortname' => 'KST',
        'hasdst' => false ),
    'Asia/Tokyo' => array(
        'offset' => 32400000,
        'longname' => "Japan Standard Time",
        'shortname' => 'JST',
        'hasdst' => false ),
    'Asia/Yakutsk' => array(
        'offset' => 32400000,
        'longname' => "Yakutsk Time",
        'shortname' => 'YAKT',
        'hasdst' => true,
        'dstlongname' => "Yaktsk Summer Time",
        'dstshortname' => 'YAKST' ),
    'Etc/GMT-9' => array(
        'offset' => 32400000,
        'longname' => "GMT+09:00",
        'shortname' => 'GMT+09:00',
        'hasdst' => false ),
    'JST' => array(
        'offset' => 32400000,
        'longname' => "Japan Standard Time",
        'shortname' => 'JST',
        'hasdst' => false ),
    'Japan' => array(
        'offset' => 32400000,
        'longname' => "Japan Standard Time",
        'shortname' => 'JST',
        'hasdst' => false ),
    'Pacific/Palau' => array(
        'offset' => 32400000,
        'longname' => "Palau Time",
        'shortname' => 'PWT',
        'hasdst' => false ),
    'ROK' => array(
        'offset' => 32400000,
        'longname' => "Korea Standard Time",
        'shortname' => 'KST',
        'hasdst' => false ),
    'ACT' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (Northern Territory)",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Australia/Adelaide' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (South Australia)",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Summer Time (South Australia)",
        'dstshortname' => 'CST' ),
    'Australia/Broken_Hill' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (South Australia/New South Wales)",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Summer Time (South Australia/New South Wales)",
        'dstshortname' => 'CST' ),
    'Australia/Darwin' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (Northern Territory)",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Australia/North' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (Northern Territory)",
        'shortname' => 'CST',
        'hasdst' => false ),
    'Australia/South' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (South Australia)",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Summer Time (South Australia)",
        'dstshortname' => 'CST' ),
    'Australia/Yancowinna' => array(
        'offset' => 34200000,
        'longname' => "Central Standard Time (South Australia/New South Wales)",
        'shortname' => 'CST',
        'hasdst' => true,
        'dstlongname' => "Central Summer Time (South Australia/New South Wales)",
        'dstshortname' => 'CST' ),
    'AET' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (New South Wales)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (New South Wales)",
        'dstshortname' => 'EST' ),
    'Antarctica/DumontDUrville' => array(
        'offset' => 36000000,
        'longname' => "Dumont-d'Urville Time",
        'shortname' => 'DDUT',
        'hasdst' => false ),
    'Asia/Sakhalin' => array(
        'offset' => 36000000,
        'longname' => "Sakhalin Time",
        'shortname' => 'SAKT',
        'hasdst' => true,
        'dstlongname' => "Sakhalin Summer Time",
        'dstshortname' => 'SAKST' ),
    'Asia/Vladivostok' => array(
        'offset' => 36000000,
        'longname' => "Vladivostok Time",
        'shortname' => 'VLAT',
        'hasdst' => true,
        'dstlongname' => "Vladivostok Summer Time",
        'dstshortname' => 'VLAST' ),
    'Australia/ACT' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (New South Wales)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (New South Wales)",
        'dstshortname' => 'EST' ),
    'Australia/Brisbane' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Queensland)",
        'shortname' => 'EST',
        'hasdst' => false ),
    'Australia/Canberra' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (New South Wales)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (New South Wales)",
        'dstshortname' => 'EST' ),
    'Australia/Hobart' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Tasmania)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (Tasmania)",
        'dstshortname' => 'EST' ),
    'Australia/Lindeman' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Queensland)",
        'shortname' => 'EST',
        'hasdst' => false ),
    'Australia/Melbourne' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Victoria)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (Victoria)",
        'dstshortname' => 'EST' ),
    'Australia/NSW' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (New South Wales)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (New South Wales)",
        'dstshortname' => 'EST' ),
    'Australia/Queensland' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Queensland)",
        'shortname' => 'EST',
        'hasdst' => false ),
    'Australia/Sydney' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (New South Wales)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (New South Wales)",
        'dstshortname' => 'EST' ),
    'Australia/Tasmania' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Tasmania)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (Tasmania)",
        'dstshortname' => 'EST' ),
    'Australia/Victoria' => array(
        'offset' => 36000000,
        'longname' => "Eastern Standard Time (Victoria)",
        'shortname' => 'EST',
        'hasdst' => true,
        'dstlongname' => "Eastern Summer Time (Victoria)",
        'dstshortname' => 'EST' ),
    'Etc/GMT-10' => array(
        'offset' => 36000000,
        'longname' => "GMT+10:00",
        'shortname' => 'GMT+10:00',
        'hasdst' => false ),
    'Pacific/Guam' => array(
        'offset' => 36000000,
        'longname' => "Chamorro Standard Time",
        'shortname' => 'ChST',
        'hasdst' => false ),
    'Pacific/Port_Moresby' => array(
        'offset' => 36000000,
        'longname' => "Papua New Guinea Time",
        'shortname' => 'PGT',
        'hasdst' => false ),
    'Pacific/Saipan' => array(
        'offset' => 36000000,
        'longname' => "Chamorro Standard Time",
        'shortname' => 'ChST',
        'hasdst' => false ),
    'Pacific/Truk' => array(
        'offset' => 36000000,
        'longname' => "Truk Time",
        'shortname' => 'TRUT',
        'hasdst' => false ),
    'Pacific/Yap' => array(
        'offset' => 36000000,
        'longname' => "Yap Time",
        'shortname' => 'YAPT',
        'hasdst' => false ),
    'Australia/LHI' => array(
        'offset' => 37800000,
        'longname' => "Load Howe Standard Time",
        'shortname' => 'LHST',
        'hasdst' => true,
        'dstlongname' => "Load Howe Summer Time",
        'dstshortname' => 'LHST' ),
    'Australia/Lord_Howe' => array(
        'offset' => 37800000,
        'longname' => "Load Howe Standard Time",
        'shortname' => 'LHST',
        'hasdst' => true,
        'dstlongname' => "Load Howe Summer Time",
        'dstshortname' => 'LHST' ),
    'Asia/Magadan' => array(
        'offset' => 39600000,
        'longname' => "Magadan Time",
        'shortname' => 'MAGT',
        'hasdst' => true,
        'dstlongname' => "Magadan Summer Time",
        'dstshortname' => 'MAGST' ),
    'Etc/GMT-11' => array(
        'offset' => 39600000,
        'longname' => "GMT+11:00",
        'shortname' => 'GMT+11:00',
        'hasdst' => false ),
    'Pacific/Efate' => array(
        'offset' => 39600000,
        'longname' => "Vanuatu Time",
        'shortname' => 'VUT',
        'hasdst' => false ),
    'Pacific/Guadalcanal' => array(
        'offset' => 39600000,
        'longname' => "Solomon Is. Time",
        'shortname' => 'SBT',
        'hasdst' => false ),
    'Pacific/Kosrae' => array(
        'offset' => 39600000,
        'longname' => "Kosrae Time",
        'shortname' => 'KOST',
        'hasdst' => false ),
    'Pacific/Noumea' => array(
        'offset' => 39600000,
        'longname' => "New Caledonia Time",
        'shortname' => 'NCT',
        'hasdst' => false ),
    'Pacific/Ponape' => array(
        'offset' => 39600000,
        'longname' => "Ponape Time",
        'shortname' => 'PONT',
        'hasdst' => false ),
    'SST' => array(
        'offset' => 39600000,
        'longname' => "Solomon Is. Time",
        'shortname' => 'SBT',
        'hasdst' => false ),
    'Pacific/Norfolk' => array(
        'offset' => 41400000,
        'longname' => "Norfolk Time",
        'shortname' => 'NFT',
        'hasdst' => false ),
    'Antarctica/McMurdo' => array(
        'offset' => 43200000,
        'longname' => "New Zealand Standard Time",
        'shortname' => 'NZST',
        'hasdst' => true,
        'dstlongname' => "New Zealand Daylight Time",
        'dstshortname' => 'NZDT' ),
    'Antarctica/South_Pole' => array(
        'offset' => 43200000,
        'longname' => "New Zealand Standard Time",
        'shortname' => 'NZST',
        'hasdst' => true,
        'dstlongname' => "New Zealand Daylight Time",
        'dstshortname' => 'NZDT' ),
    'Asia/Anadyr' => array(
        'offset' => 43200000,
        'longname' => "Anadyr Time",
        'shortname' => 'ANAT',
        'hasdst' => true,
        'dstlongname' => "Anadyr Summer Time",
        'dstshortname' => 'ANAST' ),
    'Asia/Kamchatka' => array(
        'offset' => 43200000,
        'longname' => "Petropavlovsk-Kamchatski Time",
        'shortname' => 'PETT',
        'hasdst' => true,
        'dstlongname' => "Petropavlovsk-Kamchatski Summer Time",
        'dstshortname' => 'PETST' ),
    'Etc/GMT-12' => array(
        'offset' => 43200000,
        'longname' => "GMT+12:00",
        'shortname' => 'GMT+12:00',
        'hasdst' => false ),
    'Kwajalein' => array(
        'offset' => 43200000,
        'longname' => "Marshall Islands Time",
        'shortname' => 'MHT',
        'hasdst' => false ),
    'NST' => array(
        'offset' => 43200000,
        'longname' => "New Zealand Standard Time",
        'shortname' => 'NZST',
        'hasdst' => true,
        'dstlongname' => "New Zealand Daylight Time",
        'dstshortname' => 'NZDT' ),
    'NZ' => array(
        'offset' => 43200000,
        'longname' => "New Zealand Standard Time",
        'shortname' => 'NZST',
        'hasdst' => true,
        'dstlongname' => "New Zealand Daylight Time",
        'dstshortname' => 'NZDT' ),
    'Pacific/Auckland' => array(
        'offset' => 43200000,
        'longname' => "New Zealand Standard Time",
        'shortname' => 'NZST',
        'hasdst' => true,
        'dstlongname' => "New Zealand Daylight Time",
        'dstshortname' => 'NZDT' ),
    'Pacific/Fiji' => array(
        'offset' => 43200000,
        'longname' => "Fiji Time",
        'shortname' => 'FJT',
        'hasdst' => false ),
    'Pacific/Funafuti' => array(
        'offset' => 43200000,
        'longname' => "Tuvalu Time",
        'shortname' => 'TVT',
        'hasdst' => false ),
    'Pacific/Kwajalein' => array(
        'offset' => 43200000,
        'longname' => "Marshall Islands Time",
        'shortname' => 'MHT',
        'hasdst' => false ),
    'Pacific/Majuro' => array(
        'offset' => 43200000,
        'longname' => "Marshall Islands Time",
        'shortname' => 'MHT',
        'hasdst' => false ),
    'Pacific/Nauru' => array(
        'offset' => 43200000,
        'longname' => "Nauru Time",
        'shortname' => 'NRT',
        'hasdst' => false ),
    'Pacific/Tarawa' => array(
        'offset' => 43200000,
        'longname' => "Gilbert Is. Time",
        'shortname' => 'GILT',
        'hasdst' => false ),
    'Pacific/Wake' => array(
        'offset' => 43200000,
        'longname' => "Wake Time",
        'shortname' => 'WAKT',
        'hasdst' => false ),
    'Pacific/Wallis' => array(
        'offset' => 43200000,
        'longname' => "Wallis & Futuna Time",
        'shortname' => 'WFT',
        'hasdst' => false ),
    'NZ-CHAT' => array(
        'offset' => 45900000,
        'longname' => "Chatham Standard Time",
        'shortname' => 'CHAST',
        'hasdst' => true,
        'dstlongname' => "Chatham Daylight Time",
        'dstshortname' => 'CHADT' ),
    'Pacific/Chatham' => array(
        'offset' => 45900000,
        'longname' => "Chatham Standard Time",
        'shortname' => 'CHAST',
        'hasdst' => true,
        'dstlongname' => "Chatham Daylight Time",
        'dstshortname' => 'CHADT' ),
    'Etc/GMT-13' => array(
        'offset' => 46800000,
        'longname' => "GMT+13:00",
        'shortname' => 'GMT+13:00',
        'hasdst' => false ),
    'Pacific/Enderbury' => array(
        'offset' => 46800000,
        'longname' => "Phoenix Is. Time",
        'shortname' => 'PHOT',
        'hasdst' => false ),
    'Pacific/Tongatapu' => array(
        'offset' => 46800000,
        'longname' => "Tonga Time",
        'shortname' => 'TOT',
        'hasdst' => false ),
    'Etc/GMT-14' => array(
        'offset' => 50400000,
        'longname' => "GMT+14:00",
        'shortname' => 'GMT+14:00',
        'hasdst' => false ),
    'Pacific/Kiritimati' => array(
        'offset' => 50400000,
        'longname' => "Line Is. Time",
        'shortname' => 'LINT',
        'hasdst' => false )
);
?>
