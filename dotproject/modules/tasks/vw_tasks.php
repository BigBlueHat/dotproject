<?php /* TASKS $Id$ */
GLOBAL $m, $a, $project_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
GLOBAL $user_id, $dPconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;

$toggleAll = dPgetParam($_GET, 'parents', false);

/*
        External used variables:

        * $min_view: hide some elements when active (used in the vw_tasks.php)
        * $project_id
        * $f
        * $query_string
*/
if ($AppUI->getState('task_percent_complete', false) === false) {
	$AppUI->setState('task_percent_complete', 99);
}

$filters_selection = array(
  'task_percent_complete' => array(-1 => 'All', 0 => 'not started', 1 => 'started', 99 => 'not complete', 100 => 'finished'),
  'task_status' => arrayMerge( array( '-1'=>$AppUI->_('All') ), dPgetSysVal( 'TaskStatus' )),
  'task_type' => arrayMerge(   array( '-1'=>$AppUI->_('All') ), dPgetSysVal( 'TaskType' ))
);

$tasksTitleBlock = new CTitleBlock( 'Tasks', 'applet-48.png' );
$filters = $tasksTitleBlock->addFiltersCell($filters_selection);
$tasksTitleBlock->show();


if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

// Number of columns (used to calculate how many columns to span things through)
$cols = 13;

/****
// Let's figure out which tasks are selected
*/

global $tasks_opened;
global $tasks_closed;

$tasks_closed = array();
$tasks_opened = $AppUI->getState('tasks_opened');
if(!$tasks_opened){
    $tasks_opened = array();
}

$task_id = intval( dPgetParam( $_GET, 'task_id', 0 ) );
$q = new DBQuery;
$pinned_only = intval( dPgetParam( $_GET, 'pinned', 0) );
if (isset($_GET['pin']))
{
        $pin = intval( dPgetParam( $_GET, 'pin', 0 ) );
        $msg = '';

        // load the record data
        if($pin) {
                $q->addTable('user_task_pin');
                $q->addInsert('user_id', $AppUI->user_id);
                $q->addInsert('task_id', $task_id);
        } else {
                $q->setDelete('user_task_pin');
                $q->addWhere('user_id = ' . $AppUI->user_id);
                $q->addWhere('task_id = ' . $task_id);
        }

        if ( !$q->exec() )
                $AppUI->setMsg( 'ins/del err', UI_MSG_ERROR, true );

        $AppUI->redirect('', -1);
}
else if($task_id > 0)
        $tasks_opened[] = $task_id;


$AppUI->savePlace();

if( ($open_task_id = dPGetParam($_GET, 'open_task_id', 0)) > 0
        && !in_array($_GET['open_task_id'], $tasks_opened)) {
    $tasks_opened[] = $_GET['open_task_id'];
}

// Closing tasks needs also to be within tasks iteration in order to
// close down all child tasks
if(($close_task_id = dPGetParam($_GET, 'close_task_id', 0)) > 0) {
    closeOpenedTask($close_task_id);
}

// We need to save tasks_opened until the end because some tasks are closed within tasks iteration
/// End of tasks_opened routine

$durnTypes = dPgetSysVal( 'TaskDurationType' );
$taskPriority = dPgetSysVal( 'TaskPriority' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', $AppUI->getState('tsi1_'.$project_id, 'task_start_date') );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', $AppUI->getState('tst1_'.$project_id, '1') );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', $AppUI->getState('tsi2_'.$project_id, 'task_end_date') );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', $AppUI->getState('tst2_'.$project_id, '1') );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', $AppUI->getState('tso1_'.$project_id, 4) ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', $AppUI->getState('tso2_'.$project_id, 3) ) );

