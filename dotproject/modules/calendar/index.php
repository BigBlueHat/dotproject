<?php /* $Id$ */
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

// restore/get the company filter if specified
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', $_REQUEST['company_id'] );
}
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;

// get the passed timestamp (today if none)
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : null;
$this_month = new CDate( $uts );

// pull the companies list
$sql = "SELECT company_id,company_name FROM companies ORDER BY company_name";
$companies = arrayMerge( array( 0 => 'All' ), db_loadHashList( $sql ) );

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
$strMaxLen = 20;

// assemble the links for the tasks
foreach ($tasks as $row) {
// the link
	$link['href'] = "?m=tasks&a=view&task_id=".$row['task_id'];
	$link['alt'] = $row['project_name'].":\n".$row['task_name'];

// the link text
	if (strlen( $row['task_name'] ) > $strMaxLen) {
		$row['task_name'] = substr( $row['task_name'], 0, $strMaxLen ).'...';
	}
	$link['text'] = '<span style="color:'.bestColor($row['color']).';background-color:#'.$row['color'].'">'.$row['task_name'].'</span>';

// determine which day(s) to display the task
	$start = new CDate( db_dateTime2unix( $row['task_start_date'] ) );
	$end = new CDate( db_dateTime2unix( $row['task_end_date'] ) );
	$durn = $row['task_duration'];

	if ($start->inMonth( $this_month )) {
		$temp = $link;
		$temp['alt'] = "START [".($durn < 24 ? $durn.' hours' : floor($durn/24).' days')."]\n".$link['alt'];
		$links[$start->getDay()][] = $temp;
	}
	if ($end->inMonth( $this_month ) && $start->daysTo( $end ) != 0) {
		$temp = $link;
		$temp['alt'] = "FINISH\n".$link['alt'];
		$links[$end->getDay()][] = $temp;
	}
// fill in between start and finish based on duration
	if ($durn > 24) {
	// notes:
	// start date is not in a future month, must be this or past month
	// start date is counted as one days work
	// business days are not taken into account
		$target = $start;
		$target->addDays( (int) ($durn / 24) );
		$day = $this_month->getDay();			// day of month
		$dim = $this_month->daysInMonth();		// days in month
		$d2t = $this_month->daysTo( $target );	// days to target
		$d2s = $this_month->daysTo( $start );	// days to start date
		$d2e = $this_month->daysTo( $end );		// days to end date

		$s = max( $d2s + $day + 1, 1 );
		$e = min( $d2t + $day - 1, $d2e - 1, $dim );
		
		for( $i=$s; $i <= $e; $i++ ) {
			$links[$i][] = $link;
		}
	}
}

// assemble the links for the events
foreach ($events as $row) {
	$start = new CDate( $row['event_start_date'] );

// the link
	$link['href'] = "?m=calendar&a=addedit&event_id=".$row['event_id'];
	$link['alt'] = $row['event_description'];
	$link['text'] = '<img src="./images/obj/event.gif" width="16" height="16" border="0" alt="">'
		.'<span class="event">'.$row['event_title'].'</span>';
	$links[$start->getDay()][] = $link;
}

#echo '<pre>';print_r($events);echo '</pre>';
?>

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" name="pickCompany">
<tr>
	<td><img src="./images/icons/calendar.gif" alt="Calendar" border="0" width="42" height="42"></td>
	<td nowrap><h1>*</h1></td>
	<td align="right" width="100%">
		Company:
<?php
	echo arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id );
?>
	</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_MOCAL' );?></td>
</tr>
<?php	
	// Bizarre fix for strange $copmany_id="1company_id=1" bug
	echo '<input type="hidden" name="dummy">';
?>
</form>
</table>

<script language="javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&uts='+uts;
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&a=week_view&uts='+uts;
}
</script>

<table cellspacing="0" cellpadding="0" border="0" width="98%"><tr><td>
<?php
// create the main calendar
$cal = new CMonthCalendar( $this_month  );
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
