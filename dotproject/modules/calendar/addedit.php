<?
// Add / Edit event
if(empty($event_id))$event_id = 0;

//Pull event information
$csql = "Select * from events where events.event_id = $event_id";
$crc = mysql_query($csql);
$crow = mysql_fetch_array($crc);

//Set the starting date and time
if(is_array($crow)){
	$sdate = strftime("%d/%m/%Y", $crow["$event_start_date"]);
	$stime = strftime("%h:M", $crow["$event_start_date"]);
	$edate = strftime("%d/%m/%Y", $crow["$event_end_date"]);
	$etime = strftime("%h:M", $crow["$event_end_date"]);
}


?>


<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeevent;
	if(form.event_title.value.length < 1){
		alert("Please enter a valid event title");
		form.event_title.focus();
	}
	if(form.sdate.value.length < 1){
		alert("Please enter a start date");
		form.event_start_date.focus();
	}
	if(form.edate.value.length < 1){
		alert("Please enter an end date");
		form.event_end_date.focus();
	}
	if(form.event_recurs.selectedIndex != 0 && isNaN(parseInt(form.event_times_recuring.value))){
		alert("Please select how often you wish this event to recur.");
		form.event_times_recuring.focus();
	}
	
	else
	{
	form.submit();
	}
}


function delIt(){
var form = document.changeevent;
if(confirm("Are you sure you would like\nto delete this event?"))
	{
	form.del.value="<?echo $event_id;?>";
	form.submit();
	}
}


function popCalendar(x){
var form = document.changeevent;

	mm = <?echo strftime("%m", time());?>;
	dd = <?echo strftime("%d", time());?>;
	yy = <?echo strftime("%Y", time());?>;

	dar = eval("document.changeevent." + x + ".value.split('-')");
	if(eval("document.changeevent." + x + ".value.length") > 9){
	if(dar.length == 3)
			{
			yy = parseInt(dar[0], 10);
			mm = parseInt(dar[1], 10);
			dd = parseInt(dar[2], 10);
			}
		}
	
	newwin=window.open('./calendar.php?page=events&form=changeevent&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false');
}


</script>
<TABLE border=0 cellpadding="0" cellspacing=1 width="91%">
	<TR>
		<TD width="44"><img src="./images/icons/calendar.gif" alt="" border="0"></td>
		<TD width="100%"><span class="title">Events</span></td>
		<TD valign="bottom">&nbsp;</td>
	</tr>
	<TR>
		<TD colspan=2 nowrap>This page allows you to view and edit an event</td>
		<TD align="right" nowrap><A href="javascript:delIt()">delete event <img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this event" border="0"></a></td>
	</tr>
</TABLE>

