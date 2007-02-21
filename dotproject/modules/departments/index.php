<?php /* DEPARTMENTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$titleBlock = new CTitleBlock('Departments', 'users.gif', $m, '');
$titleBlock->addCrumb('?m=companies', 'companies list');
$titleBlock->show();

echo $AppUI->_( 'deptIndexPage' );
?>