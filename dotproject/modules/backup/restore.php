<?php
// Copyright 2005, Adam Donnison <adam@saki.com.au>
// Released under GPL version 2 or later.

// Take an XML file and restore it to the database overwriting
// all the data in the database.

$perms =& $AppUI->acl();
if (! $perms->checkModule('backup', 'edit'))
  $AppUI->redirect('m=public&a=access_denied');
$AppUI->savePlace();

// Make sure the user realises that this is a drastic operation!
$titleBlock = new CTitleBlock('Restore Database', 'companies.gif', $m, $m.'.'.$a);
$titleBlock->show();

$tpl->displayFile('restore');
?>
