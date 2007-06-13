<?php /* CALENDAR $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$AppUI->savePlace();
global $locale_char_set;

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );

$companies = new CCompany();
$projects = new CProject();

$perms =& $AppUI->acl();
$tasks_filters_selection = array(
//'tasks_company' => $companies->getAllowedRecords($AppUI->user_id, 'company_id, company_name', 'company_name'),
'task_owner' => $perms->getPermittedUsers('calendar'),
//'task_creator' => $perms->getPermittedUsers('calendar'),
'task_project' => $projects->getAllowedRecords($AppUI->user_id, 'project_id, project_name', 'project_name'),
'task_company' => $companies->getAllowedRecords($AppUI->user_id, 'company_id, company_name', 'company_name'));


// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

$event_filter = $AppUI->checkPrefState('CalIdxFilter', @$_REQUEST['event_filter'], 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// establish the focus 'date'
$this_week = new CDate( $date );
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();

// prepare time period for 'events'

$first_time = new CDate($date);
$first_time->addSeconds(SEC_DAY * -7 );
$first_time->setTime( 0, 0, 0 );
$first_time->addSeconds( -1 );

$last_time = new CDate($date);
$first_time->addSeconds(SEC_DAY * -7 );
$last_time->setTime( 23, 59, 59 );

$prev_week = new CDate($date);
$prev_week->addSeconds(SEC_DAY * -7 );
$next_week = new CDate($date);
$next_week->addSeconds(SEC_DAY * 7 );

$tasks = CTask::getTasksForPeriod( $first_time, $last_time, $company_id );
$events = CEvent::getEventsForPeriod( $first_time, $last_time );

if (isset($_POST['show_form']))
{
	if (isset($_POST['show_events']))
		$AppUI->setState('CalIdxShowEvents', true);
	else if ($AppUI->getState('CalIdxShowEvents', '') === '')
		$AppUI->setState('CalIdxShowEvents', true);
	else
		$AppUI->setState('CalIdxShowEvents', false);

	if (isset($_POST['show_tasks']))
		$AppUI->setState('CalIdxShowTasks', true);
	else
		$AppUI->setState('CalIdxShowTasks', false);
}

$show_events = $AppUI->getState('CalIdxShowEvents', true);
$show_tasks = $AppUI->getState('CalIdxShowTasks', false);

$links = array();

// assemble the links for the tasks
if ($show_tasks)
{
	require_once(DP_BASE_DIR.'/modules/calendar/links_tasks.php');
	getTaskLinks($first_time, $last_time, $links, 50, $filters);
}

// assemble the links for the events
if ($show_events)
{
	require_once(DP_BASE_DIR.'/modules/calendar/links_events.php');
	getEventLinks( $first_time, $last_time, $links, 50 );
}

// setup the title block
$titleBlock = new CTitleBlock( 'Week View', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&amp;date=".$this_week->format( FMT_TIMESTAMP_DATE ), "month view" );

$titleBlock->addCell('
<form action="' . str_replace('&', '&amp;', $_SERVER['REQUEST_URI']) . '" method="post" name="filters">
	<input type="hidden" name="show_form" value="1" />
	<input type="checkbox" name="show_events" value="1" ' . ($show_events?'checked ':'') . 'onchange="document.filters.submit()" /> show events
	<input type="checkbox" name="show_tasks" value="1" ' . ($show_tasks?'checked ':'') . 'onchange="document.filters.submit()" /> show tasks
</form>', '', '', '');

if ($show_tasks)
	$filters = $titleBlock->addFiltersCell($tasks_filters_selection);

/*
$titleBlock->addCell( $AppUI->_('Event Filter') . ':');
$titleBlock->addCell(
	arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"',
	$event_filter ), '', "<form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickFilter'>", '</form>'
);
*/
$titleBlock->show();

$show_day = $this_week;
for ($i = 0; $i < 7; $i++) 
{
	$week[] = $show_day;
	$weekStamps[] = $show_day->format( FMT_TIMESTAMP_DATE );
	$show_day = new CDate($show_day);
	$show_day->addSeconds( 24*3600 );
}

$today = new CDate();
$tpl->assign('prev_week', $prev_week);
$tpl->assign('next_week', $next_week);
$tpl->assign('week', $week);

$tpl->assign('weekStamps', $weekStamps);
$tpl->assign('today', $today);
$tpl->assign('todayStamp', $today->format(FMT_TIMESTAMP_DATE));

$tpl->assign('links', $links);

$tpl->displayFile('view.week');
?>