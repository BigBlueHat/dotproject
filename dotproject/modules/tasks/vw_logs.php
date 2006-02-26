<?php /* TASKS $Id$ */
global $AppUI, $task_id, $df, $canEdit, $m, $tpl;

$perms =& $AppUI->acl();
if (! $perms->checkModuleItem('task_log', 'view', $task_id)) {
	$AppUI->redirect("m=public&a=access_denied");
}

$problem = intval( dPgetParam( $_GET, 'problem', null ) );
// get sysvals
$taskLogReference = dPgetSysVal( 'TaskLogReference' );
$taskLogReferenceImage = dPgetSysVal( 'TaskLogReferenceImage' );
?>
<script language="JavaScript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
$canDelete = $perms->checkModuleItem('task_log', 'delete', $task_id);
if ($canDelete) {
?>
function delIt2(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Task Log', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>

<?php
// Pull the task comments
$q = new DBQuery;
$q->addQuery('task_log.*');
$q->addQuery('billingcode_name as task_log_costcode');
$q->addQuery('user_username');
$q->addTable('task_log');
$q->addJoin('billingcode', 'b', 'task_log_costcode = billingcode_id');
$q->addJoin('users', 'u', 'user_id = task_log_creator');
$q->addOrder('task_log_date');
$q->addWhere("task_log_task = $task_id");
if ($problem) {
	$q->addWhere("task_log_problem > '0'");
}
$logs = $q->loadList();
$q->clear();

foreach($logs as $k => $row)
{
	$reference_image = "-";
	if($row["task_log_reference"] > 0){
			if(isset($taskLogReferenceImage[$row["task_log_reference"]])){
					$reference_image = dPshowImage( $taskLogReferenceImage[$row["task_log_reference"]], 16, 16, $taskLogReference[$row["task_log_reference"]], $taskLogReference[$row["task_log_reference"]] );
			} else if (isset($taskLogReference[$row["task_log_reference"]])){
					$reference_image = $taskLogReference[$row["task_log_reference"]];
			}
	}
	
	$row['reference_image'] = $reference_image;
	$row['canEdit'] = $perms->checkModuleItem('task_log', 'edit', $task_id);
	$hours = $row['task_log_hours'];
	$row['task_log_hours_display'] = floor($hours) .':'. sprintf('%02.0f', ($hours - floor($hours)) * 60);
	$logs[$k] = $row;
	
	$hrs += (float)$row["task_log_hours"];
}

$tpl->assign('tab', (($tab == -1)?$AppUI->getState('TaskLogVwTab'):1));
$tpl->assign('canDelete', $canDelete);
$tpl->assign('task_id', $task_id);
$tpl->assign('rows', $logs);
$tpl->assign('total_hours', $hrs);
$tpl->assign('total_hours_display', floor($hrs) .':'. sprintf('%02.0f', ($hrs - floor($hrs)) * 60));
$tpl->displayFile('tasklog');
?>