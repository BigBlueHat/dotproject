<?php /* CALENDAR $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/**
 *	Calendar classes
 */

require_once($AppUI->getLibraryClass('PEAR/Date'));
require_once($AppUI->getSystemClass('dp'));
require_once($AppUI->getSystemClass('libmail'));
require_once($AppUI->getSystemClass('date'));
require_once($AppUI->getSystemClass('webdav_client'));
require_once($AppUI->getModuleClass('projects'));

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

/** @var boolean Show the month name as link */
	var $clickMonth;

/** @var boolean Show events in the calendar boxes */
	var $showEvents;

/** @var string */
	var $dayFunc;

/** @var string */
	var $weekFunc;

/** @var boolean Show highlighting in the calendar boxes */
	var $showHighlightedDays;

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
		$this->showHighlightedDays = true;
		
	
		$this->styleTitle = '';
		$this->styleMain = '';
	
		$this->dayFunc = '';
		$this->weekFunc = '';
	
		$this->events = array();
		$this->highlightedDays = array();
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
		$this->this_month = new CDate( $date );

		$d = $this->this_month->getDay();
		$m = $this->this_month->getMonth();
		$y = $this->this_month->getYear();

		$this->prev_year = new CDate($date);
		$this->prev_year->setYear($this->prev_year->getYear() - 1);

		$this->next_year = new CDate($date);
		$this->next_year->setYear($this->next_year->getYear() + 1);

		$this->prev_month = new CDate($this->this_month);
		$this->prev_month->addMonths(-1);

		$this->next_month = new CDate($this->this_month);
		$this->next_month->addMonths(1);
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
	function setEvents($e) {
		$this->events = $e;
	}
	
/**
 * CMonthCalendar::setHighlightedDays()
 * ie	['20040517'] => '#ff0000',
 *
 * { Description }
 *
 */
	function setHighlightedDays( $hd ) {
		$this->highlightedDays = $hd;
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
		$s .= '<table border="0" cellspacing="1" cellpadding="2" width="100%" class="' . $this->styleMain . '">'."\n";

		// Draw months in the minical
		// $s .= $this->_drawMonths();

		if ($this->showDays) {
			$s .= $this->_drawDays();
		}

		$s .= $this->_drawMain();

		$s .= '</table>'."\n";

		return $s;
	}
	
	function showDynamic() {
		$html = '';
	
		$orig_date = $this->this_month;
		if ($this->this_month->getMonth() - 4 < 1) {
			$this->this_month->setMonth(8 + $this->this_month->getMonth());
			$this->this_month->setYear($this->this_month->getYear() - 1);
		} else {
			$this->this_month->setMonth($this->this_month->getMonth() - 4);
		}
		
		$this->setDate($this->this_month);
		for ($i = -3; $i <= 6; $i++)
		{
			
			if ($this->this_month->getMonth() == 12) {
				$this->this_month->setMonth(1);
				$this->this_month->setYear($this->this_month->getYear() + 1);
			} else {
				$this->this_month->setMonth($this->this_month->getMonth() + 1);
			}
			$this->setDate($this->this_month);
			$html .= '<div id="cal_' . $i . '" class="calendar' . ($i != 0?', hidden':'') . '">';
			$html .= $this->show();
			$html .= '</div>';
		}
		
		$this->setDate($orig_date);
		
		return $html;
	}

