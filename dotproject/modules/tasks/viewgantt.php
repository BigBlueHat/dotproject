<?php
GLOBAL $min_view, $m, $a;
$min_view = defVal( @$min_view, false);

$project_id = defVal( @$_GET['project_id'], 0);

// sdate and edate passed as unix time stamps
$sdate = defVal( @$_POST['sdate'], 0);
$edate = defVal( @$_POST['edate'], 0);

// months to scroll
$scroll_date = 1;

$display_option = defVal( @$_POST['display_option'], 'this_month');

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
$crumbs["javascript:showThisMonth()"] = "show this month";
$crumbs["javascript:showFullProject()"] = "show full project";

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
	echo "f.sdate.value='" . $new_start->getTimestamp() . "';";
	echo "f.edate.value='" . $new_end->getTimestamp() . "';";
?>
	f.submit()
}
	
function showThisMonth() {
	document.ganttdate.display_option.value = "this_month";
	document.ganttdate.submit();
}
	
function showFullProject() {
	document.ganttdate.display_option.value = "all";
	document.ganttdate.submit();
}	
	
</script>

<?php
if (!$min_view) {
	// Normal view (not inserted in tabbox)
?>
<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><h1><?php echo $AppUI->_( 'Gantt Chart' );?></h1></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr>
	<td nowrap valign=top><?php echo breadCrumbs( $crumbs );?></td>
<?php
} else {
	// Minimal view (inserted in tabbox)
?>
	<table width=100%>
	<tr>
<?php 	
}
?>

<td align=right>	
  <form name="ganttdate" method="post" action="?<?php echo "m=$m&a=$a&project_id=$project_id";?>">
  <input type=hidden name=display_option>
  <table border="0" cellpadding="1" cellspacing="1">
  <tr>
  <td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'From' );?>:</td>
  <td align=left nowrap>
  <input type="hidden" name="sdate" value="<?php echo $start_date->getTimestamp();?>">
  <input type="text" class="text" name="show_sdate" value="<?php echo $start_date->toString();?>" size="12" disabled="disabled">
  <a href="javascript:popCalendar('sdate')">
  <img src="./images/calendar.gif" width="24" height="12" alt="" border="0">
  </a>
  </td>
  </tr>
  
  <tr>
  <td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'To' );?>:</td>
  <td align=left nowrap>
  <input type="hidden" name="edate" value="<?php echo $end_date->getTimestamp();?>">
  <input type="text" class="text" name="show_edate" value="<?php echo $end_date->toString();?>" size="12" disabled="disabled">
  <a href="javascript:popCalendar('edate')">
  <img src="./images/calendar.gif" width="24" height="12" alt="" border="0">
  </a>
  </td>
  </tr>
  
  <tr>
  <td>
  </td>
  <td align=left>
  <input type="submit" class="button" value="<?php echo $AppUI->_( 'submit' );?>">
  </td>
</tr>
	
</form>
</table>
	
</tr>
</table>	
<br>

<table align=center><tr>	

<?php if ($display_option != "all") {
?>
	<td align=left valign=top>
		<a href="javascript:scrollPrev()">
			<img src="./images/prev.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'previous' );?>" border="0">	  
		</a>
	</td>   
<?php
}
?>

	<td>
<?php	
$src = 
  "?m=tasks&a=gantt&no_output=1&project_id=$project_id" .
  ( $display_option == 'all' ? '' : 
	'&start_date=' . $start_date->toString( "%Y-%m-%d" ) . '&end_date=' . $end_date->toString( "%Y-%m-%d" ) ) .
  "&width=' + (navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth - 200) + '";

echo "<script>document.write('<img src=\"$src\">')</script>";	
?>	
	</td>

<?php if ($display_option != "all") {
?>
	<td align=right valign=top>
	  <a href="javascript:scrollNext()">
	  	<img src="./images/next.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'next' );?>" border="0">
	  </a>
	</td>
</tr>
<?php
}
?>	  
</table>

