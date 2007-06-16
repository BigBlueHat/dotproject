<?php /* ADMIN $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

//add or edit a system user

$user_id = dPgetParam($_GET, 'user_id', 0);

if ($user_id == 0)
	$canEdit = $canAuthor;

if ($canEdit)
	$canEdit = $perms->checkModuleItem('users', ($user_id ? 'edit' : 'add'), $user_id);

// check permissions
if (!$canEdit && $user_id != $AppUI->user_id) 
	$AppUI->redirect('m=public&a=access_denied');

$q  = new DBQuery;
$q->addQuery('u.*');
$q->addQuery('con.*');
$q->addQuery('company_id, company_name, dept_name');
$q->addTable('users', 'u');
$q->addJoin('contacts', 'con', 'user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addJoin('departments', 'dep', 'dept_id = contact_department');
$q->addWhere('u.user_id = '.$user_id);
$sql = $q->prepare();
$q->clear();

if (!db_loadHash($sql, $user) && $user_id > 0) {
	$titleBlock = new CTitleBlock('Invalid User ID', 'helix-setup-user.png', $m, "$m.$a");
	$titleBlock->addCrumb('?m=admin', 'users list');
	$titleBlock->show();
} else {
	if ( $user_id == 0)
		$user['contact_id'] = 0;
// pull companies
	$q = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_id, company_name');
	$q->addOrder('company_name');
	$companies = arrayMerge( array( 0 => '&nbsp;' ), $q->loadHashList() );

// setup the title block
	$ttl = $user_id > 0 ? 'Edit User' : 'Add User';
	$titleBlock = new CTitleBlock( $ttl, 'helix-setup-user.png', $m, "$m.$a" );
	if ($perms->checkModule('admin', 'view') && $perms->checkModule('users', 'view'))
		$titleBlock->addCrumb('?m=admin', 'users list');
	if ($user_id > 0) {
		$titleBlock->addCrumb('?m=admin&amp;a=viewuser&amp;user_id=' . $user_id, 'view this user');
		if ($perms->checkModuleItem('system', 'access', $user_id) && ($canEdit || $user_id == $AppUI->user_id)) {
			$titleBlock->addCrumb('?m=system&amp;a=addeditpref&amp;user_id=' . $user_id, 'edit preferences');
		}
	}
	$titleBlock->show();
	
	$q->addQuery('contact_id');
	$q->addQuery('contact_first_name, contact_last_name');
	$q->addQuery('contact_email');
	$q->addQuery('contact_department');
	$q->addTable('contacts');
	$q->addOrder('contact_first_name, contact_last_name');
	$contacts = $q->loadList();
	$tpl->assign('contacts', $contacts);
	$user['canEdit'] = $canEdit;

	if ($user['user_contact']) 
	{
		$q->addQuery('contact_department, dept_name');
		$q->addTable('contacts');
		$q->addJoin('departments', 'd', 'contact_department = dept_id');
		$q->addWhere('contact_id = ' . $user['user_contact']);
		$contact = $q->loadList();
		$user['contact_department'] = $contact[0]['contact_department'];
		$user['dept_name'] = $contact[0]['dept_name'];
	}
	
	$tpl->assign('companies', $companies);
	$tpl->assign('utypes', $utypes);
	$tpl->displayAddEdit($user);
?>
<script language="javascript" type="text/javascript">
<!--
var emails = new Array();
<?php
foreach($contacts as $contact)
	echo 'emails['.$contact['contact_id'].'] = "' . $contact['contact_email'] . '";';
?>

function setContact() {
	var form = document.editFrm;
	contact = form.contact_id;
	contact_option = contact.options[contact.selectedIndex];
	contact_id = contact_option.value;
	contact_name = contact_option.text;
	form.contact_first_name.value = contact_name.substring(0, contact_name.indexOf(' '));
	form.contact_last_name.value = contact_name.substring(contact_name.indexOf(' ') + 1);
	form.contact_email.value = emails[contact_id];
}

function submitIt() {
    var form = document.editFrm;
   if (form.user_username.value.length < <?php echo dPgetConfig('username_min_len', 3); ?> && form.user_username.value != 'admin') {
        alert("<?php echo $AppUI->_('adminValidUserName', UI_OUTPUT_JS)  ;?>"  + <?php echo dPgetConfig('username_min_len', 3); ?>);
        form.user_username.focus();
    } else if (form.user_password.value.length < <?php echo dPgetConfig('password_min_len', 6); ?>) {
        alert("<?php echo $AppUI->_('adminValidPassword', UI_OUTPUT_JS);?>" + <?php echo dPgetConfig('password_min_len', 6); ?>);
        form.user_password.focus();
    } else if (form.user_password.value !=  form.password_check.value) {
        alert("<?php echo $AppUI->_('adminPasswordsDiffer', UI_OUTPUT_JS);?>");
        form.user_password.focus();
    } else if (form.contact_first_name.value.length < 1) {
        alert("<?php echo $AppUI->_('adminValidFirstName', UI_OUTPUT_JS);?>");
        form.contact_first_name.focus();
    } else if (form.contact_last_name.value.length < 1) {
        alert("<?php echo $AppUI->_('adminValidLastName', UI_OUTPUT_JS);?>");
        form.contact_last_name.focus();
    } else if (form.contact_email.value.length < 4) {
        alert("<?php echo $AppUI->_('adminInvalidEmail', UI_OUTPUT_JS);?>");
        form.contact_email.focus();
    } else if (form.contact_company.value < 1) {
        alert("<?php echo $AppUI->_('adminInvalidCompany', UI_OUTPUT_JS);?>");
        form.contact_company.focus();
    } else {
        form.submit();
    }
}

function popDept() {
    var f = document.editFrm;
    if (f.selectedIndex == 0) {
        alert('<?php echo $AppUI->_( 'Please select a company first!', UI_OUTPUT_JS ); ?>');
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setDept&table=departments&company_id='
            + f.contact_company.options[f.contact_company.selectedIndex].value
            + '&dept_id='+f.contact_department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.contact_department.value = key;
        f.dept_name.value = val;
    } else {
        f.contact_department.value = '0';
        f.dept_name.value = '';
    }
}
-->
</script>

<?php } ?>