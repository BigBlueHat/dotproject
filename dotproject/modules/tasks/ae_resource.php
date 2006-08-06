<?php
// $Id$
global $AppUI, $users, $task_id, $task_project, $obj, $projTasksWithEndDates, $tab, $loadFromTab;

if ( $task_id == 0 ) {
	// Add task creator to assigned users by default
	$assigned_perc = array($AppUI->user_id => '100');	
} else {
	// Pull users on this task
	$q = new DBQuery;
	$q->addQuery('user_id, perc_assignment');
	$q->addTable('user_tasks');
	$q->addWhere('task_id = '.$task_id);
	$q->addOrder('task_id <> 0');
	$assigned_perc = $q->loadHashList();	
	$q->clear();
}

$initPercAsignment = '';
$assigned = array();
foreach ($assigned_perc as $user_id => $perc) {
	$assigned[$user_id] = $users[$user_id] . ' [' . $perc . '%]';
	$initPercAsignment .= "$user_id=$perc;";
}

for ($i = 5; $i <= 100; $i+=5)
	$percentages = '<option '.(($i==100)? 'selected="true"' : '' ).' value="'.$i.'">'.$i.'%</option>';

global $tpl;

$tpl->assign('can_edit_time_information', $can_edit_time_information);
$tpl->assign('tab', $tab);

$tpl->assign('users', $users);
$tpl->assign('assigned', $assigned);
$tpl->assign('initPercAsignment', $initPercAsignment);
$tpl->assign('percentages', $percentages);
$tpl->assign('notify_by_default', $AppUI->getPref('TASKNOTIFYBYDEF'));
$tpl->assign('task_with_enddates', $projTasksWithEndDates);

$tpl->assign('obj', $obj); 

$tpl->displayFile('ae_resource');
?>