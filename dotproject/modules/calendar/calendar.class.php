<?php /* CALENDAR $Id$ */
##
## Calendar classes
##

require_once( $AppUI->getPearClass( 'Date' ) );
require_once( $AppUI->getSystemClass ('dp' ) );

/**
* Displays a configuration month calendar
*
* All Date objects are based on the PEAR Date package
*/
class CMonthCalendar {
/**#@+
* @var Date
*/
	var $this_month;
	var $prev_month;
	var $next_month;
	var $prev_year;
	var $next_year;
/**#@-*/

/** @var string The css style name of the Title */
	var $styleTitle;

/** @var string The css style name of the main calendar */
	var $styleMain;

/** @var string The name of the javascript function that a 'day' link should call when clicked */
	var $callback;

/** @var boolean Show the heading */
	var $showHeader;

/** @var boolean Show the previous/next month arrows */
	var $showArrows;

/** @var boolean Show the day name column headings */
	var $showDays;

/** @var boolean Show the week link (no pun intended) in the first column */
	var $showWeek;

/** @var boolean Show events in the calendar boxes */
	var $showEvents;

/** @var string */
	var $dayFunc;

/** @var string */
	var $weekFunc;

/**
* @param Date $date
*/
 function CMonthCalendar( $date=null ) {
		$this->setDate( $date );

		$this->classes = array();
		$this->callback = '';
		$this->showTitle = true;
		$this->showArrows = true;
		$this->showDays = true;
		$this->showWeek = true;
		$this->showEvents = true;

		$this->styleTitle = '';
		$this->styleMain = '';

		$this->dayFunc = '';
		$this->weekFunc = '';

		$this->events = array();
	}
// setting functions

/**
 * CMonthCalendar::setDate()
 *
 * { Description }
 *
 * @param [type] $date
 */
	 function setDate( $date=null ) {
		if (is_object($date) && (get_class($date) == 'date')) {
			$this->this_month = new Date( $date );
		} else {
			$this->this_month = new Date( $date ? "{$date}000000" : $date );
		}

		$d = $this->this_month->getDay();
		$m = $this->this_month->getMonth();
		$y = $this->this_month->getYear();

		//$date = Date_Calc::beginOfPrevMonth( $d, $m, $y-1, FORMAT_ISO );
		$this->prev_year = new Date( $date );
		$this->prev_year->setYear( $this->prev_year->getYear()-1 );

		$this->next_year = new Date( $date );
		$this->next_year->setYear( $this->next_year->getYear()+1 );

		$date = Date_Calc::beginOfPrevMonth( $d, $m, $y, DATE_FORMAT_TIMESTAMP_DATE );
		$this->prev_month = new Date( "{$date}000000" );

		$date = Date_Calc::beginOfNextMonth( $d, $m, $y, DATE_FORMAT_TIMESTAMP_DATE );
		$this->next_month =  new Date( "{$date}000000" );

	}

/**
 * CMonthCalendar::setStyles()
 *
 * { Description }
 *
 */
	 function setStyles( $title, $main ) {
		$this->styleTitle = $title;
		$this->styleMain = $main;
	}

/**
 * CMonthCalendar::setLinkFunctions()
 *
 * { Description }
 *
 * @param string $day
 * @param string $week
 */
	function setLinkFunctions( $day='', $week='' ) {
		$this->dayFunc = $day;
		$this->weekFunc = $week;
	}

/**
 * CMonthCalendar::setCallback()
 *
 * { Description }
 *
 */
	function setCallback( $function ) {
		$this->callback = $function;
	}

/**
 * CMonthCalendar::setEvents()
 *
 * { Description }
 *
 */
 function setEvents( $e ) {
		$this->events = $e;
	}
// drawing functions
/**
 * CMonthCalendar::show()
 *
 * { Description }
 *
 */
	 function show() {
		$s = '';
		if ($this->showTitle) {
			$s .= $this->_drawTitle();
		}
		$s .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" class=\"" . $this->styleMain . "\">\n";

		if ($this->showDays) {
			$s .= $this->_drawDays();
		}

		$s .= $this->_drawMain();

		$s .= "</table>\n";

		return $s;
	}

/**
 * CMonthCalendar::_drawTitle()
 *
 * { Description }
 *
 */
	 function _drawTitle() {
		global $AppUI, $m, $a;
		$url = "index.php?m=$m";
		$url .= $a ? "&a=$a" : '';
		$url .= isset( $_GET['dialog']) ? "&dialog=1" : '';

		$s = "\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\" class=\"$this->styleTitle\">";
		$s .= "\n\t<tr>";

		if ($this->showArrows) {
			$href = $url.'&date='.$this->prev_month->format(DATE_FORMAT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			$s .= "\n\t\t<td align=\"left\">";
			$s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="'.$AppUI->_('previous month').'" border="0" /></a>';
			$s .= "</td>";

		}

		$s .= "\n\t<th width=\"99%\" align=\"center\">";
		$s .= $this->this_month->format( "%B %Y" );
		$s .= "</th>";

		if ($this->showArrows) {
			$href = $url.'&date='.$this->next_month->format(DATE_FORMAT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			$s .= "\n\t\t<td align=\"right\">";
			$s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="'.$AppUI->_('next month').'" border="0" /></a>';
			$s .= "</td>";
		}

		$s .= "\n\t</tr>";
		$s .= "\n</table>";

		return $s;
	}
/**
* CMonthCalendar::_drawDays()
*
* { Description }
*
* @return string Returns table a row with the day names
*/
	function _drawDays() {
		$bow = Date_Calc::beginOfWeek( null,null,null,null,LOCALE_FIRST_DAY );
		$y = substr( $bow, 0, 4 );
		$m = substr( $bow, 4, 2 );
		$d = substr( $bow, 6, 2 );
		$wk = Date_Calc::getCalendarWeek( $d, $m, $y, "%a", LOCALE_FIRST_DAY );

		$s = $this->showWeek ? "\n\t\t<th>&nbsp;</th>" : "";
		foreach( $wk as $day ) {
			$s .= "\n\t\t<th width=\"14%\">$day</th>";
		}

		return "\n<tr>$s\n</tr>";
	}

/**
 * CMonthCalendar::_drawMain()
 *
 * { Description }
 *
 */
	 function _drawMain() {
		GLOBAL $AppUI;
		$today = new Date();
		$today = $today->format( "%Y%m%d%w" );

		$date = $this->this_month;
		$this_day = $date->getDay();
		$this_month = $date->getMonth();
		$this_year = $date->getYear();
		$cal = Date_Calc::getCalendarMonth( $this_month, $this_year, "%Y%m%d%w", LOCALE_FIRST_DAY );

		$df = $AppUI->getPref( 'SHDATEFORMAT' );

		$html = '';
		foreach ($cal as $week) {
			$html .= "\n<tr>";
			if ($this->showWeek) {
				$html .=  "\n\t<td class=\"week\">";
				$html .= $this->dayFunc ? "<a href=\"javascript:$this->weekFunc('$week[0]')\">" : '';
				$html .= '<img src="./images/view.week.gif" width="16" height="15" border="0" alt="Week View" /></a>';
				$html .= $this->dayFunc ? "</a>" : '';
				$html .= "\n\t</td>";
			}

			foreach ($week as $day) {
				$this_day = new Date( "{$day}000000" );
				$y = intval( substr( $day, 0, 4 ) );
				$m = intval( substr( $day, 4, 2 ) );
				$d = intval( substr( $day, 6, 2 ) );
				$dow = intval( substr( $day, 8, 1 ) );
				
				if ($m != $this_month) {
					$class = 'empty';
				} else if ($day == $today) {
					$class = 'today';
				} else if ($dow == 0 || $dow == 6) {
					$class = 'weekend';
				} else {
					$class = 'day';
				}
				$day = substr( $day, 0, 8 );
				$html .= "\n\t<td class=\"$class\">";
				if ($this->dayFunc) {
					$html .= "<a href=\"javascript:$this->dayFunc('$day','".$this_day->format( $df )."')\" class=\"$class\">";
					$html .= "$d";
					$html .= "</a>";
				} else {
					$html .= "$d";
				}
				if ($m == $this_month && $this->showEvents) {
					$html .= $this->_drawEvents( substr( $day, 0, 8 ) );
				}
				$html .= "\n\t</td>";
			}
			$html .= "\n</tr>";
		}
		return $html;
	}

/**
 * CMonthCalendar::_drawWeek()
 *
 * { Description }
 *
 */
	 function _drawWeek( $dateObj ) {
		$href = "javascript:$this->weekFunc(".$dateObj->getTimestamp().",'".$dateObj->toString()."')";
		$w = "        <td class=\"week\">";
		$w .= $this->dayFunc ? "<a href=\"$href\">" : '';
		$w .= '<img src="./images/view.week.gif" width="16" height="15" border="0" alt="Week View" /></a>';
		$w .= $this->dayFunc ? "</a>" : '';
		$w .= "</td>\n";
		return $w;
	}

/**
 * CMonthCalendar::_drawEvents()
 *
 * { Description }
 *
 */
	 function _drawEvents( $day ) {
		$s = '';
		if (!isset( $this->events[$day] )) {
			return '';
		}
		$events = $this->events[$day];
		foreach ($events as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$alt = isset($e['alt']) ? $e['alt'] : null;

			$s .= "<br />\n";
			$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
			$s .= "{$e['text']}";
			$s .= $href ? '</a>' : '';
		}
		return $s;
	}
}

/**
* Event Class
*
* { Description }
*
*/
class CEvent extends CDpObject {
/** @var int */
	var $event_id = NULL;

/** @var string The title of the event */
	var $event_title = NULL;

	var $event_start_date = NULL;
	var $event_end_date = NULL;
	var $event_parent = NULL;
	var $event_description = NULL;
	var $event_times_recuring = NULL;
	var $event_recurs = NULL;
	var $event_remind = NULL;
	var $event_icon = NULL;
	var $event_owner = NULL;
	var $event_project = NULL;
	var $event_private = NULL;
	var $event_type = NULL;

	function CEvent() {
		$this->CDpObject( 'events', 'event_id' );
	}

// overload check operation
	function check() {
	// ensure changes to check boxes and select lists are honoured
		$this->event_private = intval( $this->event_private );
		$this->event_type = intval( $this->event_type );
		return NULL;
	}

/**
* Utility function to return an array of events with a period
* @param Date Start date of the period
* @param Date End date of the period
* @return array A list of events
*/
	function getEventsForPeriod( $start_date, $end_date ) {
		global $AppUI;
	// the event times are stored as unix time stamps, just to be different

	// convert to default db time stamp
		$db_start = $start_date->format( DATE_FORMAT_ISO );
		$db_end = $end_date->format( DATE_FORMAT_ISO );

	// assemble query
		$sql = "
		SELECT *
		FROM events
		WHERE event_start_date BETWEEN '$db_start' AND '$db_end'
			AND ( event_private=0
				OR (event_private=1 AND event_owner=$AppUI->user_id)
			)
		";
	//echo "<pre>$sql</pre>";
	// execute and return
		return db_loadList( $sql );
	}
}

?>