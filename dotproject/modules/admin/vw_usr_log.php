<script type="text/javascript" language="JavaScript">
<!--
var calendarField = '';
var calWin = null;

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.frmDate.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=251, height=220, scrollbars=no' );
}

function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.frmDate.log_' + calendarField );
	fld_fdate = eval( 'document.frmDate.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function checkDate(){
           if (document.frmDate.log_start_date.value == "" || document.frmDate.log_end_date.value== ""){
                alert("<?php echo $AppUI->_('You must fill fields', UI_OUTPUT_JS) ?>");
                return false;
           } 
           return true;
}
-->
</script>

<?php
$date_reg = date("Y-m-d");
$start_date = intval( $date_reg) ? new CDate( dPgetParam($_REQUEST, "log_start_date", date("Y-m-d") ) ) : null;
$end_date = intval( $date_reg) ? new CDate( dPgetParam($_REQUEST, "log_end_date", date("Y-m-d") ) ) : null;

//$df = $AppUI->getPref('SHDATEFORMAT');
global $currentTabId, $tpl;
if ($a = dPgetParam($_REQUEST, "a", "") == ""){
    $a = "&amp;tab={$currentTabId}&amp;showdetails=1";
} else {
    $user_id = dPgetParam($_REQUEST, "user_id", 0);
    $a = "&amp;a=viewuser&amp;user_id={$user_id}&amp;tab={$currentTabId}&amp;showdetails=1";
}

if (dPgetParam($_REQUEST, "showdetails", 0) == 1 ) 
{
  $start_date = date("Y-m-d", strtotime(dPgetParam($_REQUEST, "log_start_date", date("Y-m-d") )));
  $end_date   = date("Y-m-d 23:59:59", strtotime(dPgetParam($_REQUEST, "log_end_date", date("Y-m-d") )));
      
	$q  = new DBQuery;
	$q->addTable('user_access_log', 'ual');
	$q->addTable('users', 'u');
	$q->addTable('contacts', 'c');
	$q->addQuery('ual.*, u.*, c.*');
	$q->addWhere('ual.user_id = u.user_id');
	$q->addWhere('user_contact = contact_id ');
	if($user_id != 0) { $q->addWhere("ual.user_id='$user_id'"); }
	$q->addWhere("ual.date_time_in >='$start_date'");
	$q->addWhere("ual.date_time_out <='$end_date'");
	$q->addGroup('ual.date_time_last_action DESC');
	$logs = $q->loadList();
	
  $tpl->assign('start_date', $start_date);
  $tpl->assign('end_date', $end_date);
	$tpl->assign('logs', $logs);
}

$tpl->assign('get', $a);
$tpl->displayFile('usr_log');
?>
