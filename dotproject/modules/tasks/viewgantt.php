<script language="javascript">
function popCalendar(x){
	var form = document.changeevent;

	mm = <?php echo strftime("%m", time());?>;
	dd = <?php echo strftime("%d", time());?>;
	yy = <?php echo strftime("%Y", time());?>;

	dar = eval( "document.form." + x + ".value.split('-')" );
	if (eval( "document.form." + x + ".value.length" ) > 9){
	if (dar.length == 3) {
		yy = parseInt(dar[0], 10);
		mm = parseInt(dar[1], 10);
		dd = parseInt(dar[2], 10);
		}
	}
	
	newwin = window.open('./calendar.php?page=events&form=form&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false');
}
</script>

<?php
	$start_date = ($sdate != "")?toDate($sdate):"";
	$end_date = ($edate != "")?toDate($edate):"";
			
	// TODO: Add radio buttons for displaying "entire project", "this month", ...
	if($start_date == "") {
		$start_date = date("Y-m-d", strtotime("now()"));
		$end_date = date("Y-m-d", strtotime("now() + 1 month"));
	}
?>

<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Gantt Chart</span></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<form name="form" method="post" action="?m=tasks&a=viewgantt&project_id=<?php echo $project_id ?>">
<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td align="right">Start Date:</td>
			<td nowrap><input type="text" class="text" name="sdate" value="<?php echo fromDate($start_date);?>" maxlength="10" size=12><a href="#" onClick="popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></td>
		</tr>
		<tr bgcolor="#eeeeee">
			<td align="right">End Date:</td>
			<td nowrap><input type="text" class="text" name="edate" value="<?php echo fromDate($end_date);?>" maxlength="10" size=12><a href="#" onClick="popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a></td>
		</tr>
		</table>
	</td>
	<td align=left valign=bottom>
	<input type="button" value="refresh" class=button onClick="javascript:document.form.submit();">
	</td>
	</tr>
</table>
<br>

<?php
	$scroll_value = "1 month";
?>

<table width="98%">
<tr>
        <td align=left>
                <a href="javascript:document.form.sdate.value='<?php echo formatTime(strtotime("$start_date -$scroll_value")) ?>';document.form.edate.value='<?php echo fromDate($start_date) ?>';document.form.submit()">
                	<img src="./images/prev.gif" width="16" height="16" alt="pre" border="0">
                </a>
        </td>
        <td align=right>
                <a href="javascript:document.form.sdate.value='<?php echo fromDate($end_date) ?>';document.form.edate.value='<?php echo formatTime(strtotime("$end_date +$scroll_value")) ?>';document.form.submit()">
                <img src="./images/next.gif" width="16" height="16" alt="next" border="0"></a>
        </td>
</tr>
</table>

</form>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td align=center>		
		<?php					
			echo "<script>document.write('<img src=modules/tasks/gantt.php?project_id=$project_id&start_date=$start_date&end_date=$end_date&width=' + (window.outerWidth - 200) + '>')</script>";
		?>
	</td>
</tr>
</table>
<br>
<input type="button" value="back" class=button onClick="javascript:window.location='?m=tasks'">

</body>
</html>

