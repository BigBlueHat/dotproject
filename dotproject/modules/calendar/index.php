<?php
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// Set Day, Month, Year
if (empty( $thisMonth )) $thisMonth = date( "n", time() );
if (empty( $thisYear )) $thisYear = date( "Y", time() );
if (empty( $thisDay )) $thisDay = date( "d", time() );
if ($thisDay < 1) $thisDay = 1;

if (empty( $todaysDay )) $todaysDay = date( "d", time() );
if (empty( $todaysMonth )) $todaysMonth = intval( date( "m", time() ) );
if (empty( $todaysYear )) $todaysYear = date( "Y", time());
if (empty( $field )) $field = "x";
$day = 0;

// Figure out the last day of the month
$lastday[1]=31;
// Check for Leap Years
if (checkdate( $thisMonth, 29, $thisYear )) {
	$lastday[2] = 29; 
} else {
	$lastday[2]=28; 
}
$lastday[3]=31;
$lastday[4]=30;
$lastday[5]=31;
$lastday[6]=30;
$lastday[7]=31;
$lastday[8]=31;
$lastday[9]=30;
$lastday[10]=31;
$lastday[11]=30;
$lastday[12]=31;

//Short Day names
$dayNamesShort = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );

if ($thisDay > $lastday["$thisMonth"]) {
	$thisDay = $lastday["$thisMonth"];
}

$sqldate = $thisYear . "-" . $thisMonth . "-" . $thisDay;

$prevYear = $thisYear;
$prevMonth = $thisMonth - 1;
if ($prevMonth < 1) {
	$prevMonth = $prevMonth + 12; $prevYear--;
}
if ($lastday["$prevMonth"] > $thisDay) {
	$moveday = $thisDay;
} else {
	$moveday =$lastday["$prevMonth"];
}

$nextYear = $thisYear;
$nextMonth = $thisMonth+1;
if ($nextMonth > 12) {
	$nextMonth = $nextMonth - 12;
	$nextYear++;
}
?>

<html>
<head>
<SCRIPT language="javascript">
function setClose( x, y, z ) {
	z =  z - 1999;
	if ("<?php echo $field;?>" != "Actual") {
		x = x-1;
		y = y-1;
	}

	var form = window.opener.document.AddEdit;
	form.<?php echo $field;?>MM_int.selectedIndex = x;
	form.<?php echo $field;?>DD_int.selectedIndex = y;
	form.<?php echo $field;?>YYYY_int.selectedIndex = z;
	window.close();
}
</script>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickCompany">
<TR>
	<TD><img src="./images/icons/calendar.gif" alt="<?php echo ptranslate("Calendar");?>" border="0" width="42" height="42"></td>
	<TD nowrap><span class="title">Monthly Calendar</span></td>
	<TD align="right" width="100%">
		Company: <select name="company_id" onChange="document.pickCompany.submit()" style="font-size:8pt;font-family:verdana;">
		<option value="0" <?php if($company_id == 0)echo " selected" ;?> >all
	<?php

	$csql = "select company_id,company_name from companies order by company_name";
	$crc = mysql_query($csql);

	while ( $row = mysql_fetch_array( $crc ) ) {
		echo "<option value=" . $row["company_id"];
		if ($row["company_id"] == $company_id) {
			echo " selected";
		}
		echo ">" . $row["company_name"] ;
	}?>
		</select><br>

		<?php include ("./includes/create_new_menu.php");?>
	</td>
</tr>
</form>
</TABLE>

