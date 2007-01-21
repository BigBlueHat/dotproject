<?php /* SYSTEM $Id$*/

$AppUI->savePlace();

$canEdit = !getDenyEdit( $m );
$canRead = !getDenyRead( $m );
if (!$canRead)
	$AppUI->redirect('m=public&a=access_denied');

$q = new DBQuery;
$q->addQuery('*');
$q->addTable('modules');
$q->addWhere("mod_name <> 'Public'");
$q->addOrder('mod_ui_order');
$modules = db_loadList( $q->prepare() );

// get the modules actually installed on the file system
$modFiles = $AppUI->readDirs('modules');
// and remove the public module and install module
if (isset($modFiles['public'])) 
	unset($modFiles['public']);
if (isset($modFiles['install'])) 
	unset($modFiles['install']);

$titleBlock = new CTitleBlock('Modules', 'power-management.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();


$tpl->assign('canEdit', $canEdit);
$tpl->assign('dPconfig', $dPconfig);
$tpl->assign('m', $m);
$tpl->assign('modFiles', $modFiles);
$tpl->assign('modules', $modules);
// $tpl->assign('', $);
$tpl->displayFile('viewmods', 'system');

?>
