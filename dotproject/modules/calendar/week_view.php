<?php
$denyRead = getDenyRead( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

// get the passed timestamp (today if none)
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : null;

$this_week = new CDate( $uts );
$this_week->setTime( 0,0,0 );
$this_week->setWeekday( LOCALE_FIRST_DAY );

$prev_week = $this_week;
$prev_week->addDays( -7 );

$next_week = $this_week;
$next_week->addDays( +7 );

$thisDay=0;

$events = getEventsForPeriod( $this_week, $next_week );

// assemble the links for the events
$links = array();
foreach ($events as $row) {
	$start = new CDate( $row['event_start_date'] );
// the link
	$link['href'] = "?m=calendar&a=addedit&event_id=".$row['event_id'];
	$link['alt'] = $row['event_description'];
	$link['text'] = '<img src="./images/obj/event.gif" width="16" height="16" border="0" alt="">'
		.'<span class="event">'.$row['event_title'].'</span>';
	$links[$start->getDay()][] = $link;
}

//echo '<pre>';print_r($links);echo '</pre>';
//echo '<pre>';print_r($next_week);echo '</pre>';
//echo $this_week->getTimestamp().','.$next_week->getTimestamp();

$crumbs = array();
$crumbs["?m=calendar"] = "month view";
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

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td><img src="./images/icons/calendar.gif" alt="Calendar" border="0" width="42" height="42"></td>
	<td nowrap><span class="title">Week View</span></td>
	<td align="right" width="100%">&nbsp;</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_WEEKCAL' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right"></td>
</tr>
</table>

<table border="0" cellspacing="1" cellpadding="2" width="98%" class="motitle">
<tr>
	<td>
		<a href="<?php echo '?m=calendar&a=week_view&uts='.$prev_week->getTimestamp(); ?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
	</td>
	<th width="100%">
		<span style="font-size:12pt"><?php echo $AppUI->_( 'Week' ).' '.$this_week->toString( "%U - %Y" ); ?></span>
	</th>
	<td>
		<a href="<?php echo '?m=calendar&a=week_view&uts='.$next_week->getTimestamp(); ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></A>
	</td>
</tr>
</table>

<table border="0" cellspacing="1" cellpadding="2" width="98%" style="margin-width:4px;background-color:white">
<?php
$column = 0;
$format = array( "<b>%d</b> %A", "%A <b>%d</b>" );
$show_day = $this_week;

for ($i=0; $i < 7; $i++) {
	$day  = $show_day->getDay();
	$href = "?m=calendar&a=day_view&uts=" . $show_day->getTimestamp();

	$s = '';
	if ($column == 0) {
		$s .= '<tr>';
	}
	$s .= '<td class="weekDay" style="width:50%;">';

	$s .= '<table style="width:100%;border-spacing:0;">';
	$s .= '<tr>';
	$s .= '<td><a href="'.$href.'"><?php echo $day1 ?>';

	$s .= $show_day->isToday() ? '<span style="color:red">' : '';
	$s .= $show_day->toString( $format[$column] );
	$s .= $show_day->isToday() ? '</span>' : '';
	$s .= '</a></td></tr>';

	$s .= '<tr><td>';

	if (isset( $links[$day] )) {
		foreach ($links[$day] as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$alt = isset($e['alt']) ? $e['alt'] : null;

			$s .= "<br />";
			$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
			$s .= "{$e['text']}";
			$s .= $href ? '</a>' : '';
		}
	}

	$s .= '</td></tr></table>';

	$s .= '</td>';
	if ($column == 1) {
		$s .= '</tr>';
	}
	$column = 1 - $column;

// select next day
	$show_day->addDays(1);
	echo $s;
}
?>
<tr>
	<td colspan="2" align="right" bgcolor="#efefe7">
		<a href="./index.php?m=calendar&a=week_view"><?php echo $AppUI->_('today');?></A>
	</td>
</tr>
</table>
