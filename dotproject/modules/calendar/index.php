<?php /* CALENDAR $Id$ */
$AppUI->savePlace();
include_once( $AppUI->getModuleClass( 'companies' ) );

// restore/get the company filter if specified
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// pull the companies list
$company = new CCompany();
$companies = $company->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>'All' ), $companies );



$this_month = new CDate(  );
// pull the tasks and events for the month
$first_time = $this_month;
$first_time->setDay( 1 );
$first_time->setTime( 0, 0, 0 );

$last_time = $this_month;
$last_time->setDay( $this_month->daysInMonth() );
$last_time->setTime( 23, 59, 59 );

$tasks = getTasksForPeriod( $first_time, $last_time, $company_id );
$events = getEventsForPeriod( $first_time, $last_time );
//echo '<pre>';print_r($tasks);echo '</pre>';

$links = array();

// assemble the links for the tasks
addTaskLinks( $tasks, $first_time, $last_time, $links, $strMaxLen );

// assemble the links for the events
foreach ($events as $row) {
	$start = new CDate( $row['event_start_date'] );

// the link
	$link['href'] = "?m=calendar&a=view&event_id=".$row['event_id'];
	$link['alt'] = $row['event_description'];
	$link['text'] = '<img src="./images/obj/event.gif" width="16" height="16" border="0" alt="" />'
		.'<span class="event">'.$row['event_title'].'</span>';
	$links[$start->getDay()][] = $link;
}

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
// create the main calendar
$date = new Date( $date ? "{$date}000000" : $date );
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
