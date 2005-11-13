<?php /* CALENDAR $Id$ */
$AppUI->savePlace();

dPsetMicroTime();

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany', $AppUI->user_company);

$proj = new CProject();

$r  = new DBQuery;
$r->addTable('projects');
$r->addQuery('project_id, CONCAT(c.company_name,"::", project_short_name) AS project_short_name');
$r->addJoin('companies', 'c', 'project_company = c.company_id'); 
if ($company_id > 0){
	$r->addWhere('project_company='.$company_id);
}
if ($calendar_filter > 0){
	$r->addWhere('project_id='.$calendar_filter);
}
$proj->setAllowedSQL($AppUI->user_id, $r);
$projects = $r->loadHashList();
$r->clear();
$calendar_filter_list = arrayMerge( array( '-1'=>$AppUI->_('Personal Calendar'), '0'=>$AppUI->_('Unspecified Calendar') ) , $projects );

// retrieve any state parameters
if (isset( $_REQUEST['calendar_filter'] )) {
	$AppUI->setState( 'CalIdxCalFilter', intval( $_REQUEST['calendar_filter'] ) );
}
$calendar_filter = $AppUI->getState( 'CalIdxCalFilter', '0');

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', @$_REQUEST['event_filter'], 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

#echo '<pre>';print_r($events);echo '</pre>';
// setup the title block
$titleBlock = new CTitleBlock( 'Monthly Calendar', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&a=calmgt", "calendar management" );
$titleBlock->addCrumb( "?m=calendar&a=eventimport&dialog=0", "import icalendar" );
$titleBlock->addCell( $AppUI->_('Company').':' );
$titleBlock->addCell(
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany">', '</form>'
);
$titleBlock->addCell( $AppUI->_('Calendar Filter') . ':');
$titleBlock->addCell(
	arraySelect($calendar_filter_list, 'calendar_filter', 'onChange="document.pickCalFilter.submit()" class="text"',
	$calendar_filter, true ), '', "<Form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickCalFilter'>", '</form>'
);
$titleBlock->addCell( $AppUI->_('Event Filter') . ':');
$titleBlock->addCell(
	arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"',
	$event_filter, true ), '', "<Form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickFilter'>", '</form>'
);
$titleBlock->show();
?>

<script language="javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+uts;
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&a=week_view&date='+uts;
}
</script>


<?php
// establish the focus 'date'
$date = new CDate( $date );

// prepare time period for 'events'
$first_time = new CDate( $date );
$first_time->setDay( 1 );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDaysInMonth() );
$last_time->setTime( 23, 59, 59 );

$links = array();

// assemble the links for the tasks
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 20, $company_id );

// assemble the links for the events
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_events.php" );
getEventLinks( $first_time, $last_time, $links, 20 );
getExternalWebcalEventLinks( $first_time, $last_time, $links, 20 );

// create the main calendar
$cal = new CMonthCalendar( $date  );
$cal->setStyles( 'motitle', 'mocal' );
$cal->setLinkFunctions( 'clickDay', 'clickWeek' );
$cal->setEvents( $links );

$tpl->assign('cal', $cal->show());
//echo '<pre>';print_r($cal);echo '</pre>';

// create the mini previous and next month calendars under
$minical = new CMonthCalendar( $cal->prev_month );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );

$tpl->assign('cal_prev', $minical->show());
$minical->setDate( $cal->next_month );
$tpl->assign('cal_next', $minical->show());

$tpl->display('calendar/view.month.html');
?>
