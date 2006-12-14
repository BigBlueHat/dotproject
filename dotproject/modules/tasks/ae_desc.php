<?php
// $Id$
	global $tpl, $AppUI, $task_id, $obj, $users, $task_access, $department_selection_list;
	global $task_parent_options, $dPconfig, $projects, $task_project, $can_edit_time_information, $tab;

	$perms =& $AppUI->acl();

	$tpl->assign('can_edit_time_information', $can_edit_time_information);
	$tpl->assign('task_project', $task_project);
	$tpl->assign('task_id', $task_id); 
	$tpl->assign('users', $users);
	$task_owner = !isset($obj->task_owner) ? $AppUI->user_id : $obj->task_owner;
	$tpl->assign('task_owner', $task_owner);
	$tpl->assign('task_access', $task_access);
	$contacts_active = ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) ? true : false; 
	$tpl->assign('contacts_active', $contacts_active);	
	$tpl->assign('department_selection_list', $department_selection_list);
	$tpl->assign('task_parent_options', $task_parent_options);
	$tpl->assign('projects', $projects);

	$tpl->assign('sysval_task_type', dPgetSysVal('TaskType'));

	require_once($baseDir . '/classes/CustomFields.class.php');
	GLOBAL $m;
	$custom_fields = New CustomFields( $m, 'addedit', $obj->task_id, 'edit' );
	$custom_fields_html = $custom_fields->getHTML();
	$tpl->assign('custom_fields_html', $custom_fields_html);
	$tpl->assign('tab', $tab);
	$tpl->assign('obj', $obj);

	$tpl->displayFile('ae_desc');
?>