<table border=0 cellspacing=1 cellpadding=2 width="95%">
<tr>
	<td align=center>
		<a href="<?php  echo("./index.php?m=calendar&thisYear=" . $prevYear . "&thisMonth=" . $prevMonth . "&thisDay=" . $moveday ."&field=" . $field);?>"><img src="./images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
	</td>
	<td width="100%">
		<b><?php echo strftime("%B", mktime(0,0,0,$thisMonth,1,$thisYear));?> <?php echo $thisYear?></b></font>
	</td>
	<td align=center>
		<?php  echo "<a href='./index.php?m=calendar&thisYear=" . $nextYear . "&thisMonth=" . $nextMonth . "&thisDay=" . $moveday ."&field=" . $field ."'>";?><img src="./images/next.gif" width="16" height="16" alt="next" border="0"></A>
	</td>
</tr>
</table>

<table border=0 cellspacing=1 cellpadding=2 width="95%" bgcolor="#cccccc">
<tr>
	<TD>&nbsp;</TD>
<?php   // print days across top
for( $i = 0; $i <= 6; $i++ ) { ?>
	<td align=center width="14%"><b><?php echo $dayNamesShort["$i"];?></b></td>
<?php } ?>
</tr>

<?php
	$firstDay = date( 'w', mktime( 0, 0, 0, $thisMonth, 1, $thisYear ) );
	$dayRow = 0;

	if ($firstDay > 0) {
		echo "<tr height=80><TD valign=top><A href='./index.php?m=calendar&a=week_view&day=-" .$firstDay . "&month=". $thisMonth. "&year=". $thisYear. "'><img src=./images/week.gif width=12 height=39 alt=week view border=0></A></TD>\n";
		while ($dayRow < $firstDay) {
			echo "<td align=right bgcolor='#ffffff' height='80'>&nbsp;</td>\n";
			$dayRow += 1;
		}
	}

	while ($day < $lastday["$thisMonth"]) {
		$dayp = $day + 1;

		if (($dayRow % 7) == 0) {
			echo " </tr>\n<tr height=80><TD valign=top><A href='./index.php?m=calendar&a=week_view&day=" .$dayp . "&month=". $thisMonth. "&year=". $thisYear. "'><img src=./images/week.gif width=12 height=39 alt=week view border=0></A></TD>\n";
		}

		$datestr = $thisYear ."-" .  $thisMonth ."-" .  $dayp;

		if ($dayp == $thisDay && empty($drill)) {
			$bgcolor = "efefe7";
			$txtcolor = "black";
		} else {
			$bgcolor = "#efefe7";
			$txtcolor = 'black';
		}
		$items = eventsForDate( $dayp, $thisMonth, $thisYear );

		echo "<td valign=top bgcolor=$bgcolor><A href='./index.php?m=calendar&a=day_view&thisMonth=" . $thisMonth . "&thisYear=" . $thisYear . "&thisDay=" . $dayp . "'>" . $dayp . "</A>";
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
<?php
	while (list( $key, $val ) = each( $items )) {
		$r = hexdec(substr($val["color"], 0, 2));
		$g = hexdec(substr($val["color"], 2, 2));
		$b = hexdec(substr($val["color"], 4, 2));

		if ($r < 153 && $g < 153 || $r < 153 && $b < 153 || $b < 153 && $g < 153) {
			$font = "#ffffff";
		} else {
			$font = "#272727";
		}

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
		echo '<span style="color:'.$font.';text-decoration:none;">' .  $val["title"] ;
		echo "</i></span></a></td></tr>";
		}
?>
<TR>
	<TD></td>
</tr>
</table>

<?php
		echo "</td>";
		$day++;
		$dayRow++;
	}

	while ($dayRow % 7) {
		echo "  <td align=right  bgcolor='#ffffff'>&nbsp;</td>\n";
		$dayRow += 1;
	}
?>
</tr>
<tr>
	<td colspan=8 align=right bgcolor="#efefe7">
		<font face='verdana, arial, helvetica, sans-serif' size='1'>
		<A href="<?php  echo "index.php?thisYear=" . $todaysYear . "&thisMonth=" . $todaysMonth . "&thisDay=" . $todaysDay;?>">today</A>
		</font>
	</td>
</tr>
</TABLE>

</body>
</html>
