<?php

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// Add / Edit event
if(empty( $event_id )) {
	$event_id = 0;
}

//Pull event information
$csql = "Select * from events where events.event_id = $event_id";
$crc = mysql_query( $csql );
$crow = mysql_fetch_array( $crc );

//Set the starting date and time
if(is_array($crow)){
	$sdate = strftime( "%Y-%m-%d", $crow["event_start_date"] );
	$stime = strftime( "%H:%M", $crow["event_start_date"] );
	$shour = strftime( "%H", $crow["event_start_date"] );
	$smin = strftime( "%M", $crow["event_start_date"] );
	$edate = strftime( "%Y-%m-%d", $crow["event_end_date"] );
	$etime = strftime( "%H:%M", $crow["event_end_date"] );
	$ehour = strftime( "%H", $crow["event_end_date"] );
	$emin = strftime( "%M", $crow["event_end_date"] );

	$sdate = fromDate( $sdate );
	$edate = fromDate( $edate );
}
?>


<script language="javascript">
function submitIt(){
	var form = document.changeevent;
	if (form.event_title.value.length < 1) {
		alert("Please enter a valid event title");
		form.event_title.focus();
	}
	if (form.sdate.value.length < 1){
		alert("Please enter a start date");
		form.event_start_date.focus();
	}
	if (form.edate.value.length < 1){
		alert("Please enter an end date");
		form.event_end_date.focus();
	}
	if (form.event_recurs.selectedIndex != 0 && isNaN(parseInt(form.event_times_recuring.value))){
		alert("Please select how often you wish this event to recur.");
		form.event_times_recuring.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.changeevent;
	if (confirm("Are you sure you would like\nto delete this event?")) {
		form.del.value="<?php echo $event_id;?>";
		form.submit();
	}
}

function popCalendar(x){
	var form = document.changeevent;

	mm = <?php echo strftime("%m", time());?>;
	dd = <?php echo strftime("%d", time());?>;
	yy = <?php echo strftime("%Y", time());?>;

	dar = eval( "document.changeevent." + x + ".value.split('-')" );
	if (eval( "document.changeevent." + x + ".value.length" ) > 9){
	if (dar.length == 3) {
		yy = parseInt(dar[0], 10);
		mm = parseInt(dar[1], 10);
		dd = parseInt(dar[2], 10);
		}
	}
	
	newwin = window.open('./calendar.php?page=events&form=changeevent&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false');
}
</script>
<table border=0 cellpadding="0" cellspacing="0" width="98%">
	<tr>
		<td width="44"><img src="./images/icons/calendar.gif" alt="" border="0"></td>
		<td width="100%"><span class="title">Events</span></td>
		<td valign="bottom">&nbsp;</td>
	</tr>
	<tr>
		<td colspan=2 nowrap>This page allows you to view and edit an event</td>
		<td align="right" nowrap><A href="javascript:delIt()">delete event <img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this event" border="0"></a></td>
	</tr>
</table>

<table cellspacing="1" cellpadding="2" border="0" width="98%" class="std">
<form name="changeevent" action="?m=calendar" method="post">
<input type="hidden" name="dosql" value="addeditdel_event">
<input type="hidden" name="del" value="0">
<input type="hidden" name="event_project" value="0">
<input type="hidden" name="event_id" value="<?php echo $event_id;?>">

<tr>
	<th colspan="3"><?php if($event_id == 0){echo "Add";}else{echo "Edit";}?> event</th>
</tr>
<tr>
	<td valign="top">
		<table cellspacing="1" cellpadding="1" border="0" width="360">
		<tr>
			<td align="right" nowrap>Event Name:</td>
			<td>
				<input type="text" class="text" size="25" name="event_title" value="<?php echo @$crow["event_title"];?>" maxlength="255">
			</td>
		</tr>
		<tr>
			<td align="right" nowrap>Make private:</td>
			<td>
				<input type="checkbox" value="1" name="event_private" <?php echo (@$crow["event_private"] ? 'checked' : '');?>>
			</td>
		</tr>
		</table>
		<img src="images/shim.gif" width=100 height=10 border=0><br>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td align="right">Start Date/Time:</td>
			<td nowrap><input type="text" class="text" name="sdate" value="<?php echo @$sdate;?>" maxlength="10" size=12><a href="#" onClick="popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></td>
			<td>
				<select name="stime">
<?php
	$hr=0;
	$mn="00";
	while ($hr < 24) {
		echo "<option value='$hr:$mn'";
		if ($hr == $shour && $mn == $smin)
			echo " selected";
		echo ">";
		if ($hr == 0)
			echo "12:$mn";
		else
			echo "$hr:$mn";
		if ($hr < 12)
			echo " am";
		else
		  echo " pm";
		echo "</option>\n";
		if ($mn == "00") {
			$mn = "30";
		} else {
			$mn = "00";
			$hr++;
		}
	}
?>
				</select>
			</td>
		</tr>
		<tr bgcolor="#eeeeee">
			<td align="right">End Date:</td>
			<td nowrap><input type="text" class="text" name="edate" value="<?php echo @$edate;?>" maxlength="10" size=12><a href="#" onClick="popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></td>
			<td>
				<select name="etime">
<?php
	$hr=0;
	$mn="00";
	while ($hr < 24) {
		echo "<option value='$hr:$mn'";
		if ($hr == $ehour && $mn == $emin)
			echo " selected";
		echo ">";
		if ($hr == 0)
			echo "12:$mn";
		else
			echo "$hr:$mn";
		if ($hr < 12)
			echo " am";
		else
		  echo " pm";
		echo "</option>\n";
		if ($mn == "00") {
			$mn = "30";
		} else {
			$mn = "00";
			$hr++;
		}
	}
?>
				</select>
			</td>
		</tr>
		<tr bgcolor="#eeeeee">
			<td align="right">Recurs:</td>
			<td>
				<select name="event_recurs">
					<option>Never
					<option>Hourly
					<option>Daily
					<option>Weekly
					<option>Bi-Weekly
					<option>Every Month
					<option>Quarterly
					<option>Every 6 months
					<option>Every Year
				</select> x</td>
			<td>
				<input type="text"  name="event_times_recuring" value="<?php echo @$crow["event_times_recuring"];?>" maxlength="2" size=3> times
			</td>
		</tr>
		</table>
		<img src="images/shim.gif" width=100 height=10 border=0><br>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td>Remind me:</td>
			<td><Select name="event_remind">
				<option value="900">15 mins.
				<option value="1800">30 mins.
				<option value="3600">1 hour
				<option value="7200">2 hours
				<option value="14400">4 hours
				<option value="28800">8 hours
				<option value="56600">16 hours
				<option value="86400">1 day
				<option value="172800">2 days
			
			
			</SELECT> in advance</td>
		</tr>
		</table>
	</td>
	<td valign="top">
		<b>Description</b><br>
		<textarea class="textarea" name="event_notes"><?php echo @$crow["event_description"];?></textarea></td>
	</td>
</tr>
<tr>
<td><input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=events';"></td>
<td align="right"><input type="button" value="submit" class=button onClick="submitIt()"></td></tr>
</form>
</table>
