<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $showEditCheckbox, $tasks, $priorities, $m, $a, $date, $min_view, $other_users, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks, $showLowTasks, $showEmptyDate, $user_id;
global $tpl;
$perms =& $AppUI->acl();
$canDelete = $perms->checkModuleItem($m, 'delete');

$users = dPgetUsers();
unset($users[0]);

$tpl->assign('users', $users);
$tpl->assign('user_id', $user_id);
$tpl->assign('other_users', $other_users);
$tpl->assign('date', $date);

$tpl->displayFile('todo_gantt', 'tasks');

$min_view = true;
include DP_BASE_DIR.'/modules/tasks/viewgantt.php';
?>