/**
 * CMonthCalendar::_drawTitle()
 *
 * { Description }
 *
 */
	function _drawTitle() {
		global $AppUI, $m, $a, $locale_char_set, $tpl;

		$url = 'index.php?m=' . $m;
		$url .= $a ? '&amp;a=' . $a : '';
		$url .= isset($_GET['dialog']) ? '&amp;dialog=1' : '';

		$href = $url.'&amp;date='.$this->prev_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&amp;callback='.$this->callback : '').((count($this->highlightedDays)>0)?'&uts='.key($this->highlightedDays):'');
		$tpl->assign('href_prev', $href);
		$href = $url.'&amp;date='.$this->this_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&amp;callback='.$this->callback : '').((count($this->highlightedDays)>0)?'&amp;uts='.key($this->highlightedDays):'');
		$tpl->assign('href_this', $href);
		$href = $url.'&amp;date='.$this->next_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&amp;callback='.$this->callback : '').((count($this->highlightedDays)>0)?'&amp;uts='.key($this->highlightedDays):'');
		$tpl->assign('href_next', $href);
		$urlm = 'index.php?m='.$m;
		$hrefm = $urlm.'&amp;date='.$this->this_month->format(FMT_TIMESTAMP_DATE);
		$tpl->assign('href_month', $hrefm);
		$tpl->assign('day', $this);

		return $tpl->fetchFile('_title', 'calendar');
	}
	
	function _drawMonthsAbbr() {
		global $a, $m;
	
		$url = 'index.php?m='.$m;
		$url .= $a ? '&amp;a='.$a : '';
		$url .= isset( $_GET['dialog']) ? '&amp;dialog=1' : '';
	
		$year = new CDate();
		$year->copy($this->this_month);
		for($i = 1; $i <= 12; $i++) {
			$year->setMonth($i);
			//$month = $i;
			//if ($this->styleMain != 'minical')
			$month = $year->getMonthName();
			if (!$this->showWeek) {
				global $l10n;
				
				$month = $l10n->substr($month, 0, 1);
			}
			$s .= "\n\t\t" . '<td width="9%"><a href="'.$url.'&amp;date='.$year->format(FMT_TIMESTAMP_DATE) . '">' . $month . '</a></td>';
			
		}

		return "\n" . '<tr><td colspan="8"><table class="tbl" width="100%"><tr>' . $s . '</tr></table></td></tr>';
	}
	
	function _drawMonths() {
		global $a, $m;
	
		$url = 'index.php?m='.$m;
		$url .= $a ? '&amp;a='.$a : '';
		$url .= isset( $_GET['dialog']) ? '&amp;dialog=1' : '';
	
		$year = new CDate();
		$year->copy($this->this_month);
		for($i = 1; $i <= 12; $i++) {
			$year->setMonth($i);
			$month = $i;
			//if ($this->styleMain != 'minical')
			if ($this->showWeek) {
				$month = $year->getMonthName();
			}
			$s .= "\n\t\t" . '<td width="9%"><a href="'.$url.'&amp;date='.$year->format(FMT_TIMESTAMP_DATE) . '">' . $month . '</a></td>';
			
		}

		return "\n" . '<tr><td colspan="8"><table class="tbl"><tr>' . $s . '</tr></table></td></tr>';
	}
	
	/**
	* CMonthCalendar::_drawDays()
	*
	* { Description }
	*
	* @return string Returns table a row with the day names
	*/
	function _drawDays() {
		global $locale_char_set;

		$s = $this->showWeek ? "\n\t\t<th>&nbsp;</th>" : "";
		for( $day = 0; $day < 7; $day++ ) {
			$s .= "\n\t\t<th width=\"14%\">" . htmlentities(CDate::getWeekdayAbbrname(($day + LOCALE_FIRST_DAY) % 7), ENT_COMPAT, $locale_char_set) . "</th>";
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
		GLOBAL $AppUI, $tpl;
		$today = new CDate();
		$today = $today->format( "%Y%m%d%w" );

		$date = $this->this_month;
		$this_day = intval($date->getDay());
		$this_month = intval($date->getMonth());
		$this_year = intval($date->getYear());
		$cal = Date_Calc::getCalendarMonth( $this_month, $this_year, '%Y%m%d%w', LOCALE_FIRST_DAY );

		$df = $AppUI->getPref('SHDATEFORMAT');

		$html = '';
		foreach ($cal as $week) {
			$html .= "\n<tr>";
			if ($this->showWeek) {
				if ($this->dayFunc)
					$tpl->assign('href', "javascript:$this->weekFunc('{$week[0]}')");
				$html .= $tpl->fetchFile('_week', 'calendar');
			}

			foreach ($week as $day) {
				$this_day = new CDate($day);
				$y		= intval(substr($day, 0, 4));
				$m		= intval(substr($day, 4, 2));
				$d		= intval(substr($day, 6, 2));
				$dow	= intval(substr($day, 8, 1));

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
				$html .= "\n\t<td class=\"$class\"";
				if($this->showHighlightedDays && isset($this->highlightedDays[$day])){
					$html .= " style=\"border: 1px solid ".$this->highlightedDays[$day]."\"";
				}
				$html .= ">";
				if ($m == $this_month) {
					if ($this->dayFunc) {
						$html .= "<a href=\"javascript:$this->dayFunc('$day','".$this_day->format( $df )."')\" class=\"$class\">";
						$html .= "$d";
						$html .= "</a>";
					} else {
					  $html .= "$d";
					}
					
					if ($this->showEvents) {
						$html .= $this->_drawEvents(substr($day, 0, 8));
					}
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
		global $tpl;
		
		$href = "javascript:$this->weekFunc(".$dateObj->getTimestamp().",'".$dateObj->toString()."')";
		if ($this->dayFunc)
			$tpl->assign('href', $href);

		return $tpl->fetchFile('_week', 'calendar');
	}

	/**
	 * CMonthCalendar::_drawEvents()
	 *
	 * { Description }
	 *
	 */
	 function _drawEvents( $day ) {
		global $tpl;
		$s = '';
		if (!isset( $this->events[$day] )) {
			return '';
		}
		$events = $this->events[$day];
		foreach ($events as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$e['alt'] = isset($e['alt']) ? str_replace("\n",' ',$e['alt']) : null;
			
			$tpl->assign('event', $e);
			$s .= $tpl->fetchFile('_event', 'calendar');
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
	var $event_project = 0;
	var $event_task = 0;
	var $event_private = NULL;
	var $event_type = NULL;
	var $event_notify = null;
	var $event_cwd = null;
	var $event_url = null;

	function CEvent() {
		$this->CDpObject('events', 'event_id');
		$this->_tbl_name = 'event_title';
		$this->search_fields = array ('event_title', 'event_description');
		$this->_parent = new CProject;
		$this->_tbl_parent = 'event_project';
	}

	/**
	 * overload check operation 
	 */
	function check() {
	// ensure changes to check boxes and select lists are honoured
		$this->event_private	= intval($this->event_private);
		$this->event_type		= intval($this->event_type);
		$this->event_cwd			= intval($this->event_cwd);
		
		return null;
	}

	/**
	 * Calculating if an recurrent date is in the given period
	 * @param Date Start date of the period
	 * @param Date End date of the period
	 * @param Date Start date of the Date Object
	 * @param Date End date of the Date Object
	 * @param integer Type of Recurrence
	 * @param integer Times of Recurrence ... Note from merlinyoda: doesn't appear necessary, remove var/update calls?
	 * @param integer Time of Recurrence
	 * @return array Calculated Start and End Dates for the recurrent Event for the given Period
	 */
	function getRecurrentEventforPeriod( $start_date, $end_date, $event_start_date, $event_end_date, $event_recurs, $event_times_recuring, $j ) {

		//this array will be returned
		$transferredEvent = array();

		//create Date Objects for Event Start and Event End
		$eventStart = new CDate($event_start_date);
		$eventEnd	= new CDate($event_end_date);

		//Time of Recurence = 0 (first occurence of event) has to be checked, too.
		if ($j>0) {
			switch ($event_recurs) {
				case 1:
					$eventStart->addSpan(new Date_Span(3600 * $j));
					$eventEnd->addSpan(new Date_Span(3600 * $j));
					break;
				case 2:
					$eventStart->addDays($j);
					$eventEnd->addDays($j);
					break;
				case 3:
					$eventStart->addDays(7 * $j);
					$eventEnd->addDays(7 * $j);
					break;
				case 4:
					$eventStart->addDays(14 * $j);
					$eventEnd->addDays(14 * $j);
					break;
				case 5:
					$eventStart->addMonths($j);
					$eventEnd->addMonths($j);
					break;
				case 6:
					$eventStart->addMonths(3 * $j);
					$eventEnd->addMonths(3 * $j);
					break;
				case 7:
					$eventStart->addMonths(6 * $j);
					$eventEnd->addMonths(6 * $j);
					break;
				case 8:
					$eventStart->addMonths(12 * $j);
					$eventEnd->addMonths(12 * $j);
					break;
				default:
					break;
			}
		}

		if ($start_date->compare ($start_date, $eventStart) <= 0 &&
			$end_date->compare ($end_date, $eventEnd) >= 0)
		{
		// add temporarily moved Event Start and End dates to returnArray
		$transferredEvent = array($eventStart, $eventEnd);
		}

		// return array with event start and end dates for given period (positive case)
		// or an empty array (negative case)
		return $transferredEvent;
	}

	/**
	 * Utility function to return an array of events with a period
	 * @param Date Start date of the period
	 * @param Date End date of the period
	 * @return array A list of events
	 */
	function getEventsForPeriod( $start_date, $end_date, $filter = 'all', $user_id = null, $pro_filter = 0 ) {
		global $AppUI;

	// the event times are stored as unix time stamps, just to be different

	// convert to default db time stamp
		$sdate = new CDate();
		$sdate->setDate($start_date->getDate());
		$db_start = $sdate->format( FMT_DATETIME_MYSQL );
		$edate = new CDate();
		$edate->setDate($end_date->getDate());
		$db_end = $edate->format( FMT_DATETIME_MYSQL );
		if (! isset($user_id)) {
			$user_id = $AppUI->user_id;
		}

		$project =& new CProject;
		$allowedProjects = $project->getAllowedSQL($user_id, 'event_project');
		
		//do similiar actions for recurring and non-recurring events
		$queries = array('q'=>'q', 'r'=>'r');
		
		foreach ($queries as $query_set) {
		
			$$query_set  = new DBQuery;
			$$query_set->addTable('events', 'e');
			$$query_set->addQuery('e.*');
			
			if (($AppUI->getState('CalIdxCompany'))) {
				$$query_set->addJoin('projects', 'p', 'p.project_id =  e.event_project');
				$$query_set->addWhere('project_company = ' . $AppUI->getState('CalIdxCompany') );
			}
			
			if (count($allowedProjects)) {
				$$query_set->addWhere('( ( ' . implode(' AND ',  $allowedProjects) . ' ) ' 
					. (($AppUI->getState('CalIdxCompany'))?'':' OR event_project = 0 ').')');
			}
			
			switch ($filter) {
				case 'my':
					$$query_set->addJoin('user_events', 'ue', 'ue.event_id = e.event_id AND ue.user_id ='.$user_id);
					$$query_set->addWhere('((event_private = 0 AND ue.user_id = '.$user_id.') OR event_owner='.$user_id.')');
					break;
				case 'own':
					$$query_set->addWhere('event_owner ='. $user_id);
					break;
				case 'all':
					$$query_set->addWhere('(event_private=0 OR (event_private=1 AND event_owner='.$user_id.'))');
					break;
			}
			
			switch ($pro_filter) {
				case '0':
					break;
				default:
					$$query_set->addWhere("( event_project =".$pro_filter." )");
					break;
			}
			
			if ($query_set == 'q') { // assemble query for non-recursive events
				$$query_set->addWhere('(event_recurs <= 0)');
				// following line is only good for *non-recursive* events
				$$query_set->addWhere("(event_start_date <= '$db_end' AND event_end_date >= '$db_start' "
					."OR event_start_date BETWEEN '$db_start' AND '$db_end')");
				$eventList = $$query_set->loadList();
			} else if ($query_set == 'r') { // assemble query for recursive events
				$$query_set->addWhere('(event_recurs > 0)');
				$eventListRec = $$query_set->loadList();
			}
		}
		

	//Calculate the Length of Period (Daily, Weekly, Monthly View)
		$periodLength = Date_Calc::dateDiff($start_date->getDay(),$start_date->getMonth(),$start_date->getYear(),$end_date->getDay(),$end_date->getMonth(),$end_date->getYear());


		// AJD: Should this be going off the end of the array?	I don't think so.
		// If it should then a comment to that effect would be nice.
		// for ($i=0; $i < sizeof($eventListRec)+1;	 $i++) {
		for ($i=0; $i < sizeof($eventListRec);	$i++) {

			//note from merlinyoda: j=0 is the original event according to getRecurrentEventforPeriod
			// So, since the event is *recurring* x times, the loop condition should be j <= x, not j < x.
			// This way the original and all recurrances are covered.
			//for ($j=0; $j < intval($eventListRec[$i]['event_times_recuring']); $j++) {
			for ($j=0; $j <= intval($eventListRec[$i]['event_times_recuring']); $j++) {

				//Daily View
				//show all
				if ($periodLength == 1){
				$recEventDate = CEvent::getRecurrentEventforPeriod( $start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j );
				}
				//Weekly or Monthly View and Hourly Recurrent Events
				//only show hourly recurrent event one time and add string 'hourly'
				elseif ($periodLength > 1 && $eventListRec[$i]['event_recurs'] == 1 && $j==0) {
				$recEventDate = CEvent::getRecurrentEventforPeriod( $start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j );
				$eventListRec[$i]['event_title'] = $eventListRec[$i]['event_title']." (".$AppUI->_('Hourly').")";
				}
				//Weekly and Monthly View and higher recurrence mode
				//show all events of recurrence > 1
				elseif ($periodLength > 1 && $eventListRec[$i]['event_recurs'] > 1) {
				$recEventDate = CEvent::getRecurrentEventforPeriod( $start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j );
				}
				//add values to the eventsArray if check for recurrent event was positive
				if ( sizeof($recEventDate) > 0 ) {
					$eList[0] = $eventListRec[$i];
					$eList[0]['event_start_date'] = $recEventDate[0]->format( FMT_DATETIME_MYSQL );
					$eList[0]['event_end_date'] = $recEventDate[1]->format( FMT_DATETIME_MYSQL );
					$eventList = array_merge($eventList,$eList);
				}
				// clear array of positive recurrent events for the case that next loop recEventDate is empty in order to avoid double display
				$recEventDate = array();
			}
		}

		//return a list of non-recurrent and recurrent events
		return $eventList;
	}

	function &getAssigned() {
		$q	= new DBQuery;
		$q->addTable('users', 'u');
		$q->addTable('user_events', 'ue');
		$q->addTable('contacts', 'con');
		$q->addQuery('u.user_id');
		$q->addWhere('ue.event_id = ' . $this->event_id);
		$q->addWhere('user_contact = contact_id');
		$q->addWhere('ue.user_id = u.user_id');
		$assigned_ids = $q->loadColumn();
		if (!empty($assigned_ids))
			$assigned = dPgetUsersHash($assigned_ids);
		else
			$assigned = array();
		
		return $assigned;
	}
	
	function &getAssignedContacts() {
		$q	= new DBQuery;
		$q->addQuery('c.contact_id');
		$q->addQuery('c.contact_order_by');
		$q->addTable('contacts', 'c');
		$q->addTable('event_contacts', 'ec');
		$q->addWhere('ec.event_id = ' . $this->event_id);
		$q->addWhere('ec.contact_id = c.contact_id');
		$assigned_contacts = $q->loadHashList();
				
		return $assigned_contacts;
	}
	
	function updateAssignedContacts($assigned_contacts) {
		// First remove the assigned from the user_events table
		global $AppUI;
		
		$q	= new DBQuery;
		$q->addWhere("event_id = $this->event_id");
		$q->setDelete('event_contacts');		
		$q->exec();
		$q->clear();
		
		if (is_array($assigned_contacts) && count($assigned_contacts)) {
			foreach ($assigned_contacts as $cid) {
				if ($cid) {
					$q->addTable('event_contacts', 'ec');
					$q->addInsert('event_id', $this->event_id);
					$q->addInsert('contact_id', $cid);
					$q->exec();
					$q->clear();
				}
			}
			
			if ($msg = db_error())
				$AppUI->setMsg($msg, UI_MSG_ERROR);
		}
	}

	function updateAssigned($assigned) {
		// First remove the assigned from the user_events table
		global $AppUI;
		
		$q	= new DBQuery;
		$q->addWhere('event_id = ' . $this->event_id);
		$q->setDelete('user_events');		
		$q->exec();
		$q->clear();
		
		if (is_array($assigned) && count($assigned)) {
			
			foreach ($assigned as $uid) {
				if ($uid) {
					$q->addTable('user_events', 'ue');
					$q->addInsert('event_id', $this->event_id);
					$q->addInsert('user_id', $uid);
					$q->exec();
					$q->clear();
				}
			}
			
			if ($msg = db_error())
				$AppUI->setMsg($msg, UI_MSG_ERROR);
		}
	}

	function notify($assignees, $update = false, $clash = false)
	{
	  global $AppUI, $locale_char_set;
	  $mail_owner = $AppUI->getPref('MAILALL');
	  $assignee_list = explode(',', $assignees);
	  $owner_is_assigned = in_array($this->event_owner, $assignee_list);
	  if ($mail_owner && ! $owner_is_assigned && $this->event_owner) {
		array_push($assignee_list, $this->event_owner);
	  }
		// Remove any empty elements otherwise implode has a problem
		foreach ($assignee_list as $key => $x) {
			if (! $x)
				unset($assignee_list[$key]);
		}
	  if (! count($assignee_list))
		return;
		
		$q	= new DBQuery;
		$q->addTable('users','u');
		$q->addTable('contacts','con');
		$q->addQuery('user_id, contact_first_name,contact_last_name, contact_email');
		$q->addWhere('u.user_contact = con.contact_id');
		$q->addWhere("user_id in ( " . implode(',', $assignee_list) . ")");
		$users = $q->loadHashList('user_id');
		
		$q->addTable('event_contacts', 'ec');
		$q->addJoin('contacts','con', 'con.contact_id = ec.contact_id');
		$q->addQuery('con.contact_id, contact_first_name,contact_last_name, contact_email');
		$q->addWhere('ec.event_id = ' . $this->event_id);
		$contacts = $q->loadList();
	
	  $date_format = $AppUI->getPref('SHDATEFORMAT');
	  $time_format = $AppUI->getPref('TIMEFORMAT');
	  $fmt = "$date_format $time_format";

	  $start_date =& new CDate($this->event_start_date);
	  $end_date =& new CDate($this->event_end_date);

	  $mail =& new Mail;
	  $type = $update ? $AppUI->_('Updated') : $AppUI->_('New');
	  if ($clash) {
		$mail->Subject($AppUI->_('Requested Event') . ': ' . $this->event_title, $locale_char_set);
	  } else  {
		$mail->Subject($type . ' ' . $AppUI->_('Event') . ': ' . $this->event_title, $locale_char_set);
	  }
	  $mail->From( '"' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>');

	  $body = '';
	  if ($clash) {
		$body .= "You have been invited to an event by $AppUI->user_first_name $AppUI->user_last_name\n";
		$body .= 'However, either you or another intended invitee has a competing event'."\n";
		$body .= "$AppUI->user_first_name $AppUI->user_last_name has requested that you reply to this message\n";
		$body .= 'and confirm if you can or can not make the requested time.'."\n\n";
	  }
	  $body .= $AppUI->_('Event') . ":\t" . $this->event_title . "\n";
	  $body .= $AppUI->_('Starts') . ":\t" . $start_date->format($fmt) . "\n";
	  $body .= $AppUI->_('Ends') . ":\t" . $end_date->format($fmt) . "\n";
		if ($this->event_url)
			$body .= $AppUI->_('Website') . ":\t" . $this->event_url . "\n";

	  // Find the project name.
	  if ($this->event_project) {
			$prj = array();
			$q	= new DBQuery;
			$q->addTable('projects','p');
			$q->addQuery('project_name');
			$q->addWhere('p.project_id ='.$this->event_project);
			$sql = $q->prepare();
			$q->clear();
			if (db_loadHash($sql, $prj)){
				$body .= $AppUI->_('Project') . ":\t". $prj['project_name'];
			}
	  }

	  $types = dPgetSysVal('EventType');

	  $body .= $AppUI->_('Type') . ":\t" . $AppUI->_($types[$this->event_type]) . "\n";
	  $body .= $AppUI->_('Attendees') . ":\t";
	  $start = false;
	  foreach ($users as $user) {
			if ($start)
				$body .= ',';
			else
				$start = true;
			
		$body .= $user['contact_first_name'].' '.$user['contact_last_name'];
	  }
	  $bodyContacts = $body . "\n\n" . $this->event_description . "\n";

		// create vEvent Attachment String	
		$v = new vCalendar;
		$v->addSum($this->event_title);
		$v->addDesc($this->event_description);
		$v->addCat($types[(int) $this->event_type]);
		$v->addOrg($AppUI->user_first_name.' '.$AppUI->user_last_name, $AppUI->user_email);
		$v->addStart($start_date);
		$v->addEnd($end_date);
		if ($this->event_recurs > 0)
			$v->addRec($this->event_recurs, $this->event_times_recuring, $end_date);
		
		foreach ($users as $user) {
			$v->addAttendee($user['contact_first_name'] .' '. $user['contact_last_name'], $user['contact_email']);
		}
		foreach ($contacts as $contact) {
			$v->addAttendee($contact['contact_first_name'] .' '. $contact['contact_last_name'], $contact['contact_email']);
		}
		
		
		$v->addRel($this->event_parent, 'PARENT');
		$v->addCreated();
		$v->addUid();
		$v->addSeq();
		if ($this->event_private == TRUE || $this->event_private == 1) {
			$v->addClass('PRIVATE');
		} else {
			$v->addClass('PUBLIC');
		}
		$v->addvEvent();
		$ical = $v->genString();
		// end of vEvent generation
	
	  $mail->Body($bodyContacts, $locale_char_set);
	  $mail->Attach( $AppUI->_('Event').'.ics', $filetype = 'text/calendar' , $disposition = 'inline', $ical );

		// Send out emails to all interested contacts.
		foreach ($contacts as $contact) {
		if (!empty($contact['contact_email']) && $mail->ValidEmail($contact['contact_email'])) {
			$mail->To($contact['contact_email'], true);
				$mail->Send();
		}
	  }

		// Send details with dP URLs for users (they don't apply to contacts, since contacts can't login)
		if (! $clash)
		$body .= $AppUI->_('URL') . ":\t" . DP_BASE_URL . '/index.php?m=calendar&amp;a=view&amp;event_id=' . $this->event_id . "\n";
	   $bodyUsers = $body . "\n\n" . $this->event_description . "\n";
		$mail->Body($bodyUsers, $locale_char_set);
		
		$v->addUrl(DP_BASE_URL . '/index.php?m=calendar&amp;a=view&amp;event_id=' . $this->event_id );
		$mail->clearAttachments();
		$mail->Attach( $AppUI->_('Event').'.ics', $filetype = 'text/calendar' , $disposition = 'inline', $ical );
		
		// Send out emails to all interested users.
	  foreach ($users as $user) {
			if (! $mail_owner && $user['user_id'] == $this->event_owner)
				continue;
				
			$mail->To($user['contact_email'], true);
			$mail->Send();
	  }
	}

	function checkClash($userlist = null)
	{
	  global $AppUI;
	  if (! isset($userlist))
		return false;
	  $users = explode(',', $userlist);

	  // Now, remove the owner from the list, as we will always clash on this.
	  $key = array_search($AppUI->user_id, $users);
	  if (isset($key) && $key !== false) // Need both for change in php 4.2.0
		unset($users[$key]);

	  if (! count($users))
		return false;

	  $start_date =& new CDate($this->event_start_date);
	  $end_date =& new CDate($this->event_end_date);

	  // Now build a query to find matching events.
		$q	= new DBQuery;
		$q->addTable('events', 'e');
		$q->addQuery('e.event_owner, ue.user_id, e.event_cwd, e.event_id, e.event_start_date, e.event_end_date');
		$q->addJoin('user_events', 'ue', 'ue.event_id = e.event_id');
		$q->addWhere("event_start_date <= '" . $end_date->format(FMT_DATETIME_MYSQL) . "'");
		$q->addWhere("event_end_date >= '" . $start_date->format(FMT_DATETIME_MYSQL) . "'");
		$q->addWhere("( e.event_owner in (" . implode(',', $users) . ") OR ue.user_id in (" . implode(',', $users) .") )");
		$q->addWhere('e.event_id !='.$this->event_id);
		
		$result = $q->exec();
	  if (! $result)
		return false;

	  $clashes = array();
	  while ($row = db_fetch_assoc($result)) {
		array_push($clashes, $row['event_owner']);
		if ($row['user_id'])
		  array_push($clashes, $row['user_id']);
	  }
	  $clash = array_unique($clashes);
		$q->clear();
	  if (count($clash)) {	
		$q->addTable('users','u');
		$q->addTable('contacts','con');
		$q->addQuery('user_id');
		$q->addQuery('CONCAT_WS(" ",contact_first_name,contact_last_name)');
		$q->addWhere("user_id in (" . implode(",", $clash) . ")");
		$q->addWhere('user_contact = contact_id');
		return $q->loadHashList();
	  } else {
		return false;
	  }

	}

	function getEventsInWindow($start_date, $end_date, $start_time, $end_time, $users = null)
	{
	  if (!isset($users))
		return false;
	  if (!count($users))
		return false;

	  // Now build a query to find matching events. 
		$q	= new DBQuery;
		$q->addTable('events', 'e');
		$q->addQuery('e.event_owner, ue.user_id, e.event_cwd, e.event_id, e.event_start_date, e.event_end_date');
		$q->addJoin('user_events', 'ue', 'ue.event_id = e.event_id');
		$q->addWhere("event_start_date >= '$start_date'");
		$q->addWhere("event_end_date <= '$end_date'");
		$q->addWhere("EXTRACT(HOUR_MINUTE FROM e.event_end_date) >= '$start_time'");
		$q->addWhere("EXTRACT(HOUR_MINUTE FROM e.event_start_date) <= '$end_time'");
		$q->addWhere('(e.event_owner in (' . implode(',', $users) . ') OR ue.user_id in (' . implode(',', $users) .'))');
		$result = $q->exec();
		if (!$result)
			return false;

		$eventlist = array();
		while ($row = db_fetch_assoc($result)) {
			$eventlist[] = $row;
		}
		
		return $eventlist;
	}

	function delete() {
		$msg = parent::delete();
		if(empty($msg))
		{
		  $q  = new DBQuery;
		  $q->setDelete('user_events');
		  $q->addWhere('event_id = ' . $this->event_id);
		  if (!$q->exec())
			$msg = db_error();
		  
		  $q->clear();
		}
		CWebCalresource::autoPublish($this->event_project);
		
		return $msg;
	}

	function store($autoPublish = true) {
		$msg = parent::store();
		if ($autoPublish)
			CWebCalresource::autoPublish($this->event_project);
		
		return $msg;
	}
}

$event_filter_list = array (
	'my'	=> 'My Events',
	'own' => 'Events I Created',
	'all' => 'All Events'
);


class vCalendar {	 
	var $recurs = null;
	var $sd = null;
	var $vcalendar = null;
	var $vevent = null;
	
	function vCalendar() {
		$this->recurs = array ('NEVER', 'HOURLY', 'DAILY', 'WEEKLY', 'BI-WEEKLY', 'EVERY MONTH', 'QUARTERLY', 'EVERY 6 MONTHS', 'YEARLY');
		$this->sd = array('SU','MO','TU','WE','TH','FR','SA');
	}

	/** 
	 * create vcalendar header
	 */
	function addVCH() {
		global $AppUI;
		$vch = "BEGIN:VCALENDAR\r\n";
		$vch .= "PRODID:-//dotProject devTeam//NONSGML dotProject ".$AppUI->getVersion()."//iCal 2.0//EN\r\n";
		$vch .= "VERSION:2.0\r\n";
		$vch .= "METHOD:PUBLISH\r\n";	
		$this->vcalendar = $vch.$this->vcalendar;
	}
	
	/** 
	 * append footer
	 */
	function addVCF() {
		$this->vcalendar .= 'END:VCALENDAR';
	}

	/** 
	 * append vevent header
	 */
	function addVEH() {
		$this->vevent = "BEGIN:VEVENT\r\n". $this->vevent;
	}

	/**
	 * append vevent footer
	 */
	function addVEF() {
		$this->vevent .= "END:VEVENT\r\n";
	}

	function addAttendee($c, $e, $r = 'REQ-PARTICIPANT') {
		$this->vevent .= 'ATTENDEE;ROLE='.$r.';CN=' . $c . ':MAILTO:' . $e . "\r\n";
	}
	
	function addCat($c = 'GENERAL') {
		$this->vevent .= 'CATEGORIES:'.$c."\r\n";	
	}
	
	function addClass($c = 'PUBLIC') {
		$this->vevent .= "CLASS:".$c."\r\n";	
	}
	
	function addCreated($d = null, $t = null) {
		$this->vevent .= 'CREATED:'.			(empty($d) ? date('Ymd') : $d) .'T'.(empty($t) ? date('His') : $t) ."Z\r\n";
		$this->vevent .= 'DTSTAMP:'.			(empty($d) ? date('Ymd') : $d) .'T'.(empty($t) ? date('His') : $t) ."Z\r\n";
		$this->vevent .= 'LAST-MODIFIED:'.(empty($d) ? date('Ymd') : $d) .'T'.(empty($t) ? date('His') : $t) ."Z\r\n";
	}
	
	function addDesc($d) {
		$this->vevent .= 'DESCRIPTION:'.$d."\r\n";	
	}
	
	/**
	 *	@param object $e CDate object 
	 */
	function addEnd($e) {
		$this->vevent .= 'DTEND:'.$e->format('%Y%m%d').'T'.$e->format('%H%M%S')."Z\r\n";	
	}
	
	function addLoc($l) {
		$this->vevent .= 'LOCATION:'.$l."\r\n";	
	}
	
	function addPrio($p = '3') {
		$this->vevent .= 'PRIORITY:'.$p."\r\n";	
	}
	
	function addOrg($n, $e) {
		$this->vevent .= 'ORGANIZER;CN='.$n.':MAILTO:'.$e."\r\n";	
	}
	
	function addRel($r, $t = 'PARENT') {
		$this->vevent .= "RELATED-TO;RELTYPE=$t:$r\r\n";	
	}
	
	function addRec($f, $i, $e) {
		$this->vevent .= 'RRULE:FREQ='.$this->recurs[$f].';COUNT='.$i.';UNTIL='.$e->format('%Y%m%d').'T'.$e->format('%H%M%S')."Z\r\n";
	}
	
	function addSeq($s = '0') {
		$this->vevent .= 'SEQUENCE:'.$s."\r\n";	
	}
	
	/**
	 *	@param object $s CDate object 
	 */
	function addStart($s) {
		$this->vevent .= 'DTSTART:'.$s->format('%Y%m%d').'T'.$s->format('%H%M%S')."Z\r\n";	
	}
	
	function addSum($s) {
		$this->vevent .= 'SUMMARY:'.$s."\r\n";	
	}
	
	function addTransp($t = 'OPAQUE') {
		$this->vevent .= 'TRANSP:'.$t."\r\n";	
	}
	
	function addUid($u = 'dotProject') {
		$this->vevent .= 'UID:'.$u."\r\n";	
	}
	
	function addUrl($u) {
		$this->vevent .= 'URL:'.$u."\r\n";	
	}
	
	function addvEvent() {		
		$this->addVEH();
		$this->addVEF();
		$this->vcalendar .= $this->vevent;
		$this->vevent = null;
	}
	
	/** 
	 * public function to add a vevent object
	 */
	function genvEventString() {		
		return "BEGIN:VEVENT\r\n".$this->vevent."END:VEVENT\r\n";
	}
	
	function genString() {	
		$this->addVCH();
		$this->addVCF();
		
		return $this->vcalendar;
	}
	
	function iCalDateToDateObj($iD) {
		$d = new CDate(substr($iD, 0, 8));
		$d->setHour(substr($iD, 9, 2));
		$d->setMinute(substr($iD, 11, 2));
		$d->setSecond(substr($iD, 13, 2));
		
		return $d;
	}
	
	function recToDB($s) {
		if (!empty($s)) {
			$d = array();
			// divide the different parameters (resulting PARAM=VALUE)
			$re = explode(';', $s);
			
			// divide each parameter string into key and value
			foreach ($re as $r) {
				$t = explode('=', $r);
				$d[$t[0]] = $t[1];
			}
			return $d;
		} else 
			return FALSE;
	}

	/**
	* Retrieve filtered events
	* @param string Filter Info String
	* @param bool Determine whether Private Events shall be added or not
	* @return array Event List
	*/

	function getEvents($eventFilter = null, $addPrivateEvents = false) {
		$q	= new DBQuery;
		$q->addTable('events');
		if (!empty($eventFilter))
			$q->addWhere($eventFilter);

		if ($addPrivateEvents == false)
			$q->addWhere('event_private <> 1');

		$events = $q->loadList();	
		
		return $events;
	}
	
	/**
	* Add filtered events to the object output string from filter
	* @param string Filter Info String
	* @param bool Determine whether Private Events shall be added or not
	*/

	function addEventsByFilter($eventFilter = null, $addPrivateEvents = false) {
		$events = $this->getEvents($eventFilter, $addPrivateEvents);
		
		$types = dPgetSysVal('EventType');		

		foreach ($events as $e) {
			$q	= new DBQuery;
			$q->addTable('user_events');
			$q->addJoin('users', 'u', 'u.user_id = user_events.user_id');
			$q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id');
			$q->addWhere('event_id = ' . $e['event_id']);
			$users = $q->loadList();	
			
			$owner = null;
			$q->addTable('users');
			$q->addJoin('contacts', 'c', 'users.user_contact = c.contact_id');
			$q->addWhere('user_id ='.$e['event_owner'] );
			$q->loadObject($owner);	

			$start_date =& new CDate($e['event_start_date']);
			$end_date =& new CDate($e['event_end_date']);	
			
			// create vEvent String	
			$this->addSum($e['event_title']);
			$this->addDesc($e['event_description']);
			$this->addCat($types[(int) $e['event_type']]);
			$this->addOrg($owner->contact_first_name.' '.$owner->contact_last_name, $owner->contact_email);
			$this->addStart($start_date);
			$this->addEnd($end_date);
			if ($e['event_recurs'] > 0)
				$this->addRec($e['event_recurs'], $e['event_times_recuring'], $end_date);
			
			foreach ($users as $user)
				$this->addAttendee($user['contact_first_name'] .' '. $user['contact_last_name'], $user['contact_email']);
			
			$this->addUrl(DP_BASE_URL . '/index.php?m=calendar&amp;a=view&amp;event_id=' . $e['event_id'] );
			$this->addRel($e['event_parent'], 'PARENT');
			$this->addCreated();
			$this->addUid($e['event_id']);
			$this->addSeq();
			if ($e['event_private'] == TRUE || $e['event_private'] == 1)
				$this->addClass('PRIVATE');
			else
				$this->addClass('PUBLIC');
			
			$this->addvEvent();
		}		
	}

	/**
	* Store an array parsed from a vcalendar file by the IMC class in dotproject events table
	* @param array vcalendar array
	* @param int	project/'calendar' for event_project storage 
	* @param bool Determine whether existing events for that project shall be purged or not
	* @param bool preserve the id info got from the input row
	* @return mixed true on 'no error' otherwise error info string
	*/
	// event_project is in fact the target calendar
	function icsParsedArrayToDpEvents($ics, $event_project, $purge_before_update = true, $preserve_id = false) {
	GLOBAL $AppUI;
	
		if ($purge_before_update) {
			// grab events_id to delete
			$q = new DBQuery;
			$q->addTable('events');
			$q->addQuery('event_id');
			$q->addWhere('events.event_project ='.$event_project);
			$events = $q->loadList();	

			// delete events
			$q->setDelete('events');
			$q->addWhere('events.event_project ='.$event_project);
			$q->exec();	
			$q->clear();
			
			// compile where clause for user_events
			$ev = 'event_id = "a" ';		// trick to avoid a 'OR'-if-clause down below
			foreach ($events as $eve) {
				$ev .= ' OR event_id ='. $eve['event_id'];
			}

			// delete user_events relations
			$q	= new DBQuery;
			$q->setDelete('user_events');
			$q->addWhere($ev);
			$q->exec();	
		}		

		$cat = dPgetSysVal('EventType');
		$tac = array_flip($cat);
		
		//get event types
		$et = array_flip($this->recurs);
		$errors = null;
		foreach ($ics['VCALENDAR'] as $ci) {	//one file can contain multiple iCal items
			foreach ($ci['VEVENT'] as $c) {		//each iCal item can contain multiple VEVENT items
				$obj = new CEvent();
				$eventValues = $this->icsObjectToArray($c, $tac, $et, $event_project, $preserve_id);

				// bind array to object
				if (!$obj->bind( $eventValues ))
					$errors .= $obj->getError()."\r";
		
				// store iCal data for this object
				if (($msg = $obj->store(false)))
					$errors .= $msg."\r";
			}
		}
		
		return empty($errors) ? true : $errors;
	}
	
	/**
	* Convert an array parsed from a vcalendar file by the IMC class to aray list
	* @param array vcalendar array
	* @return mixed true on 'no error' otherwise error info string
	*/
	function icsParsedArrayToList($ics) {
		global $AppUI;
	
		$cat = dPgetSysVal('EventType');
		$tac = array_flip($cat);
		
		//get event types
		$et = array_flip($this->recurs);
		$errors = null;

		//target array
		$events = array();		

		foreach ($ics['VCALENDAR'] as $ci) {	//one file can contain multiple iCal items
			foreach ($ci['VEVENT'] as $c) {		//each iCal item can contain multiple VEVENT items
				$eventValues = $this->icsObjectToArray($c, $tac, $et);

				// add current event data set to target array
				$events[] = $eventValues;
			}
		}
		
		return empty($errors) ? $events : $errors;
	}

	/** 
	 * internal function to create a dp database like data set from an icalendar 'object'
	 */
	function icsObjectToArray($c, $tac, $et, $event_project = 0, $preserve_id = false) {
		//set target calendar, i.e. define event_project
		$eventValues["event_project"] = $event_project;
		
		$e = $this->iCalDateToDateObj($c['DTEND'][0]['value'][0][0]);
		$s = $this->iCalDateToDateObj($c['DTSTART'][0]['value'][0][0]);
		
		if ($preserve_id == true)
			$eventValues['event_id'] = is_int($c['UID'][0]['value'][0][0]) ? $c['UID'][0]['value'][0][0] : 0;

		$eventValues['event_end_date'] = $e->format(FMT_DATETIME_MYSQL);
		$eventValues['event_start_date'] = $s->format(FMT_DATETIME_MYSQL);
		$eventValues['event_title'] = $c['SUMMARY'][0]['value'][0][0];
		$eventValues['event_description'] = $c['DESCRIPTION'][0]['value'][0][0];
		$eventValues['event_private'] = ($c['CLASS'][0]['value'][0][0] == 'PUBLIC') ? 0 : 1;
		$eventValues['event_parent'] = ($c['RELATED-TO'][0]['param'][0][0] == 'PARENT') ? $c['RELATED-TO'][0]['value'][0][0] : 0;
		$eventValues['event_owner'] = $AppUI->user_id;	// for instance set event_owner to importing user
		// could perhaps be guessed by CN and email from db
		$eventValues['event_type'] = $tac[$c['CATEGORIES'][0]['value'][0][0]];
		
		//recurrent events info
		if (!empty($c['RRULE'][0]['value'][0][0])) {
			$rt = vCalendar::recToDB($c['RRULE'][0]['value'][0][0]);
			$eventValues['event_recurs'] = $et[$rt['FREQ']];
			$eventValues['event_times_recuring'] = $rt['COUNT'];
		} else {
			$eventValues['event_recurs'] = 0;
		}
		
		return $eventValues;
	}
}

class CWebCalresource extends CDpObject {
	var $webcal_id = null;
	var $webcal_path = null;
	var $webcal_port = null;
	var $webcal_user = null;	
	var $webcal_pass = null;
	var $webcal_auto_import = null;
	var $webcal_auto_publish = null;
	var $webcal_auto_show = null;
	var $webcal_preserve_id = null;
	var $webcal_private_events = null;
	var $webcal_purge_events = null;
	var $webcal_eq_id = null;

	function CWebCalresource() {
		global $AppUI;
		
		$this->webcal_port = 80;
		$this->webcal_purge_events = 1;
		$this->webcal_user = $AppUI->user_username;
		$this->CDpObject('webcal_resources', 'webcal_id');
	}

	/**
	* Get associated Projects/Calendars for object
	*/
	function getCalendars() {
		$q	= new DBQuery;
		$q->addTable('webcal_projects');
		$q->addWhere('webcal_id ='. $this->webcal_id);
		$wp = $q->loadList();

		$cals = array();
		foreach ($wp as $w) {
			$cals[] = $w['project_id'];
		}
		
		return $cals;
	}

	function getExternalWebcalendars() {
		$q	= new DBQuery;
		$q->addTable('webcal_resources');
		$q->addWhere('webcal_auto_show = 1');
		$cals = $q->loadList();
	
		/*
		$cals = array();
		foreach ($wc as $w) {
			$cals[] = $w['webcal_id'];
		} */
		
		return $cals;
	}
	
	/**
	* Get Filter string based on associated Projects/Calendars for object
	*/
	function getEventFilter() {
		$q = new DBQuery;
		$q->addTable('webcal_projects', 'wp');
		$q->addWhere('webcal_id = ' . $this->webcal_id);
		$wi = $q->loadList();

		// establish the filter info based on the selected calendars
		$w = null;
		foreach ($wi as $c) {
			if (!is_null($w))
				$w .= ' OR ';
				
			$w .= 'event_project='.$c['project_id'];
		}
		
		return $w;
	}

	/** 
	 * auto publish a webcal resource to given place
	 */
	function autoPublish($event_project) {
		//global $AppUI;
		$q = new DBQuery;
		$q->addTable('webcal_resources', 'w');
		$q->addJoin('webcal_projects', 'wp', 'wp.webcal_id = w.webcal_id');
		$q->addWhere('project_id = ' . (empty($event_project))?'0':$event_project);
		$q->addWhere('webcal_auto_publish = 1');
		$wr = $q->loadList();

		foreach ($wr as $r) {	//process each autoPub rule
			$q = new DBQuery;
			$q->addTable('webcal_projects', 'wp');
			$q->addWhere('webcal_id = ' . $r['webcal_id']);
			$wi = $q->loadList();

			// establish the filter info based on the selected calendars
			$w = null;
			foreach ($wi as $c) {
				if (!is_null($w)) {
					$w .= ' OR ';
				}
					
				$w .= 'event_project='.$c['project_id'];
			}

			// instantiate new multiple vEvent object with filter info
				$v = new vCalendar();
				$v->addEventsByFilter($w, $r['webcal_private_events']);
				$ics = $v->genString();

			$r['webcal_path'] = 'http://'.$r['webcal_path'];

			// establish webDAV client connection
			$wdc = new WebDAVclient();
			$target_path = $wdc->pathInfo( $r['webcal_path'] );
			$wdc->setPath( $target_path );
			$wdc->setServer( $wdc->hostInfo( $r['webcal_path'] ) );
			$wdc->setPort($r['webcal_port']);
			$wdc->setUser($r['webcal_user']);
			$wdc->setPass($r['webcal_pass']);

			if (!$wdc->openConnection()) {
			//	$AppUI->setMsg( 'WebDAVClient: Could not open server connection', UI_MSG_ERROR );
			}

			// check if server supports webdav rfc 2518
			if (!$wdc->checkConnection()) {
			//	$AppUI->setMsg( 'WebDAVClient: Server does not support WebDAV or user/password may be wrong', UI_MSG_ERROR );
			}
			// put the ical content string to webdav collection as file
			$http_status = $wdc->put($target_path, $ics);
			$msgtype = ($http_status == '201' || $http_status == '204') ? UI_MSG_OK : UI_MSG_ERROR;
			//$AppUI->setMsg( 'WebDAVClient: Server Status '.$http_status, $msgtype );
			$wdc->closeConnection();
			//flush();
		}
	}
	
	/** 
	 * auto import webcal resources
	 * this function is automatically called from the event_queue scanner class
	 */
	function autoImport($mod, $type, $originator, $owner, &$args) {
		global $AppUI;
		
		extract($args);
		$webcal_id = $args['webcal_id'];
		
		$wcr = new CWebCalresource;
		$wcr->load($webcal_id);

		$wdc = new WebDAVclient();
		$target_path = $wdc->pathInfo( 'http://'.$wcr->webcal_path );
		$wdc->setPath( $target_path );
		$wdc->setServer( $wdc->hostInfo( 'http://'.$wcr->webcal_path ) );
		$wdc->setPort($wcr->webcal_port);
		$wdc->setUser($wcr->webcal_user);
		$wdc->setPass($wcr->webcal_pass);

		if (!$wdc->openConnection()) {
			
		}

		// check if server supports webdav rfc 2518
		if (!$wdc->checkConnection()) {
			
		}

		$ics = null;
		$http_status = $wdc->get($target_path, $ics);
		if ($http_status == '200') { 
			require_once($AppUI->getLibraryClass('PEAR/File/IMC/Parse'));

			// instantiate a parser object
			$parse = new File_IMC_Parse();

			// parse a iCal file and store the data
			// in $calinfo
			$calinfo = $parse->fromText($ics);
			$calendars = $wcr->getCalendars();

			// store the ical info array
			$v = new vCalendar;
			foreach ($calendars as $c) {
				$msg = $v->icsParsedArrayToDpEvents($calinfo, $c, $wcr->webcal_purge_events, $wcr->webcal_preserve_id);
			}
		}
		
		return true;
	}

	function delete() {
		$msg = parent::delete();
		if ($msg == null) {
			$q	= new DBQuery;
			$q->setDelete('webcal_projects');
			$q->addWhere('webcal_id ='.$this->webcal_id);
			$q->exec();	
		}
		
		return $msg;
	}

	function store($calendars, $event_project = 0, $purge_before_update = true, $preserve_id = false) {
		global $AppUI;
		//todo: check if update, then check if still autoImport.

		$msg = parent::store();
		
		// store the related calendars
		if ($msg == null) {
			foreach ($calendars as $c) {
				$q	= new DBQuery;
				$q->addTable('webcal_projects');
				$q->addInsert('webcal_id', $this->webcal_id);
				$q->addInsert('project_id', $c);
				$q->exec();	
				$q->clear();
			}
		} 

		// add autoImport event to event queue if necessary
		if ($msg == null && $this->webcal_auto_import > 0) {
			require_once $AppUI->getSystemClass('event_queue');
			$eq = new EventQueue;
			$vars = get_object_vars($this);
			$now = time();
			//$now += $this->webcal_auto_import * 60;
			//$this->webcal_eq_id = $eq->add(array('CWebCalresource', 'autoImport'), $vars, 'calendar', false, 0, 'calAutoImport', $now);
			$this->webcal_eq_id = $eq->add(array('CWebCalresource', 'autoImport'), $vars, 'calendar', false, 0, 'calAutoImport', $now);
		}

		// now that we've got the webcal_eq_id we'll 
		// have to update the object in the db
		$msg2 = parent::store();
		
		return $msg;
	}
}
?>