<?php
GLOBAL $min_view, $m, $a;
$min_view = isset( $min_view ) ? $min_view : false;

$project_id = isset( $_GET['project_id'] ) ? $_GET['project_id'] : 0;

// sdate and edate passed as unix time stamps
$sdate = isset( $_POST['sdate'] ) ? $_POST['sdate'] : 0;
$edate = isset( $_POST['edate'] ) ? $_POST['edate'] : 0;
// months to scroll
$scroll_date = 1;

$display_option = isset( $_POST['display_option'] ) ? $_POST['display_option'] : 'month';

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
$crumbs["?m=projects&a=view&project_id=$project_id"] = "view this project";
?>
<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.ganttdate.' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250,height=220,scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.ganttdate.' + calendarField );
	fld_fdate = eval( 'document.ganttdate.show_' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

function scrollPrev() {
	f = document.ganttdate;
<?php 
	$new_start = $start_date;
	$new_end = $end_date;
	$new_start->addMonths( -$scroll_date );
	$new_end->addMonths( -$scroll_date );
	echo "f.sdate.value='".$new_start->getTimestamp()."';";
	echo "f.edate.value='".$new_end->getTimestamp()."';";
?>
	f.submit()
}

function scrollNext() {
	f = document.ganttdate;
<?php 
	$new_start = $start_date;
	$new_end = $end_date;
	$new_start->addMonths( $scroll_date );
	$new_end->addMonths( $scroll_date );
	echo "f.sdate.value='".$new_start->getTimestamp()."';";
	echo "f.edate.value='".$new_end->getTimestamp()."';";
?>
	f.submit()
}
</script>

<?php if (!$min_view) { ?>
<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><h1>*</h1></td>
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
<?php } ?>

<table border="0" cellpadding="1" cellspacing="1" width="500" class=std>
<form name="ganttdate" method="post" action="?<?php echo "m=$m&a=$a&project_id=$project_id";?>">
<tr>
	<td nowrap width=100><input type=radio name=display_option value=custom >Date range :</td>
	<td>
		<table border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width=360>
		<tr bgcolor="#eeeeee">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?>:</td>
			<td nowrap>
				<input type="hidden" name="sdate" value="<?php echo $start_date->getTimestamp();?>">
				<input type="text" class="text" name="show_sdate" value="<?php echo $start_date->toString();?>" size="12" disabled="disabled">
				<a href="javascript:popCalendar('sdate')">
					<img src="./images/calendar.gif" width="24" height="12" alt="" border="0">
				</a>
			</td>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'End Date' );?>:</td>
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
	<td><input type="radio" name="display_option" value="month">This Month</td>
	<td>&nbsp;</td>
</tr>

<tr>
	<td><input type="radio" name="display_option" value="all">Entire project</td>
	<td align="right" valign="bottom">
		<input type="submit" class="button" value="<?php echo $AppUI->_( 'submit' );?>">
	</td>
</tr>
</form>
</table>
<br />

<?php
if ($display_option != "all") {
	$scroll_value = "1 month";

?>

<table width="500">
<tr>
	<td align=left>
		<a href="javascript:scrollPrev()">
			<img src="./images/prev.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'previous' );?>" border="0">
		</a>
	</td>
	<td align=right>
		<a href="javascript:scrollNext()">
		<img src="./images/next.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'next' );?>" border="0"></a>
	</td>
</tr>
</table>
<?php 
}

$src = "modules/tasks/gantt.php?project_id=$project_id";
$src .= ($display_option == 'all') ? '' :
	'&start_date='.$start_date->toString( "%Y-%m-%d" ).'&end_date='.$end_date->toString( "%Y-%m-%d" );
$src .= "&width=' + (navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth - 200) + '";

echo "<script>document.write('<img src=\"$src\">')</script>";
?>
