<?php /* ADMIN $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $addPwT, $company_id, $dept_ids, $department, $min_view, $m, $a;

$user_id = dPgetParam($_GET, 'user_id', 0);

if ($user_id != $AppUI->user_id 
&& ( ! $perms->checkModuleItem('admin', 'view', $user_id) 
|| ! $perms->checkModuleItem('users', 'view', $user_id) ) )
	$AppUI->redirect('m=public&a=access_denied');

$AppUI->savePlace();

$company_id = $AppUI->getState('UsrProjIdxCompany') !== NULL ? $AppUI->getState( 'UsrProjIdxCompany' ) : $AppUI->user_company;

$company_prefix = 'company_';

if (isset( $_POST['department'] )) {
	$AppUI->setState('UsrProjIdxDepartment', $_POST['department']);
	
	//if department is set, ignore the company_id field
	unset($company_id);
}
$department = $AppUI->getState('UsrProjIdxDepartment') !== NULL ? $AppUI->getState('UsrProjIdxDepartment') : $company_prefix.$AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the 
// $department variable, and populate the $company_id variable.
if(!(strpos($department, $company_prefix)===false)){
	$company_id = substr($department,strlen($company_prefix));
	$AppUI->setState('UsrProjIdxCompany', $company_id);
	unset($department);
}

if (isset($_GET['tab']))
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
	
$tab = $AppUI->getState('UserVwTab') !== NULL ? $AppUI->getState('UserVwTab') : 0;


// pull data
$q  = new DBQuery;
$q->addQuery('u.*');
$q->addQuery('con.*, company_id, company_name');
$q->addQuery('dept_name, dept_id');
$q->addTable('users', 'u');
$q->addJoin('contacts', 'con', 'user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addJoin('departments', 'dep', 'dept_id = contact_department');
$q->addWhere('u.user_id = '.$user_id);
list($user) = $q->loadList();

if (!$user) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	$titleBlock->addCrumb('?m=admin', 'users list');
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View User', 'helix-setup-user.png', $m, "$m.$a" );
	if ($canRead) {
	  $titleBlock->addCrumb('?m=admin', 'users list');
  }
	
	if ($canEdit || $user_id == $AppUI->user_id) {
		$titleBlock->addCrumb('?m=admin&amp;a=addedituser&amp;user_id=' . $user_id, 'edit this user');
		if ($perms->checkModuleItem('system', 'access', $user_id)) {
			$titleBlock->addCrumb('?m=system&amp;a=addeditpref&amp;user_id=' . $user_id, 'edit preferences');
		}
		$titleBlock->addCrumbRight('<a href="#" onclick="popChgPwd();return false">' . $AppUI->_('change password') . '</a>');
		$titleBlock->addCell('<input type="button" class=button value="'.$AppUI->_('add user').'" onclick="javascript:window.location=\'./index.php?m=admin&amp;a=addedituser\';" />');
	}
	$titleBlock->show();
?>
<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit || $user_id == $AppUI->user_id) {
?>
function popChgPwd() {
	window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $user['user_id']; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scrollbars=no' );
}
<?php } ?>
-->
</script>

<?php
	$user['user_type_name'] = $utypes[$user['user_type']];
	$tpl->displayView($user);
		
	// tabbed information boxes
	$min_view = true;
	$tabBox = new CTabBox('?m=admin&amp;a=viewuser&amp;user_id=' . $user_id, '', $tab);
	$tabBox->loadExtras('admin', 'viewuser'); 
	$tabBox->add(DP_BASE_DIR.'/modules/admin/vw_usr_log', 'User Log');
	$tabBox->add(DP_BASE_DIR.'/modules/admin/vw_usr_perms', 'Permissions');
	$tabBox->add(DP_BASE_DIR.'/modules/admin/vw_usr_roles', 'Roles');
	$tabBox->show();
}
?>