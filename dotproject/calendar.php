<?php
//test
// Set Day, Month, Year
if(empty($thisMonth)){$thisMonth = date("n", time());}
if(empty($thisYear)){$thisYear = date("Y", time());}
if(empty($thisDay)){$thisDay = date("d", time());}
if($thisDay < 1){$thisDay = 1;}
if(empty($todaysDay)){$todaysDay = date("d", time());}
if(empty($todaysMonth)){$todaysMonth = intval(date("m", time()));} 
if(empty($todaysYear)){$todaysYear = date("Y", time());} 
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

//Short Month names
$dayNamesShort[0] = "Sun";
$dayNamesShort[1] = "Mon";
$dayNamesShort[2] = "Tue";
$dayNamesShort[3] = "Wed";
$dayNamesShort[4] = "Thu";
$dayNamesShort[5] = "Fri";
$dayNamesShort[6] = "Sat";

if($thisDay > $lastday[$thisMonth]){$thisDay = $lastday[$thisMonth];}

$sqldate = $thisYear . "-" . $thisMonth . "-" . $thisDay;

$prevYear = $thisYear;
$prevMonth = $thisMonth - 1;
if( $prevMonth < 1 )
{ 
$prevMonth = $prevMonth + 12; $prevYear--; 
}
if($lastday[$prevMonth] > $thisDay)
{
$moveday = $thisDay;
}
else
{
$moveday =$lastday[$prevMonth];
}

$nextYear = $thisYear;
$nextMonth = $thisMonth+1;
if( $nextMonth > 12 ) { $nextMonth = $nextMonth - 12; $nextYear++; }
?>
<html>
<head>
<SCRIPT language="javascript">
function setClose(x,y,z){
	var form = window.opener.document.<?=$form;?>;

	if("<?=$page;?>"=="events"){
		//form.<?echo $field;?>.value = x + "/" + y + "/" + z;
		form.<?echo $field;?>.value = z + "-" + x + "-" + y;
	
	}
	else if("<?echo $field;?>".indexOf("task") >-1){
		form.<?echo $field;?>.value = z + "-" + x + "-" + y;
	
	}
	else{
		z =  z - 1999;
		if("<?echo $field;?>" != "Actual"){
			x = x-1;
			y = y-1;
		}
	
		form.<?echo $field;?>MM_int.selectedIndex = x;
		form.<?echo $field;?>DD_int.selectedIndex = y;
		form.<?echo $field;?>YYYY_int.selectedIndex = z;
		
	}
	window.close();
}




</script>




<title>Calendar</title>
</head>
<body bgcolor="White" onload="this.focus();">
<table border=0 cellspacing=1 cellpadding=2 width="220">
	<tr>
		<td align=center>
			<a href="<? echo($SCRIPT_NAME . "?form=" . $form . "&page=" . $page . "&thisYear=" . $prevYear . "&thisMonth=" . $prevMonth . "&thisDay=" . $moveday ."&field=" . $field);?>"><img src="./images/prev.gif" width="16" height="16" alt="pre" border="0"></A>
		</td>
		<td colspan=5 align=center bgcolor="#000000">
			<font face="arial, helvetica" size=2 color="#ffffff"><b>
			<?php echo strftime("%B", mktime(0,0,0,$thisMonth,1,$thisYear));?> <?php echo $thisYear?></b></font>
		</td>
		<td align=center>
			<? echo "<a href='" . $SCRIPT_NAME . "?form=" . $form . "&page=" . $page . "&thisYear=" . $nextYear . "&thisMonth=" . $nextMonth . "&thisDay=" . $moveday ."&field=" . $field ."'>";?><img src="./images/next.gif" width="16" height="16" alt="next" border="0"></A>
		</td>
	</tr>
	<tr>
	<?  // print days across top
	for( $i = 0; $i <= 6; $i++ ) 
	{
  	echo "<td bgcolor=#ffffff align=center>";
   	echo "<font face='arial, helvetica, sans-serif' size='2'>";
   	echo "<b>" . $dayNamesShort[$i] . "</b>\n";
   	echo "</font>\n";
  	echo "</td>\n";
 	}?>
	</tr>
<? 
$firstDay = date( 'w', mktime( 0, 0, 0, $thisMonth, 1, $thisYear ) );
$dayRow = 0;
if( $firstDay > 0 ) 
	{
  	while( $dayRow < $firstDay ) 
		{
   		echo "  <td align=right bgcolor='#efefe7'>&nbsp;</td>\n";
   		$dayRow += 1;  
  		}
 	}

 	while( $day < $lastday[$thisMonth] ) 
		{
  		if( ( $dayRow % 7 ) == 0) 
			{
   			echo " </tr>\n<tr>\n";
  			}

  		$dayp = $day + 1;

   		$datestr = $thisYear ."-" .  $thisMonth ."-" .  $dayp;


   		if( $dayp == $thisDay && empty($drill) ) 
			{ 
			$bgcolor = "silver"; 
			$txtcolor = "black"; 
			}
   		else 
			{ 
			$bgcolor = "#efefe7"; 
			$txtcolor = 'black'; 
			}
?>
  <td align=center bgcolor="<?php echo $bgcolor?>">
   <font face='arial, helvetica, sans-serif' size='1'>
    <?php 
	if( $dayp == $todaysDay && $thisMonth == $todaysMonth && $thisYear == $todaysYear ) { echo "<b>"; }
   	echo "<a href='#' onClick='setClose(".$thisMonth.",".$dayp.",".$thisYear.")'>
		<font color=" . $txtcolor . ">" . $dayp . "</font>";
   	if( $dayp == $todaysDay && $thisMonth == $todaysMonth && $thisYear == $todaysYear ) { echo "</b>"; }
    ?>
   </font>
  </td>
<?php 
  	$day++;
  	$dayRow++;
 	}

 while( $dayRow % 7 ) 
	{
   	echo "  <td align=right bgcolor=\"" . $bgcolor . "\">&nbsp;</td>\n";
   	$dayRow += 1;  
  	}
?>
 </tr>
 <tr>
  <td colspan=7 align=right bgcolor="#efefe7">
    <font face='Tahoma, arial, helvetica, sans-serif' size='1'>
   <A href="<? echo $SCRIPT_NAME . "?form=" . $form . "&page=" . $page . "&thisYear=" . $todaysYear . "&thisMonth=" . $todaysMonth . "&thisDay=" . $todaysDay . "&field=" . $field;?>">today</A>
   </font>
  </td>
 </tr>
</TABLE>


</body>
</html>

