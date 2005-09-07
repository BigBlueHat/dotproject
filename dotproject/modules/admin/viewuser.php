<?php /* ADMIN $Id$ */

$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

if ($user_id != $AppUI->user_id 
&& ( ! $perms->checkModuleItem('admin', 'view', $user_id) 
|| ! $perms->checkModuleItem('users', 'view', $user_id) ) )
	$AppUI->redirect('m=public&a=access_denied');

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;

// pull data
$q  = new DBQuery;
$q->addTable('users', 'u');
$q->addQuery('u.*');
$q->addQuery('con.*, company_id, company_name, dept_name, dept_id');
$q->addJoin('contacts', 'con', 'user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addJoin('departments', 'dep', 'dept_id = contact_department');
$q->addWhere('u.user_id = '.$user_id);
$sql = $q->prepare();
$q->clear();

if (!db_loadHash( $sql, $user )) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=admin", "users list" );
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View User', 'helix-setup-user.png', $m, "$m.$a" );
	if ($canRead) {
	  $titleBlock->addCrumb( "?m=admin", "users list" );
	}
	if ($canEdit || $user_id == $AppUI->user_id) {
	      $titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=$user_id", "edit this user" );
	      $titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );
	      $titleBlock->addCrumbRight(
			'<a href="#" onclick="popChgPwd();return false">' . $AppUI->_('change password') . '</a>'
	      );
	      $titleBlock->addCell('<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>');
	}
	$titleBlock->show();
?>
<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit || $user_id == $AppUI->user_id) {
?>
function popChgPwd() {
	window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $user['user_id']; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );
}
<?php } ?>
</script>



<?php
	$user['user_type_name'] = $utypes[$user['user_type']];
	$tpl->displayView($user);
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=admin&a=viewuser&user_id=$user_id", "{$dPconfig['root_dir']}/modules/admin/", $tab );
	$tabBox->add( 'vw_usr_proj', 'Owned Projects' );
	$tabBox->add( 'vw_usr_log', 'User Log');
	$tabBox->add( 'vw_usr_perms', 'Permissions' );
	$tabBox->add( 'vw_usr_roles', 'Roles' );
	$tabBox->show();
}
?>
