<?php
require_once( "classdefs/date.php" );

$date = new CDate();
$date->setDate( $thisYear, $thisMonth, $thisDay );
$tmpdate = $currentDate = $date;
if(empty($field))$field = "x";
$tmpdate->addDays(-7);
$urlPrevWeek = $tmpdate->toString( "thisYear=%Y&thisMonth=%m&thisDay=%d" );
$tmpdate->addDays(14);
$urlNextWeek = $tmpdate->toString( "thisYear=%Y&thisMonth=%m&thisDay=%d" );
$thisDay=0;
$sqldate = $thisYear . "-" . $thisMonth . "-" . $thisDay;
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

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD><img src="./images/icons/calendar.gif" alt="Calendar" border="0" width="42" height="42"></td>
	<TD nowrap><span class="title">Week View</span></td>
	<TD align="right" width="100%">&nbsp;</td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<TR>
	  <TD width="50%" nowrap><a href="./index.php?m=calendar">Month View</a></td>
	  <TD width="50%" align="right">&nbsp;</td>
</TR>
</table>

<table border=0 cellspacing=1 cellpadding=2 width="95%" class=bordertable>
<tr>
	<td align="center">
		<a href="<?php echo "?m=calendar&a=week_view&$urlPrevWeek&field=$field"; ?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
	</td>
	<td width="100%">
		<span style="font-size:12pt"><?php echo $date->toString( "Week %U %Y" ); ?></span>
	</td>
	<td align="center">
		<a href="<?php echo "?m=calendar&a=week_view&$urlNextWeek&field=$field"; ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></A>
	</td>
</tr>
</table>

<table border=0 cellspacing=1 cellpadding=2 width="95%" bgcolor="#cccccc" style="margin-width:4px;background-color:white">
<?php
$fmt1 = "<b>%d</b> %A";
$fmt2 = "%A <b>%d</b>";

for ($i=0; $i< 7; $i+=2) {
	// TODAY
	if( $currentDate->isToday() ) {
		$day1 = $currentDate->toString( "<font color=\"red\">$fmt1</font>" );
	} else {
		// HOLIDAY
		$day1 = $currentDate->toString($fmt1);
	}

	$day1url = "?m=calendar&a=day_view&" . $currentDate->toString("thisYear=%Y&thisMonth=%m&thisDay=%d" );

	// select next day
	$currentDate->addDays(1);

	if( $currentDate->isToday() ) {
		$day2 = $currentDate->toString( "<font color=\"red\">$fmt2</font>" );
//    } else if ( $cal->isFerienDate( $currentDate ) ) {
//			$day2 = $currentDate->toString( "<font color=\"#009044\">$fmt2</font>" );
	} else {
		$day2 = $currentDate->toString($fmt2);
	}

	$day2url = "?m=calendar&a=day_view&" . $currentDate->toString("thisYear=%Y&thisMonth=%m&thisDay=%d" );

	if( $bankLabel = $currentDate->getBankHoliday() != NULL ) {
		$ferienLabel = '<span class="ferienLabel">' . $bankLabel . '</span>';
	} else {
		$ferienLabel = "";
	}
?>
<tr>
	<td class="weekDay" style="width:50%;">
		<table style="width:100%;border-spacing:0;">
		<tr>
			<td><a href="<?php echo $day1url ?>"><?php echo $day1 ?></a> &nbsp; <?php echo $ferienLabel ?></td>
			<td align="right">
<?php
//        echo IconLink( "?_eventnew=1&_day=$daylocale", 'task/event.new' ),
//                        IconLink( "?_todonew=1&_duedate=$daylocale", 'task/todo.new' );
?>
			</td>
		</tr>
		</table>
<?php
  eventsForDay($currentDate, -1);
?>
	</td>
	<td class="weekDay">
		<table style="width:100%;border-spacing:0;">
		<tr>
			<td style="" >
<?php
//echo IconLink( "?_todonew=1&_duedate=$daylocale", 'task/todo.new' ),
//        IconLink( "?_eventnew=1&_day=$daylocale", 'task/event.new' );
?>
			</td>
			<td align="right"><?php echo $ferienLabel ?> &nbsp; <a href="<?php echo $day2url ?>"><?php echo $day2 ?></a></td>
		</tr>
		</table>
<?php
			eventsForDay($currentDate, 0);
?>
	</td>
</tr>
<?php
		// select next day
	$currentDate->addDays(1);
} // end for
?>
<tr>
	<td colspan="<?php echo $numcols + 1 ?>" align="right" bgcolor="#efefe7">
		<font face='Tahoma, arial, helvetica, sans-serif' size='1'>
		<A href="./index.php?m=calendar&a=week_view<?php echo "&thisYear=" . $todaysYear . "&thisMonth=" . $todaysMonth . "&thisDay=" . $todaysDay;?>">Today</A>
		</font>
	</td>
</tr>
</TABLE>

<?php
function eventsForDay( $eventDay, $addDay ) {
	$eventDay->addDays( $addDay );
	$items = eventsForDate( $eventDay->getDay(), $eventDay->getMonth(), $eventDay->getYear() );
	echo "<table>";
	while (list( $key, $val ) = each( $items )) {
		echo "<TR><TD bgcolor=" . $val["color"] .">";

		if ($val["type"] == "p") {
					echo "<a href=./index.php?m=projects&a=view&project_id=" . $val["id"] ."><B>";
		} else if ($val["type"] == "t") {
					if (intval( $val["priority"] ) <> 0) {
							echo "<img src=\"./images/icons/" . $val["priority"] .".gif\" border=0 width=13 height=16 align=absmiddle>";
					}
					echo "<a href=./index.php?m=tasks&a=view&task_id=" . $val["id"] .">";
		} else if ($val["type"] == "e") {
					echo "<a href=./index.php?m=calendar&a=addedit&event_id=" . $val["id"] ."><i>";
		}
		echo '<span style="color:' . bestColor( $val["color"], '#ffffff', '#272727' ) . ';text-decoration:none;">' .  $val["title"] ;
		echo "</i></span></a></td></tr>";
	}
	echo "</table>";
}

?>
