<?php /* CALENDAR $Id$ */
$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany' ) !== NULL ? $AppUI->getState( 'CalIdxCompany' ) : $AppUI->user_company;

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// establish the focus 'date'
$this_day = new CDate( $date );
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// prepare time period for 'events'
$first_time = $this_day;
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );

$last_time = $this_day;
$last_time->setTime( 23, 59, 59 );

$prev_day = new CDate( Date_calc::prevDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );
$next_day = new CDate( Date_calc::nextDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Day View', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&date=".$this_day->format( FMT_TIMESTAMP_DATE ), "month view" );
$titleBlock->addCrumb( "?m=calendar&a=week_view&date=".$this_day->format( FMT_TIMESTAMP_DATE ), "week view" );
$titleBlock->show();
?>
<script language="javascript">
function clickDay( idate, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+idate;
}
</script>

<style type="text/css">
table.tbl td.event {
	background-color: #f0f0f0;
}
</style>

<table width="100%" cellspacing="0" cellpadding="4">
<tr>
	<td valign="top">
		<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
		<tr>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&date='.$prev_day->format( FMT_TIMESTAMP_DATE ); ?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></a>
			</td>
			<th width="100%">
				<?php echo $this_day->format( "%A, %d %B %Y" ); ?>
			</th>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&date='.$next_day->format( FMT_TIMESTAMP_DATE ); ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></a>
			</td>
		</tr>
		</table>

<?php
// tabbed information boxes
$tabBox = new CTabBox( "?m=calendar&a=day_view&date=" . $this_day->format( FMT_TIMESTAMP_DATE ),
	"{$AppUI->cfg['root_dir']}/modules/calendar/", $tab );
$tabBox->add( 'vw_day_events', 'Events' );
$tabBox->add( 'vw_day_tasks', 'Tasks' );
$tabBox->show();
?>

	</td>
	<td valign="top" width="175">
<?php
$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->setLinkFunctions( 'clickDay' );

$minical->setDate( $minical->prev_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table>';
?>
	</td>
</tr>
</table>