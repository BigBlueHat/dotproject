<?php
// $Id$
global $AppUI, $dPconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $obj;
global $durnTypes, $task_project, $task_id, $tab;

//Time arrays for selects
$start = intval(dPgetConfig('cal_day_start'));
$end   = intval(dPgetConfig('cal_day_end'));
$inc   = intval(dPgetConfig('cal_day_increment'));
if ($start === null ) $start = 8;
if ($end   === null ) $end = 17;
if ($inc   === null)  $inc = 15;
$hours = array();
for ( $current = $start; $current < $end + 1; $current++ ) {
	if ( $current < 10 ) { 
		$current_key = '0' . $current;
	} else {
		$current_key = $current;
	}
	
	if ( stristr($AppUI->getPref('TIMEFORMAT'), '%p') ){
		//User time format in 12hr
		$hours[$current_key] = ( $current > 12 ? $current-12 : $current );
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

// Pull tasks dependencies
$q  = new DBQuery;
$q->addTable('tasks', 't');
$q->addQuery('task_id, task_name');

$deps = false;
if ($deps) {
	$q->addWhere(' task_id in ('.$deps.')');
} else {
	$q->addTable('task_dependencies', 'td');
	$q->addWhere('td.dependencies_task_id = '.$task_id);
	$q->addWhere('t.task_id = td.dependencies_req_task_id');
}
$taskDep = $q->loadHashList();
$q->clear();

global $tpl;

$tpl->assign('can_edit_time_information', $can_edit_time_information);
$tpl->assign('task_parent_options', str_replace('selected', '', $task_parent_options));
$tpl->assign('taskDep', $taskDep?$taskDep:array());
$tpl->assign('tab', $tab); 

$tpl->assign('obj', $obj); 

$tpl->displayFile('ae_depend');
?>