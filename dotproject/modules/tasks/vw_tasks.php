<?php /* TASKS $Id$ */
GLOBAL $m, $a, $project_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
GLOBAL $user_id, $dPconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
/*
        tasks.php

        This file contains common task list rendering code used by
        modules/tasks/index.php and modules/projects/vw_tasks.php

        in

        External used variables:

        * $min_view: hide some elements when active (used in the vw_tasks.php)
        * $project_id
        * $f
        * $query_string
*/

if (empty($query_string))
	$query_string = "?m=$m&a=$a";


// Number of columns (used to calculate how many columns to span things through)
$cols = 13;

/****
// Let's figure out which tasks are selected
*/

global $tasks_opened;
global $tasks_closed;

$tasks_closed = array();
$tasks_opened = $AppUI->getState("tasks_opened");
if(!$tasks_opened){
    $tasks_opened = array();
}

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$q = new DBQuery;
$pinned_only = intval( dPgetParam( $_GET, 'pinned', 0) );
if (isset($_GET['pin']))
{
        $pin = intval( dPgetParam( $_GET, "pin", 0 ) );
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
                $AppUI->setMsg( "ins/del err", UI_MSG_ERROR, true );

        $AppUI->redirect('', -1);
}
else if($task_id > 0)
        $tasks_opened[] = $task_id;


$AppUI->savePlace();

if( ($open_task_id = dPGetParam($_GET, 'open_task_id', 0)) > 0
        && !in_array($_GET['open_task_id'], $tasks_opened)) {
    $tasks_opened[] = $_GET["open_task_id"];
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

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );
if (isset($_POST['show_task_options'])) {
        $AppUI->setState('TaskListShowIncomplete', dPgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

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
//$user_id = $user_id ? $user_id : $AppUI->user_id;
$q->addWhere('task_project = ' . $project_id);

if ($pinned_only)
	$q->addWhere('task_pinned = 1');

if ($showIncomplete)
	$q->addWhere('( task_percent_complete < 100 or task_percent_complete is null )');

$q->addWhere('tasks.task_id = task_parent');
// $q->addWhere('(task_id = task_parent OR (t1.task_parent = t2.task_id AND t2.task_dynamic <> 1))');

// patch 2.12.04 text search
//if ( $search_text = $AppUI->getState('searchtext') )
//        $q->addWhere("( task_name LIKE ('%$search_text%') OR task_description LIKE ('%$search_text%') )");

// filter tasks considering task and project permissions
$tasks_filter = '';

// TODO: Enable tasks filtering

$obj =& new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if ( count($allowedTasks))
	$q->addWhere($allowedTasks);

$q->addGroup('task_id');
//$q->addOrder($task_sort_item1.', '.$task_sort_item2);

$tasks = $q->loadList();

//add information about assigned users into the page output
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

        $tasks[$k]['task_assigned_users'] = $q->loadList();
				$tasks[$k]['node_id'] = 'node-' . $task['task_id'];
				$tasks[$k]['style'] = taskstyle($task);
				$tasks[$k]['canEdit'] = !getDenyEdit( 'tasks', $task['task_id'] );
				$tasks[$k]['canViewLog'] = $perms->checkModuleItem('task_log', 'view', $task['task_id']);


//				$tasks[$k]['task_description'] = str_replace("\"", "&quot;", str_replace("\r", ' ', str_replace("\n", ' ', $task['task_description'])));
//        $alt = htmlspecialchars($alt);
}

$showEditCheckbox = false;
?>
<script type="text/JavaScript" src="modules/tasks/list.js.php"></script>
<script language="JavaScript" src="modules/tasks/tree.js?<?php echo time(); ?>"></script>

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
$tpl->assign('showIncomplete', $showIncomplete);
$tpl->assign('showEditCheckbox', $showEditCheckbox);
$tpl->assign('direct_edit_assignment', dPgetConfig('direct_edit_assignment'));
$tpl->assign('show_cols', (dPgetConfig('direct_edit_assignment')?($cols-4):($cols-1)));

$tpl->assign('durnTypes', $durnTypes);
$tpl->assign('canViewLog', $canViewTask);
$tpl->assign('canEdit', $canEdit);

$tpl->assign('style', $style);

$tpl->displayList('tasks', $tasks);
?>
