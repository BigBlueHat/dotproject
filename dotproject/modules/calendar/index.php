<?php /* CALENDAR $Id$ */
$AppUI->savePlace();

dPsetMicroTime();

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>'All' ), $companies );

#echo '<pre>';print_r($events);echo '</pre>';
// setup the title block
$titleBlock = new CTitleBlock( 'Monthly Calendar', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Company').':' );
$titleBlock->addCell(
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany">', '</form>'
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

<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td>
<?php
// establish the focus 'date'
$date = new CDate( $date );

// prepare time period for 'events'
$first_time = new CDate( $date );
$first_time->setDay( 1 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDaysInMonth() );

$links = array();

// assemble the links for the tasks
require_once( $AppUI->getConfig( 'root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 20, $company_id );

// assemble the links for the events
require_once( $AppUI->getConfig( 'root_dir' )."/modules/calendar/links_events.php" );
getEventLinks( $first_time, $last_time, $links, 20 );

// create the main calendar
$cal = new CMonthCalendar( $date  );
$cal->setStyles( 'motitle', 'mocal' );
$cal->setLinkFunctions( 'clickDay', 'clickWeek' );
$cal->setEvents( $links );

echo $cal->show();
//echo '<pre>';print_r($cal);echo '</pre>';

// create the mini previous and next month calendars under
$minical = new CMonthCalendar( $cal->prev_month );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" width="200">'.$minical->show().'</td>';
echo '<td align="center" width="100%">&nbsp;</td>';

$minical->setDate( $cal->next_month );

echo '<td align="center" width="200">'.$minical->show().'</td>';
echo '</tr></table>';
?>
</td></tr></table>
