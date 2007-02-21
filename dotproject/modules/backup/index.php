<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/**
 * backup database module for dotProject
 * (c)2003 Daniel Vijge
 * Changes by Adam Donnison <ajdonnison@dotproject.net>
 * Changes include:
 *   Upgraded to work with dotProject 2.0
 *   Added permissions check to ensure user is allowed to backup
 *   Added XML backup option
 *   Completely reworked the backup system to use ADODB primitives.
 *   Added localisation code so that it can be translated.
 */ 

$perms =& $AppUI->acl();
if (! $perms->checkModule('backup', 'view'))	// Should we have an exec permission?
	$AppUI->redirect("m=public&a=access_denied");

$title =& new CTitleBlock('Backup Database', 'companies.gif', $m, $m .'.'.$a);
$title->addCrumb('index.php?m=backup&a=restore', 'restore xml file');
$title->show();

$tpl->displayFile('index');
?>