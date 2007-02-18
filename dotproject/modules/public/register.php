<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$register = dPgetParam($_POST, 'user_username', false);
if ($register)
{
	require_once(DP_BASE_DIR . '/modules/system/roles/roles.class.php');
	require_once(DP_BASE_DIR . '/modules/admin/admin.class.php');

	$user = new CUser;
	$user->bind($_POST);
	if ($msg = $user->store()) {
		echo 'failed: ' . $msg;
		exit;
	}

	$perms =& $AppUI->acl();
	$user_roles = $perms->getUserRoles($user_id);
	$crole =& new CRole;
	$roles = $crole->getRoles();
	foreach ($roles as $role)
		if (strtolower($role['name']) == 'guest')
			$role_id = $role['id'];

	$perms->insertUserRole($role_id, $user->user_id);
	
	$AppUI->redirect('logout=-1');
}
else
	$tpl->displayFile('register');
?>
