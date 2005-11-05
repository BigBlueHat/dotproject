<?php /* CALENDAR $Id$ */
global $tab, $locale_char_set, $date;
$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany', $AppUI->user_company);

$event_filter = $AppUI->checkPrefState('CalIdxFilter', @$_REQUEST['event_filter'], 'EVENTFILTER', 'my');

$AppUI->setState( 'CalDayViewTab', dPgetParam($_GET, 'tab', $tab) );
$tab = $AppUI->getState( 'CalDayViewTab' ,'0');

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', NULL );
// establish the focus 'date'
$this_day = new CDate( $date );
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// get current week
$this_week = Date_calc::beginOfWeek ($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY );

// prepare time period for 'events'
$first_time = new CDate( $date);
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );

$last_time = new CDate( $date );
$last_time->setTime( 23, 59, 59 );

$prev_day = new CDate( Date_calc::prevDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );
$next_day = new CDate( Date_calc::nextDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Day View', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&date=".$this_day->format( FMT_TIMESTAMP_DATE ), "month view" );
$titleBlock->addCrumb( "?m=calendar&a=week_view&date=".$this_week, "week view" );
$titleBlock->addCell(
	'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
	'<form action="?m=calendar&a=addedit&date=' . $this_day->format( FMT_TIMESTAMP_DATE )  . '" method="post">', '</form>'
);
$titleBlock->show();


$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );

$minical->setDate( $minical->prev_month );
$tpl->assign('minical_prev', $minical->show());
$minical->setDate( $minical->next_month );
$tpl->assign('minical', $minical->show());
$minical->setDate( $minical->next_month );
$tpl->assign('minical_next', $minical->show());

$tabBox = new CTabBox( "?m=calendar&a=day_view&date=" . $this_day->format( FMT_TIMESTAMP_DATE ), "", $tab );
$tabBox->add( $dPconfig['root_dir'].'/modules/calendar/vw_day_events', 'Events' );
$tabBox->add( $dPconfig['root_dir'].'/modules/calendar/vw_day_tasks', 'Tasks' );
$tabBox->loadExtras('calendar', 'day_view');

//$tabBox->show();
$tpl->assign('tabbox', $tabBox->fetch());
$tpl->assign('show_minical', $dPconfig['cal_day_view_show_minical']);

$tpl->assign('prev_day', $prev_day);
$tpl->assign('next_day', $next_day);
$tpl->assign('today', htmlentities($this_day->format( "%A" ), ENT_COMPAT, $locale_char_set).', '.$this_day->format( $df ));

$tpl->displayFile('view.day');

?>
<script language="javascript">
function clickDay( idate, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+idate;
}
</script>