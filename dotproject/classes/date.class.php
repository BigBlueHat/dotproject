<?php /* CLASSES $Id$ */
/**
 *	@package dotproject
 *	@subpackage utilites
*/

require_once( $AppUI->getPearClass( 'Date' ) );

define( 'FMT_DATEISO', '%Y%m%dT%H%M%S' );
define( 'FMT_DATELDAP', '%Y%m%d%H%M%SZ' );
define( 'FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S' );
define( 'FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S' );
define( 'FMT_TIMESTAMP', '%Y%m%d%H%M%S' );
define( 'FMT_TIMESTAMP_DATE', '%Y%m%d' );
define( 'FMT_TIMESTAMP_TIME', '%H%M%S' );
define( 'FMT_UNIX', '3' );
define( 'WDAY_SUNDAY',    0 );
define( 'WDAY_MONDAY',    1 );
define( 'WDAY_TUESDAY',   2 );
define( 'WDAY_WENESDAY',  3 );
define( 'WDAY_THURSDAY',  4 );
define( 'WDAY_FRIDAY',    5 );
define( 'WDAY_SATURDAY',  6 );
define( 'SEC_MINUTE',    60 );
define( 'SEC_HOUR',    3600 );
define( 'SEC_DAY',    86400 );

/**
* dotProject implementation of the Pear Date class
*/
class CDate extends Date {

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
* @param int
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function addMonths( $n ) {
		$an = abs( $n );
		$years = floor( $an / 12 );
		$months = $an % 12;

		if ($n < 0) {
			$this->year -= $years;
			$this->month -= $months;
			if ($this->month < 1) {
				$this->year--;
				$this->month = 12 - $this->month;
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
* @param Date
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function dateDiff( $when ) {
		return Date_calc::dateDiff(
			$this->getDay(), $this->getMonth(), $this->getYear(),
			$when->getDay(), $when->getMonth(), $when->getYear()
		);
	}

}
?>