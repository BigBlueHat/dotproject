<?php /* CALENDAR $Id$ */
$AppUI->savePlace();
global $locale_char_set;

require_once( $AppUI->getModuleClass( 'tasks' ) );

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
$first_time = new CDate( Date_calc::beginOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( Date_calc::endOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$last_time->setTime( 23, 59, 59 );

$prev_week = new CDate( Date_calc::beginOfPrevWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$next_week = new CDate( Date_calc::beginOfNextWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );

$tasks = CTask::getTasksForPeriod( $first_time, $last_time, $company_id );
$events = CEvent::getEventsForPeriod( $first_time, $last_time );

$links = array();

// assemble the links for the tasks
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 50, $company_id );

// assemble the links for the events
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_events.php" );
getEventLinks( $first_time, $last_time, $links, 50 );

// setup the title block
$titleBlock = new CTitleBlock( 'Week View', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&date=".$this_week->format( FMT_TIMESTAMP_DATE ), "month view" );
$titleBlock->addCell( $AppUI->_('Event Filter') . ':');
$titleBlock->addCell(
	arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"',
	$event_filter ), '', "<Form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickFilter'>", '</form>'
);
$titleBlock->show();

$show_day = $this_week;
for ($i = 0; $i < 7; $i++) 
{
	$week[] = $show_day;
	$weekStamps[] = $show_day->format( FMT_TIMESTAMP_DATE );
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

<style type="text/css">
TD.weekDay  {
	height:120px;
	vertical-align: top;
	padding: 1px 4px 1px 4px;
	border-bottom: 1px solid #ccc;
	border-right: 1px solid  #ccc;
	text-align: left;
}
</style>
