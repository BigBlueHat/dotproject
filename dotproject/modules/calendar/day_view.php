<?
//define up event array
$earr = array();


// Set Day, Month, Year
if(empty($thisMonth))$thisMonth = date("n", time());
if(empty($thisYear))$thisYear = date("Y", time());
if(empty($thisDay))$thisDay = date("d", time());

if($thisDay < 1)$thisDay = 1;

if(empty($todaysDay))$todaysDay = date("d", time());
if(empty($todaysMonth))$todaysMonth = intval(date("m", time()));
if(empty($todaysYear))$todaysYear = date("Y", time());
if(empty($field))$field = "x";
$day=0;

// Figure out the last day of the month
$lastday[1]=31;
// Check for Leap Years
if( checkdate( $thisMonth, 29, $thisYear ) ) { $lastday[2] = 29; }else { $lastday[2]=28; }
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
$dayNamesShort[0] = "Sun";
$dayNamesShort[1] = "Mon";
$dayNamesShort[2] = "Tue";
$dayNamesShort[3] = "Wed";
$dayNamesShort[4] = "Thu";
$dayNamesShort[5] = "Fri";
$dayNamesShort[6] = "Sat";



if($thisDay > $lastday["$thisMonth"]){$thisDay = $lastday["$thisMonth"];}

$sqldate = $thisYear . "-" . $thisMonth . "-" . $thisDay;

$prevYear = $thisYear;
$prevMonth = $thisMonth - 1;
if( $prevMonth < 1 ){ 
$prevMonth = $prevMonth + 12; $prevYear--; 
}
if($lastday["$prevMonth"] > $thisDay){
$moveday = $thisDay;
}
else{
$moveday =$lastday["$prevMonth"];
}

$nextYear = $thisYear;
$nextMonth = $thisMonth+1;
if( $nextMonth > 12 ) { $nextMonth = $nextMonth - 12; $nextYear++; }



//Get events for today
$thismorn = mktime(0,0,0,$thisMonth, $thisDay, $thisYear);
$thiseve = $thismorn + 86399;
$sql = "Select event_title, event_id, event_start_date, event_end_date 
		from events 
		where 
		(event_start_date < $thismorn and event_end_date > $thiseve ) or
		(event_start_date >= $thismorn and event_start_date < $thiseve) or
		(event_end_date >= $thismorn and event_end_date < $thiseve) 
		order by event_start_date group by event_id";
$rc = mysql_query($sql);
if ( $rc != false) {
while($row = mysql_fetch_array($rc)){
$earr[] = $row;

}

}


?>
<html>
<head>
<SCRIPT language="javascript">
function setClose(x,y,z){
z =  z - 1999;
if("<?echo $field;?>" != "Actual"){
	x = x-1;
	y = y-1;
}


var form = window.opener.document.AddEdit;
	form.<?echo $field;?>MM_int.selectedIndex = x;
	form.<?echo $field;?>DD_int.selectedIndex = y;
	form.<?echo $field;?>YYYY_int.selectedIndex = z;
	window.close();
}




</script>


<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/calendar.gif" alt="<?echo ptranslate("Calendar");?>" border="0" width="42" height="42"></td>
		<TD nowrap><span class="title">Day View</span></td>
		<TD align="right" width="100%">&nbsp;</td>
	</tr>
</TABLE>

<table border=0 cellspacing=1 cellpadding=2 width="95%" class=bordertable>
	<tr>
		<td align=center>
			<a href="<? echo("./index.php?m=calendar&a=day_view&thisYear=" . $prevYear . "&thisMonth=" . $prevMonth . "&thisDay=" . $moveday ."&field=" . $field);?>"><img src="./images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
		</td>
		<td width="100%">
			<b><a href="<? echo("./index.php?m=calendar&thisYear=" . $thisYear . "&thisMonth=" . $thisMonth);?>"><?php echo strftime("%B", mktime(0,0,0,$thisMonth,1,$thisYear));?> <?php echo $thisYear?></a></b>
		</td>
		<td align=center>
			<? echo "<a href='./index.php?m=calendar&a=day_view&thisYear=" . $nextYear . "&thisMonth=" . $nextMonth . "&thisDay=" . $moveday ."&field=" . $field ."'>";?><img src="./images/next.gif" width="16" height="16" alt="next" border="0"></A>
		</td>
	</tr>
</table>
<table width="95%">



</table>

</body>
</html>