$AppUI->setState('tsi1_'.$project_id, $task_sort_item1);
$AppUI->setState('tsi2_'.$project_id, $task_sort_item2);
$AppUI->setState('tst1_'.$project_id, $task_sort_type1);
$AppUI->setState('tst2_'.$project_id, $task_sort_type2);
$AppUI->setState('tso1_'.$project_id, $task_sort_order1);
$AppUI->setState('tso2_'.$project_id, $task_sort_order2);

$where = '';
require_once $AppUI->getModuleClass('projects');
$project =& new CProject;

$perms =& $AppUI->acl();
$canViewTask = $perms->checkModule('tasks', 'view');

$q->clear();

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
$q->addWhere('task_project = ' . $project_id);
$q->addWhere("task_name like '%" . $AppUI->getState('searchtext') . "%'");

foreach ($filters as $name => $filter) {
  if ($filter != '') {	
    if ($filter == -1) {
      // do nothing
    } else {
      if ($name == 'task_percent_complete') {
      	if ($filter == 1) {
      	  $q->addWhere('(task_percent_complete > 0 AND task_percent_complete < 100 OR task_percent_complete is null)');
      	} else if ($filter == 99) {
      	  $q->addWhere('(task_percent_complete < 100 OR task_percent_complete is null)');
      	} else {
		  $q->addWhere('task_percent_complete = ' . $filter);
        }
  	  } else {
  		$q->addWhere($name . ' = ' . $filter);
      }
    }
  }
}

if ($pinned_only)
	$q->addWhere('task_pinned = 1');

$q->addWhere('tasks.task_id = task_parent');

// filter tasks considering task and project permissions
$tasks_filter = '';

// TODO: Enable tasks filtering

$obj =& new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if ( count($allowedTasks))
	$q->addWhere($allowedTasks);

$q->addOrder($task_sort_item1.', '.$task_sort_item2);
$q->addGroup('tasks.task_id');
$tasks = $q->loadList();

//add information about assigned users into the page output
$i = 0;
global $display_tasks;
foreach ($tasks as $k => $task) 
{
        $q->clear();
        $q->addQuery('ut.user_id, u.user_username');
		$q->addQuery('contact_email, ut.perc_assignment, SUM(ut.perc_assignment) AS assign_extent');
		$q->addQuery('contact_first_name, contact_last_name');
        $q->addTable('user_tasks', 'ut');
        $q->leftJoin('users', 'u', 'u.user_id = ut.user_id');
        $q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
        $q->addWhere('ut.task_id = ' . $task['task_id']);
        $q->addGroup('ut.user_id');
		$q->addOrder('perc_assignment desc, user_username');
        $task['task_assigned_users'] = $q->loadList();
				
		$q->addQuery('count(*) as children');
		$q->addTable('tasks');
		$q->addWhere('task_parent = ' . $task['task_id']);
		$q->addWhere('task_id <> task_parent');
		$task['children'] = $q->loadResult();
		$task['style'] = taskstyle($task);
		$task['canEdit'] = !getDenyEdit( 'tasks', $task['task_id'] );
		$task['canViewLog'] = $perms->checkModuleItem('task_log', 'view', $task['task_id']);
		$task['task_number'] = ++$i;
		$task['node_id'] = 'node_'.$i.'-' . $task['task_id'];

		if (strpos($task['task_duration'], '.') && $task['task_duration_type'] == 1) {
			$task['task_duration'] = floor($task['task_duration']) . ':' . round(60 * ($task['task_duration'] - floor($task['task_duration'])));
		}

		$display_tasks[$i] = $task;
		if ($task['children'] > 0 && $toggleAll == 'open') {
			recurse_children($task['node_id']);
		}
}

//natural sorting instead?
if (is_array($display_tasks)) {
	ksort($display_tasks);
}  

