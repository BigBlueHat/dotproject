<?php /* CALENDAR $Id$ */
##
## Calendar classes
##

require_once( $AppUI->getPearClass( 'Date' ) );
require_once( $AppUI->getSystemClass ('dp' ) );

/**
 *
 */
class CMonthCalendar {
	var $this_month;
	var $prev_month;
	var $next_month;
	var $prev_year;
	var $next_year;

	var $styleTitle;
	var $styleMain;

	var $callback;

	var $showHeader;
	var $showArrows;
	var $showDays;
	var $showWeek;
	var $showEvents;

	var $shDayNames = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );
	var $tiMoNames = array( 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D' );

	var $dayFunc;
	var $weekFunc;

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

	function setStyles( $title, $main ) {
		$this->styleTitle = $title;
		$this->styleMain = $main;
	}

	function setLinkFunctions( $day='', $week='' ) {
		$this->dayFunc = $day;
		$this->weekFunc = $week;
	}

	function setCallback( $function ) {
		$this->callback = $function;
	}

	function setEvents( $e ) {
		$this->events = $e;
	}
// drawing functions
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
 *	@return string Returns table a row with the day names
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
					$html .= $this->_drawEvents( $d );
				}
				$html .= "\n\t</td>";
			}
			$html .= "\n</tr>";
		}
		return $html;
	}

	function _drawWeek( $dateObj ) {
		$href = "javascript:$this->weekFunc(".$dateObj->getTimestamp().",'".$dateObj->toString()."')";
		$w = "        <td class=\"week\">";
		$w .= $this->dayFunc ? "<a href=\"$href\">" : '';
		$w .= '<img src="./images/view.week.gif" width="16" height="15" border="0" alt="Week View" /></a>';
		$w .= $this->dayFunc ? "</a>" : '';
		$w .= "</td>\n";
		return $w;
	}

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

##
## CEvent Class
##
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

	function CEvent() {
		$this->CDpObject( 'events', 'event_id' );
	}

	function check() {
		$this->event_private = intval( $this->event_private );
		return NULL; // object is ok
	}
}
?>