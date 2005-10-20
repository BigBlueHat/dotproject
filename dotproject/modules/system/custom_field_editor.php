<?php
/*	$Id$ 	
 *
 *	Custom field editor - lists custom fields by module 	
 *
 */

if (!$canEdit) {
    $AppUI->redirect( "m=public&a=access_denied" );
}
	$AppUI->savePlace();

	require_once("./classes/CustomFields.class.php");

	$titleBlock = new CTitleBlock('Custom field editor', "customfields.png", "admin", "admin.custom_field_editor");
	$titleBlock->addCrumb( "?m=system", "system admin" );

	$edit_field_id = dpGetParam( $_POST, "field_id", NULL );

	$titleBlock->show();

	// Load module list
	$q  = new DBQuery;
	$q->addTable('modules');
	$q->addOrder('mod_ui_order');
	$q->addWhere("mod_name IN ('Companies', 'Projects', 'Tasks', 'Calendar')");
	$modules = $q->loadList();
	$q->clear();
	
	$module_fields = Array();

	foreach ($modules as $i=>$module)
	{
		// Load field list for each module
		$q = new DBQuery;
                $q->addTable('custom_fields_struct');
                $q->addWhere("field_module = '".strtolower($module["mod_name"])."'");
                $custom_fields = $q->loadList();

		$modules[$i]['custom_fields'] = $custom_fields;
                $q->clear();
	}	

	$tpl->assign('modules', $modules);
	$tpl->displayFile('custom_field_editor');
?>