// Code duplicated from above. To be cleaned!!! (to be done in one place)
function recurse_children($node_id)
{
	global $display_tasks, $perms, $task_sort_item1, $task_sort_item2;

	$task_id = substr($node_id, strrpos($node_id, '-') + 1);
	$q = new DBQuery;
	$q->addQuery('*');
	$q->addTable('tasks');
	$q->addWhere('task_parent = ' . $task_id);
	$q->addWhere('task_id <> task_parent');
	$q->addOrder($task_sort_item1.', '.$task_sort_item2);
	
	// To be sorted (by date? reversed?)
	$tasks = $q->loadList();
	$i = 0;
	$num = substr($node_id, 5, strpos($node_id, ')') - 5);
	foreach($tasks as $task)
	{
		$q->addQuery('ut.user_id, u.user_username');
		$q->addQuery('contact_email, ut.perc_assignment, SUM(ut.perc_assignment) AS assign_extent');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addTable('user_tasks', 'ut');
		$q->leftJoin('users', 'u', 'u.user_id = ut.user_id');
		$q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
		$q->addWhere('ut.task_id = ' . $task['task_id']);
		$q->addGroup('ut.user_id');
		$task['task_assigned_users'] = $q->loadList();
		
		$q->addQuery('count(*) as children');
		$q->addTable('tasks');
		$q->addWhere('task_parent = ' . $task['task_id']);
		$q->addWhere('task_id <> task_parent');
		$task['children'] = intval($q->loadResult());
		$task['style'] = taskstyle($task);
		$task['canEdit'] = !getDenyEdit( 'tasks', $task['task_id'] );
		$task['canViewLog'] = $perms->checkModuleItem('task_log', 'view', $task['task_id']);
		$task['task_number'] = $num . '.' . (++$i);
		$task['node_id'] = str_replace('('.$num.')', '('.$task['task_number'].')', $node_id) . '-' . $task['task_id'];
		$task['level'] = range(1, count(explode('.', $task['task_number']))-1);
		
		if (strpos($task['task_duration'], '.') && $task['task_duration_type'] == 1)
			$task['task_duration'] = floor($task['task_duration']) . ':' . round(60 * ($task['task_duration'] - floor($task['task_duration'])));

		$display_tasks[$num . '.' . $i] = $task;
		if ($task['children'] > 0)
			recurse_children($task['node_id']);
	}
}



$showEditCheckbox = false;
?>
<script type="text/JavaScript" src="modules/tasks/list.js.php"></script>
<script type="text/JavaScript" src="modules/tasks/tree.js?<?php echo time(); ?>"></script>

<?php
$AppUI->setState('tasks_opened', $tasks_opened);

$tpl->assign('project_id', $project_id);
$tpl->assign('task_id', $task_id);
$tpl->assign('user_id', $user_id);

$tpl->assign('cols', $cols);

$tpl->assign('sort1', $task_sort_item1);
$tpl->assign('sort2', $task_sort_item2);

$tpl->assign('sort_order1', $task_sort_order1);
$tpl->assign('sort_order2', $task_sort_order2);
$tpl->assign('sort_type1', $task_sort_type1);
$tpl->assign('sort_type2', $task_sort_type2);

$tpl->assign('query_string', $query_string);
//$tpl->assign('showIncomplete', $showIncomplete);
$tpl->assign('showEditCheckbox', $showEditCheckbox);
$tpl->assign('direct_edit_assignment', dPgetConfig('direct_edit_assignment'));
$tpl->assign('show_cols', (dPgetConfig('direct_edit_assignment')?($cols-4):($cols-1)));

$tpl->assign('durnTypes', $durnTypes);
$tpl->assign('canViewLog', $canViewTask);
$tpl->assign('canEdit', $canEdit);

$tpl->assign('style', $style);
$tpl->assign('is_opened', $toggleAll == 'open');
$tpl->assign('ajax', dPgetConfig('tasks_ajax_list'));
$tpl->displayList('tasks', $display_tasks);

if ($toggleAll == 'open')
{
?>
<script type="text/javascript">
	table = document.getElementById('tbl'); 
	parents = 'open';
	dpExpandAll();
</script>
<?php } ?>
