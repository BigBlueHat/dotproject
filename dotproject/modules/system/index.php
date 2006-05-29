<?php /* SYSTEM $Id$ */
$perms =& $AppUI->acl();
if (! $perms->checkModule($m, 'view'))
	$AppUI->redirect('m=public&amp;a=access_denied');
if (! $perms->checkModule('users', 'view'))
	$AppUI->redirect('m=public&amp;a=access_denied');


$AppUI->savePlace();

$titleBlock = new CTitleBlock( 'System Administration', '48_my_computer.png', $m, "$m.$a" );
$titleBlock->show();

$tpl->displayFile('index');
?>
