<?php
require "$root_dir/classdefs/date.php";

$project_id = isset( $_GET['sdate'] ) ? $_GET['sdate'] : 0;

// sdate and edate passed as unix time stamps
$sdate = isset( $_POST['sdate'] ) ? $_POST['sdate'] : 0;
$edate = isset( $_POST['edate'] ) ? $_POST['edate'] : 0;
// months to scroll
$scroll_date = 1;

$display_option = isset( $_POST['display_option'] ) ? $_POST['display_option'] : 'month';

//$start_date = ($sdate != "")?toDate($sdate):"";
//$end_date = ($edate != "")?toDate($edate):"";

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

if ($display_option == "custom" ) {
	$start_date = new CDate( $sdate );
	$end_date = new CDate( $edate );
} else {
	$start_date = new CDate( $sdate ? $sdate : null );
	$end_date = $start_date;
	$end_date->addMonths( $scroll_date );
}

$start_date->setFormat( $df );
$end_date->setFormat( $df );

$crumbs = array();
$crumbs["?m=tasks"] = "tasks list";
$crumbs["?m=projects&a=view&project_id=$project_id"] = "this project";
?>
<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.ganttdate.' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.ganttdate.' + calendarField );
	fld_fdate = eval( 'document.ganttdate.show_' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

</script>

<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Gantt Chart</span></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right" width="100%"></td>
</tr>
</table>

<table border="0" cellpadding="1" cellspacing="1" width="98%" class=std>
<form name="ganttdate" method="post" action="?m=tasks&a=viewgantt&project_id=<?php echo $project_id ?>">
<tr>
	<td nowrap width=100><input type=radio name=display_option value=custom >Date range :</td>
	<td>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td align="right" nowrap="nowrap">Start Date:</td>
			<td nowrap>
				<input type="hidden" name="sdate" value="<?php echo $start_date->getTimestamp();?>">
				<input type="text" class="text" name="show_sdate" value="<?php echo $start_date->toString();?>" size="12" disabled="disabled">
				<a href="javascript:popCalendar('sdate')">
					<img src="./images/calendar.gif" width="24" height="12" alt="" border="0">
				</a>
			</td>
			<td align="right" nowrap="nowrap">End Date:</td>
			<td nowrap>
				<input type="hidden" name="edate" value="<?php echo $end_date->getTimestamp();?>">
				<input type="text" class="text" name="show_edate" value="<?php echo $end_date->toString();?>" size="12" disabled="disabled">
				<a href="javascript:popCalendar('edate')">
					<img src="./images/calendar.gif" width="24" height="12" alt="" border="0">
				</a>
			</td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td><input type=radio name=display_option value=month>This month</td>
	<td>&nbsp;</td>
</tr>

<tr>
	<td><input type=radio name=display_option value=all>Entire project</td>
	<td align=right valign=bottom>
		<input type="submit" class="button" value="refresh">
	</td>
</tr>
</form>
</table>
<br>

<?php
if($display_option!="all") {
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
<?php } ?>


<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td align=center>
		<?php
			$src = "modules/tasks/gantt.php?project_id=$project_id";
			$src .= ($display_option == 'all') ? '' :
				'&start_date='.$start_date->toString( "%Y-%m-%d" ).'&end_date='.$end_date->toString( "%Y-%m-%d" );
			$src .= "&width=' + (window.outerWidth - 200) + '";

			echo "<script>document.write('<img src=\"$src\">')</script>";
		?>
	</td>
</tr>
</table>
<br>
<input type="button" value="back" class=button onClick="javascript:window.location='?m=tasks'">

</body>
</html>

