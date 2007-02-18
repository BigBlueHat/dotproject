<?php /* ADMIN $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

GLOBAL $AppUI, $user_id, $canEdit, $canDelete, $tab, $tpl;

//$roles
// Create the roles class container
require_once(DP_BASE_DIR . '/modules/system/roles/roles.class.php');

$perms =& $AppUI->acl();
$user_roles = $perms->getUserRoles($user_id);
$crole =& new CRole;
$roles = $crole->getRoles();
// Format the roles for use in arraySelect
$roles_arr = array();
foreach ($roles as $role)
  $roles_arr[$role['id']] = $role['name'];
  
$tpl->assign('canEdit', $canEdit);
$tpl->assign('roles_arr', $roles_arr);
$tpl->assign('user_id', $user_id);
$tpl->assign('user_name', $user_name);
$tpl->assign('user_roles', $user_roles);
$tpl->displayFile('usr_roles', $users);
?>

<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this role?' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php
}?>
-->
</script>
