<?php /* ADMIN $Id$ */

$del = dPgetParam($_REQUEST, 'del', false);

$perms =& $AppUI->acl();

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Roles' );

if ($del) {
	if ($perms->deleteUserRole($_REQUEST['role_id'], $_REQUEST['user_id']))
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	else
		$AppUI->setMsg('failed to delete role', UI_MSG_ERROR);

	$AppUI->redirect();
}

if (isset($_REQUEST['user_role']) && $_REQUEST['user_role']) {
	if ( $perms->insertUserRole($_REQUEST['user_role'], $_REQUEST['user_id']))
		$AppUI->setMsg('added', UI_MSG_ALERT, true);
	else
		$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
		
	$AppUI->redirect();
}
?>