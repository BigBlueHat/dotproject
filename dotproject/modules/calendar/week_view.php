<?php

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
?>
<html>
<head>
<SCRIPT language="javascript">
function setClose(x,y,z){
z =  z - 1999;
if("<?php echo $field;?>" != "Actual"){
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
	<TR>
	<TD><img src="./images/icons/calendar.gif" alt="<?php echo ptranslate("Calendar");?>" border="0" width="42" height="42"></td>
		<TD nowrap><span class="title">Week View</span></td>
		<TD align="right" width="100%">&nbsp;</td>
	</tr>
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
	<?php   // print days across top
	for( $i = 0; $i <= 6; $i++ ) 
	{?>
  	<td align=center width="14%"><b><?php echo $dayNamesShort["$i"];?></b></td>
 	<?php }?>
	</tr>

<?php  
$firstDay = date( 'w', mktime( 0, 0, 0, $thisMonth, 1, $thisYear ) );
$dayRow = 0;
if( $firstDay > 0 ) 
	{
  	while( $dayRow < $firstDay ) 
		{
   		echo "<td align=right bgcolor='#ffffff' height='80'>&nbsp;</td>\n";
   		$dayRow += 1;  
  		}
 	}

 	while( $day < $lastday["$thisMonth"] ) 
		{
  		if( ( $dayRow % 7 ) == 0) 
			{
   			echo " </tr>\n<tr height=80>\n";
  			}

  		$dayp = $day + 1;

   		$datestr = $thisYear ."-" .  $thisMonth ."-" .  $dayp;


   		if( $dayp == $thisDay && empty($drill) ) 
			{ 
			$bgcolor = "efefe7"; 
			$txtcolor = "black"; 
			}
   		else 
			{ 
			$bgcolor = "#efefe7"; 
			$txtcolor = 'black'; 
			}
		$items = eventsForDate($dayp, $thisMonth,$thisYear);
		echo "<td valign=top bgcolor=$bgcolor>" . $dayp;
		?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<?php 

		while (list ($key, $val) = each ($items))
		{
					$r = hexdec(substr($val["color"], 0, 2)); 
					$g = hexdec(substr($val["color"], 2, 2)); 
					$b = hexdec(substr($val["color"], 4, 2)); 
					
					if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128) 
					{
					$f = "<span style=\"color:white; text-decoration:none;\">";
					}
					else
					{
					$f = "<span style=\"color:#272727; text-decoration:none;\">";
					};
		
		

		echo "<TR><TD bgcolor=" . $val["color"] .">";
		if($val["type"]=="p")
		{
		echo "<a href=./index.php?m=projects&a=view&project_id=" . $val["id"] ."><B>";
		}
		elseif($val["type"]=="t")
		{
			if(intval($val["priority"] <> 0))echo "<img src=\"./images/icons/" . $val["priority"] .".gif\" border=0 width=13 height=16 align=absmiddle>";
			echo "<a href=./index.php?m=tasks&a=view&task_id=" . $val["id"] .">";
			
		}
		
		echo $f .  $val["title"] ;
		echo "</a></td></tr>";
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

 while( $dayRow % 7 ) 
	{
   	echo "  <td align=right  bgcolor='#ffffff'>&nbsp;</td>\n";
   	$dayRow += 1;  
  	}
?>
 </tr>
 <tr>
  <td colspan=7 align=right bgcolor="#efefe7">
    <font face='Tahoma, arial, helvetica, sans-serif' size='1'>
   <A href="<?php  echo "index.php?thisYear=" . $todaysYear . "&thisMonth=" . $todaysMonth . "&thisDay=" . $todaysDay;?>">today</A>
   </font>
  </td>
 </tr>
</TABLE>


</body>
</html>

