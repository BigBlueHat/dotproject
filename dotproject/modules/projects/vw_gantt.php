<?php
GLOBAL $tab, $project_id;

$start_date = isset( $_POST['sdate'] ) ? toDate( $_POST['sdate'] ) : '';
$end_date = isset( $_POST['edate'] ) ? toDate( $_POST['edate'] ) : '';

$display_option = isset( $_POST['display_option'] ) ? $_POST['display_option'] : '';

//$start_date = ($sdate != "")?toDate($sdate):"";
//$end_date = ($edate != "")?toDate($edate):"";
		
if($display_option=="month" || $start_date == "") {
	$start_date = time2YMD(strtotime("now()"));
	$end_date = time2YMD(strtotime("now() + 1 month"));
}

if($display_option!="all") {
	$scroll_value = "1 month";
}

$crumbs = array();
$crumbs["?m=tasks"] = "tasks list";
$crumbs["?m=projects&a=view&project_id=$project_id"] = "this project";
?>
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

<table border="0" cellpadding="1" cellspacing="1" width="100%" class="std">
<form name="form" method="post" action="?m=projects&a=view&tab=<?php echo $tab;?>&project_id=<?php echo $project_id ?>">
<tr>
	<td nowrap width=100>
		<input type=radio name=display_option value=custom >Date range :
	</td>
	<td>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td align="right"><?php echo $AppUI->_('Start Date');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="sdate" value="<?php echo fromDate($start_date);?>" maxlength="10" size=12><a href="#" onClick="popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>
			</td>
			<td align="right"><?php echo $AppUI->_('End Date');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="edate" value="<?php echo fromDate($end_date);?>" maxlength="10" size=12><a href="#" onClick="popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>
			</td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td><input type=radio name=display_option value=month><?php echo $AppUI->_('This month');?></td>
	<td>&nbsp;</td>
</tr>

<tr>
	<td><input type=radio name=display_option value=all><?php echo $AppUI->_('Entire project');?></td>
	<td align=right valign=bottom>
		<input type="button" value="<?php echo $AppUI->_('refresh');?>" class=button onClick="javascript:document.form.submit();">
	</td>
</tr>
</form>
</table>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td width="16" align="right">
		<a href="javascript:document.form.sdate.value='<?php echo formatTime(strtotime("$start_date -$scroll_value")) ?>';document.form.edate.value='<?php echo fromDate($start_date) ?>';document.form.submit()">
			<img src="./images/prev.gif" width="16" height="16" alt="pre" border="0">
		</a>
	</td>
	<td width="1" align="center">
		<?php					
			echo "<script>document.write('<img src=./modules/tasks/gantt.php?project_id=$project_id" . ($display_option=="all"?"":"&start_date=$start_date&end_date=$end_date") . "&width=' + (window.outerWidth - 200) + '>')</script>";
		?>
	</td>
	<td width="16" align="left">
		<a href="javascript:document.form.sdate.value='<?php echo fromDate($end_date) ?>';document.form.edate.value='<?php echo formatTime(strtotime("$end_date +$scroll_value")) ?>';document.form.submit()">
		<img src="./images/next.gif" width="16" height="16" alt="next" border="0"></a>
	</td>
</tr>
</table>
