<?php //$Id$
//global $durnTypes, $AppUI;
$perms =& $AppUI->acl();
global $tpl;
if (! $perms->checkModule('tasks', 'view'))
	$AppUI->redirect('m=public&a=access_denied');

if (isset($_GET['table']))
{
	$node_id = dPgetParam($_GET, 'node_id');
	$parent = substr($node_id, strrpos($node_id, '-') + 1);
	$parent_number = substr($node_id, 5, strpos($node_id, ')') - 5);
	$q = new DBQuery;
	$q->addQuery('distinct tasks.task_id, task_parent, task_name');
	$q->addQuery('task_start_date, task_end_date, task_dynamic');
	$q->addQuery('task_pinned, pin.user_id as pin_user');
	$q->addQuery('task_priority, task_percent_complete');
	$q->addQuery('task_duration, task_duration_type');
	$q->addQuery('task_project');
	$q->addQuery('task_description, task_owner, task_status');
	$q->addQuery('usernames.user_username, usernames.user_id');
	$q->addQuery('assignees.user_username as assignee_username');
	$q->addQuery('count(distinct assignees.user_id) as assignee_count');
	$q->addQuery('co.contact_first_name, co.contact_last_name');
	$q->addQuery('task_milestone');
	$q->addQuery('count(distinct f.file_task) as file_count');
	$q->addQuery('tlog.task_log_problem');
	
	
	$q->addTable('tasks');
	$mods = $AppUI->getActiveModules();
	if (!empty($mods['history']) && !getDenyRead('history'))
	{
		$q->addQuery('history_date as last_update');
		$q->leftJoin('history', 'h', 'history_item = tasks.task_id AND history_table=\'tasks\'');
	}
	$q->leftJoin('projects', 'p', 'p.project_id = task_project');
	$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
	$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
	$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
	$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
	$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
	$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
	$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . $AppUI->user_id);
	$q->addWhere('task_parent = ' . $parent);
	$q->addWhere('task_parent <> tasks.task_id');
	$q->addGroup('task_id');
	$q->addOrder('project_id, task_start_date');
	
	//echo $q->prepare();
	//$q->addTable('tasks');
	//$q->addQuery('*');
	//$sql = $q->prepare();
	$durnTypes = dPgetSysVal( 'TaskDurationType' );
	$tasks = $q->loadList();
	$msg = db_error();
	if ($msg)
		$AppUI->setMsg('failed collapse/expand: ' . $msg, UI_MSG_WARNING);
	else
	{
		//echo 'parent: ' . $parent . '; sql: ' . $sql . '::' . db_error();
		global $durnTypes;
		$tpl->assign('durnTypes', $durnTypes);

		$tpl->assign('direct_edit_assignment', false);
		$i = 0;
		foreach ($tasks as $t)
		{
			$q->clear();
			$q->addQuery('ut.user_id,
			u.user_username, contact_email, ut.perc_assignment, SUM(ut.perc_assignment) AS assign_extent, contact_first_name, contact_last_name');
			$q->addTable('user_tasks', 'ut');
			$q->leftJoin('users', 'u', 'u.user_id = ut.user_id');
			$q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
			$q->addWhere('ut.task_id = ' . $t['task_id']);
			$q->addGroup('ut.user_id');
		
			$assigned_users = array ();
			$t['task_assigned_users'] = $q->loadList();
		
			$q->addQuery('count(*)');
			$q->addTable('tasks');
			$q->addWhere('task_parent = ' . $t['task_id']);
			$t['children'] = $q->loadResult() - 1;
			$t['task_number'] = $parent_number . '.' . (++$i);
		
			$new_id = str_replace('('.$parent_number.')', '('.$t['task_number'].')', $node_id) . '-' . $t['task_id'];
			$t['node_id'] = $new_id;
			echo $node_id . '---';
			
			$t['canEdit'] = !getDenyEdit( 'tasks', $t['task_id'] );
			$t['canViewLog'] = $perms->checkModuleItem('tasks', 'view', $t['task_id']);
			$t['style'] = taskstyle($t);
			$t['level'] = range(1, count(explode('-', $t['node_id']))-2);

			$tpl->assign('obj', $t);
			$tpl->displayFile('list.row', 'tasks');
			echo '[][][]';
		}
	}
}
else 
{
	$taskfield = dPgetParam( $_REQUEST, 'taskfield', 'new_task');
	$form = dPgetParam($_REQUEST, 'form', 'form');
		
	$proj = $_GET['project'];
	$q = new DBQuery;
	$q->addTable('tasks');
	$q->addQuery('task_id, task_name');
	if ($proj != 0)
		$q->addWhere('task_project = ' . $proj);
	$tasks = $q->loadList();
	?>

<script language="JavaScript">
function loadTasks()
{
  var tasks = new Array();
  var sel = parent.document.forms['<?php echo $form; ?>'].<?php echo $taskfield; ?>;
  while ( sel.options.length )
    sel.options[0] = null;
    
  sel.options[0] = new Option('[top task]', 0);
  <?php
    $i = 0;
    foreach($tasks as $task)
    {
      ++$i;
    ?>
  sel.options[<?php echo $i; ?>] = new Option('<?php echo addslashes($task['task_name']); ?>', <?php echo $task['task_id']; ?>);
    <?php
    }
    ?>
  }
  
  loadTasks();
</script>

<?php
}
?>
