<?php /* SYSTEM $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$perms =& $AppUI->acl();
if (! $perms->checkModule($m, 'view'))
	$AppUI->redirect('m=public&a=access_denied');
if (! $perms->checkModule('users', 'view'))
	$AppUI->redirect('m=public&a=access_denied');

$AppUI->savePlace();

$titleBlock = new CTitleBlock( 'System Administration', '48_my_computer.png', $m, "$m.$a" );
$titleBlock->show();

$tpl->displayFile('index');
?>