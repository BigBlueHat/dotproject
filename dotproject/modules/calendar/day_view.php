<?php /* CALENDAR $Id$ */
$AppUI->savePlace();

// get the passed timestamp (today if none)
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : null;
$company_id = $AppUI->user_company;

$this_day = new CDate( $uts );
$this_day->setTime( 0,0,0 );

$prev_day = $this_day;
$prev_day->addDays( -1 );

$next_day = $this_day;
$next_day->addDays( +1 );

$tasks = getTasksForPeriod( $this_day, $next_day, $company_id );
$events = getEventsForPeriod( $this_day, $next_day );

$links = array();

// override standard length
$strMaxLen = 50;
addTaskLinks( $tasks, $this_day, $next_day, $links, $strMaxLen );

foreach ($events as $row) {
	$start = new CDate( $row['event_start_date'] );
// the link
	$link['href'] = "?m=calendar&a=addedit&event_id=".$row['event_id'];
	$link['alt'] = $row['event_description'];
	$link['text'] = '<img src="./images/obj/event.gif" width="16" height="16" border="0" alt="">'
		.'<span class="event">'.$row['event_title'].'</span>';
	$links[$start->getDay()][] = $link;
}


$crumbs = array();
$crumbs["?m=calendar"] = "month view";
$crumbs["?m=calendar&a=week_view&uts=$uts"] = "week view";
?>
<script language="javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&uts='+uts;
}
</script>

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/calendar.gif" alt="" border="0" width="42" height="42"></td>
	<td nowrap><h1>Day View</h1></td>
	<td align="right" width="100%">&nbsp;</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_DAYCAL' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right">&nbsp;</td>
</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="4">
<tr>
	<td valign="top">
		<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
		<tr>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&uts='.$prev_day->getTimestamp(); ?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></a>
			</td>
			<th width="100%">
				<?php echo $this_day->toString( "%A, %d %B %Y" ); ?>
			</th>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&uts='.$next_day->getTimestamp(); ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></a>
			</td>
		</tr>
		</table>

		<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">
	<?php
		$s = '';
		if (isset( $links[$this_day->getDay()] )) {
			foreach ($links[$this_day->getDay()] as $e) {
				$href = isset($e['href']) ? $e['href'] : null;
				$alt = isset($e['alt']) ? $e['alt'] : null;

				$s .= "<tr><td>";
				$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
				$s .= "{$e['text']}";
				$s .= $href ? '</a>' : '';
				$s .= '</td></tr>';
			}
		}
		echo $s;
	?>
		</table>
	</td>
	<td valign="top" width="200">
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