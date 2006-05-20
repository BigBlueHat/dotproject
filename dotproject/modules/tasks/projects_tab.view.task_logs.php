<?php /* TASKS $Id$ */
	global $AppUI, $project_id, $df, $canEdit, $m, $tab;

	// Lets check which cost codes have been used before
  $q  = new DBQuery;
  $q->addQuery('project_company');
  $q->addTable('projects');
  $q->addWhere('project_id = ' . $project_id);
  $company_id = $q->loadResult();

  $q->addTable('billingcode');
  $q->addQuery('billingcode_id, billingcode_name');
  $q->addOrder('billingcode_name');
  $q->addWhere('(company_id = 0 OR company_id = ' . $company_id . ')');
  $task_log_costcodes = $q->loadHashList();
	$task_log_costcodes[0] = '&nbsp;';
	ksort($task_log_costcodes);
	
	$q->addTable('users');
	$q->addQuery("user_id, concat(contact_first_name,' ',contact_last_name)");
	$q->addJoin('contacts', 'con', 'user_contact = contact_id');
	$q->addOrder('contact_first_name, contact_last_name');
	$users = arrayMerge( array( '-1' => $AppUI->_('All Users') ), $q->loadHashList() );

	$cost_code = dPgetParam( $_GET, 'cost_code', '0' );
	
	if (isset( $_GET['user_id'] )) {
		$AppUI->setState( 'ProjectsTaskLogsUserFilter', $_GET['user_id'] );
	}
	$user_id = $AppUI->getState( 'ProjectsTaskLogsUserFilter' ) ? $AppUI->getState( 'ProjectsTaskLogsUserFilter' ) : $AppUI->user_id;

	if (isset( $_GET['hide_inactive'] )) {
		$AppUI->setState( 'ProjectsTaskLogsHideArchived', true );
	} else {
		$AppUI->setState( 'ProjectsTaskLogsHideArchived', false );
	}
	$hide_inactive = $AppUI->getState( 'ProjectsTaskLogsHideArchived' );

	if (isset( $_GET['hide_complete'] )) {
		$AppUI->setState( 'ProjectsTaskLogsHideComplete', true );
	} else {
		$AppUI->setState( 'ProjectsTaskLogsHideComplete', false );
	}
	$hide_complete = $AppUI->getState( 'ProjectsTaskLogsHideComplete' );

$perms =& $AppUI->acl();
$project =& new CProject;

// Pull the task comments
$q->addTable('task_log');
$q->addQuery('task_log.*, user_username, task_id');
$q->addQuery('billingcode_name as task_log_costcode');
$q->addJoin('users', 'u', 'user_id = task_log_creator');
$q->addJoin('tasks', 't', 'task_log_task = t.task_id');
$q->addJoin('billingcode', 'b', 'task_log.task_log_costcode = billingcode_id');
//already included bY the setAllowedSQL function
//$q->addJoin('projects', 'p', 'task_project = p.project_id');
$q->addWhere("task_project = $project_id ");
if ($user_id>0) 
	$q->addWhere("task_log_creator=$user_id");
if ($hide_inactive) 
	$q->addWhere("task_status>=0");
if ($hide_complete) 
	$q->addWhere("task_percent_complete < 100");
if ($cost_code != '0') 
	$q->addWhere('task_log_costcode = ' . $cost_code);
$q->addOrder('task_log_date');
$project->setAllowedSQL($AppUI->user_id, $q, 'task_project');
$logs = $q->loadList();

global $tpl;
$tpl->assign('project_id', $project_id);
$tpl->assign('tab', $tab);
$tpl->assign('users', $users);
$tpl->assign('user_id', $user_id);
$tpl->assign('cost_code', $cost_code);
$tpl->assign('task_log_costcodes', $task_log_costcodes);

$tpl->assign('hide_inactive', $hide_inactive);
$tpl->assign('hide_complete', $hide_complete);

$tpl->assign('rows', $logs);
$tpl->displayFile('tasklog', 'tasks');

?>

<script type="text/javascript" language="JavaScript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt2(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Task Log', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>

