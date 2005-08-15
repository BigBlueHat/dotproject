<?php /* ADMIN $Id$ */
//add or edit a system user

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;

if ($user_id == 0)
	$canEdit = $canAuthor;

if ($canEdit)
	$canEdit = $perms->checkModuleItem('users', ($user_id ? 'edit' : 'add'), $user_id);

// check permissions
if (!$canEdit && $user_id != $AppUI->user_id) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$q  = new DBQuery;
$q->addTable('users', 'u');
$q->addQuery('u.*');
$q->addQuery('con.*, company_id, company_name, dept_name');
$q->addJoin('contacts', 'con', 'user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addJoin('departments', 'dep', 'dept_id = contact_department');
$q->addWhere('u.user_id = '.$user_id);
$sql = $q->prepare();
$q->clear();

if (!db_loadHash( $sql, $user ) && $user_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=admin", "users list" );
	$titleBlock->show();
} else {
	 if ( $user_id == 0)
        $user['contact_id'] = 0;
// pull companies
	$q = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_id, company_name');
	$q->addOrder('company_name');
	$companies = arrayMerge( array( 0 => '' ), $q->loadHashList() );

// setup the title block
	$ttl = $user_id > 0 ? "Edit User" : "Add User";
	$titleBlock = new CTitleBlock( $ttl, 'helix-setup-user.png', $m, "$m.$a" );
	if ($perms->checkModule('admin', 'view') && $perms->checkModule('users', 'view'))
		$titleBlock->addCrumb( "?m=admin", "users list" );
	if ($user_id > 0) {
		$titleBlock->addCrumb( "?m=admin&a=viewuser&user_id=$user_id", "view this user" );
		if ($canEdit || $user_id == $AppUI->user_id) {
		$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );
		}
	}
	$titleBlock->show();
	
	$user['canEdit'] = $canEdit;
	
	$tpl->assign('companies', $companies);
	$tpl->assign('utypes', $utypes);
	$tpl->displayAddEdit($user);
	
?>
<SCRIPT language="javascript">
function submitIt(){
    var form = document.editFrm;
   if (form.user_username.value.length < <?php echo dPgetConfig('username_min_len'); ?> && form.user_username.value != 'admin') {
        alert("<?php echo $AppUI->_('adminValidUserName', UI_OUTPUT_JS)  ;?>"  + <?php echo dPgetConfig('username_min_len'); ?>);
        form.user_username.focus();
    } else if (form.user_password.value.length < <?php echo dPgetConfig('password_min_len'); ?>) {
        alert("<?php echo $AppUI->_('adminValidPassword', UI_OUTPUT_JS);?>" + <?php echo dPgetConfig('password_min_len'); ?>);
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
    } else if (form.contact_birthday && form.contact_birthday.value.length > 0) {
        dar = form.contact_birthday.value.split("-");
        if (dar.length < 3) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.contact_birthday.focus();
        } else if (isNaN(parseInt(dar[0],10)) || isNaN(parseInt(dar[1],10)) || isNaN(parseInt(dar[2],10))) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[1],10) < 1 || parseInt(dar[1],10) > 12) {
            alert("<?php echo $AppUI->_('adminInvalidMonth', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[2],10) < 1 || parseInt(dar[2],10) > 31) {
            alert("<?php echo $AppUI->_('adminInvalidDay', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.contact_birthday.focus();
        } else if(parseInt(dar[0],10) < 1900 || parseInt(dar[0],10) > 2020) {
            alert("<?php echo $AppUI->_('adminInvalidYear', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.contact_birthday.focus();
        } else {
            form.submit();
        }
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
</script>


<?php } ?>