<TABLE border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=0 width="91%">
	
	<form name="changeevent" action="?m=calendar" method="post">
	<input type="hidden" name="dosql" value="addeditdel_event">
	<input type="hidden" name="del" value="0">
	<input type="hidden" name="event_project" value="0">
	<input type="hidden" name="event_id" value="<?echo $event_id;?>">
	<TR bgcolor="#878676" height="20" style="border: outset #eeeeee 2px;">
		<TD valign="top" colspan=2><b><i><?if($event_id == 0){echo "Add";}else{echo "Edit";}?> event </i></b></td>
		<TD align="right" colspan=2>&nbsp;</td>
	</tr>
	<TR>
		<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
		<TD colspan=2></td>
		<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
		</tr>
	<tr>
		<TD valign=top>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="black" width=360>
				<tr bgcolor="#f4efe3"><TD align="right" width="100">Event Name: </td><TD><input type="text" class="text" size=25 name="event_title" value="<?echo @$crow["event_title"];?>" maxlength="255"></td></tr>
			</table>
			<img src="images/shim.gif" width=100 height=10 border=0><br>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
				<tr bgcolor="#eeeeee">
					<TD align="right">Start Date/Time:</td>
					<TD nowrap><input type="text" class="text" name="sdate" value="<?echo @$crow["sdate"];?>" maxlength="10" size=12><a href="#" onClick="popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></TD>
					<TD>
						<select name="stime">
						<option value="0:00">12:00 am
						<option value="0:30">12:30 am
						<option value="1:00">1:00 am
						<option value="1:30">1:30 am
						<option value="2:00">2:00 am
						<option value="2:30">2:30 am
						<option value="3:00">3:00 am
						<option value="3:30">3:30 am
						<option value="4:00">4:00 am
						<option value="4:30">4:30 am
						<option value="5:00">5:00 am
						<option value="5:30">5:30 am
						<option value="6:00">6:00 am
						<option value="6:30">6:30 am
						<option value="7:00">7:00 am
						<option value="7:30">7:30 am
						<option value="8:00">8:00 am
						<option value="8:30">8:30 am
						<option value="9:00">9:00 am
						<option value="9:30">9:30 am
						<option value="10:00">10:00 am
						<option value="10:30">10:30 am
						<option value="11:00">11:00 am
						<option value="11:30">11:30 am
						<option value="12:00">12:00 pm
						<option value="12:30">12:30 pm
						<option value="13:00">1:00 pm
						<option value="13:30">1:30 pm
						<option value="14:00">2:00 pm
						<option value="14:30">2:30 pm
						<option value="15:00">3:00 pm
						<option value="15:30">3:30 pm
						<option value="16:00">4:00 pm
						<option value="16:30">4:30 pm
						<option value="17:00">5:00 pm
						<option value="17:30">5:30 pm
						<option value="18:00">6:00 pm
						<option value="18:30">6:30 pm
						<option value="19:00">7:00 pm
						<option value="19:30">7:30 pm
						<option value="20:00">8:00 pm
						<option value="20:30">8:30 pm
						<option value="21:00">9:00 pm
						<option value="21:30">9:30 pm
						<option value="22:00">10:00 pm
						<option value="22:30">10:30 pm
						<option value="23:00">11:00 pm
						<option value="23:30">11:30 pm
						</select>
					</td>
				</tr>
				<tr bgcolor="#eeeeee">
					<TD align="right">End Date:</td>
					<TD><input type="text"  name="edate" value="<?echo @$crow["edate"];?>" maxlength="10" size=12><a href="#" onClick="popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></TD>
					<TD>
					<select name="stime">
						<option value="0:00">12:00 am
						<option value="0:30">12:30 am
						<option value="1:00">1:00 am
						<option value="1:30">1:30 am
						<option value="2:00">2:00 am
						<option value="2:30">2:30 am
						<option value="3:00">3:00 am
						<option value="3:30">3:30 am
						<option value="4:00">4:00 am
						<option value="4:30">4:30 am
						<option value="5:00">5:00 am
						<option value="5:30">5:30 am
						<option value="6:00">6:00 am
						<option value="6:30">6:30 am
						<option value="7:00">7:00 am
						<option value="7:30">7:30 am
						<option value="8:00">8:00 am
						<option value="8:30">8:30 am
						<option value="9:00">9:00 am
						<option value="9:30">9:30 am
						<option value="10:00">10:00 am
						<option value="10:30">10:30 am
						<option value="11:00">11:00 am
						<option value="11:30">11:30 am
						<option value="12:00">12:00 pm
						<option value="12:30">12:30 pm
						<option value="13:00">1:00 pm
						<option value="13:30">1:30 pm
						<option value="14:00">2:00 pm
						<option value="14:30">2:30 pm
						<option value="15:00">3:00 pm
						<option value="15:30">3:30 pm
						<option value="16:00">4:00 pm
						<option value="16:30">4:30 pm
						<option value="17:00">5:00 pm
						<option value="17:30">5:30 pm
						<option value="18:00">6:00 pm
						<option value="18:30">6:30 pm
						<option value="19:00">7:00 pm
						<option value="19:30">7:30 pm
						<option value="20:00">8:00 pm
						<option value="20:30">8:30 pm
						<option value="21:00">9:00 pm
						<option value="21:30">9:30 pm
						<option value="22:00">10:00 pm
						<option value="22:30">10:30 pm
						<option value="23:00">11:00 pm
						<option value="23:30">11:30 pm
					</select>
				</td>
			</tr>
			<tr bgcolor="#eeeeee">
				<TD align="right">Recurs:</td>
				<TD>
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
					</select> x</TD>
				<TD>
					<input type="text"  name="event_times_recuring" value="<?echo @$crow["event_times_recuring"];?>" maxlength="2" size=3> times
				</td>
			</tr>
		</table>
		<img src="images/shim.gif" width=100 height=10 border=0><br>
		<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
			<tr bgcolor="#eeeeee">
				<TD>Remind me:</TD>
				<TD><Select name="event_remind">
					<option value="900">15 mins.
					<option value="1800">30 mins.
					<option value="3600">1 hour
					<option value="7200">2 hours
					<option value="14400">4 hours
					<option value="28800">8 hours
					<option value="56600">16 hours
					<option value="86400">1 day
					<option value="172800">2 days
				
				
				</SELECT> in advance</TD>
			</tr>
		</TABLE>
	</TD>
	<TD valign="top">
		<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver">
			<tr bgcolor="#eeeeee">
				<TD><b>Description</b><br>
				<textarea class="textarea" name="event_notes"><?echo @$crow["event_title"];?></textarea></TD>
			</TR>
		</TABLE>
	</td>
</TR>
<TR>
<TD><input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=events';"></td>
<TD align="right"><input type="button" value="submit" class=button onClick="submitIt()"></td></tr>
</form>
</TABLE>


