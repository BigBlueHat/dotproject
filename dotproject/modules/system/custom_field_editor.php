<?php
/*	$Id$ 	
 *
 *	Custom field editor - lists custom fields by module 	
 */

if (!$canEdit)
	$AppUI->redirect('m=public&a=access_denied');

$AppUI->savePlace();

require_once($baseDir . '/classes/CustomFields.class.php');

$titleBlock = new CTitleBlock('Custom field editor', 'customfields.png', 'admin', 'admin.custom_field_editor');
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$edit_field_id = dpGetParam( $_POST, "field_id", NULL );

// Load module list
$q  = new DBQuery;
$q->addTable('modules');
$q->addOrder('mod_ui_order');
$q->addWhere("mod_name IN ('Companies', 'Projects', 'Tasks', 'Calendar', 'Resources')");
$modules = $q->loadList();
	
$module_fields = Array();

foreach ($modules as $i=>$module)
{
	// Load field list for each module
	$q = new DBQuery;
	$q->addTable('custom_fields_struct');
	$q->addWhere("field_module = '".strtolower($module['mod_name'])."'");
	$custom_fields = $q->loadList();

	$modules[$i]['custom_fields'] = $custom_fields;
}	

$tpl->assign('modules', $modules);
$tpl->displayFile('custom_field_editor');
?>
