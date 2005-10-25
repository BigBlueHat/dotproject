<?php /* DEPARTMENTS $Id$ */
// Add / Edit Company
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions for this department
$canEdit = !getDenyEdit( $m, $dept_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull data for this department
$q  = new DBQuery;
$q->addTable('departments','dep');
$q->addQuery('dep.*, company_name');
$q->addJoin('companies', 'com', 'com.company_id = dep.dept_company');
$q->addWhere('dep.dept_id = '.$dept_id);
$sql = $q->prepare();
$q->clear();
if (!db_loadHash( $sql, $drow ) && $dept_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Department ID', 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	if ($company_id) {
		$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	}
	$titleBlock->show();
} else {
	##echo $sql.db_error();##
	$company_id = $dept_id ? $drow['dept_company'] : $company_id;

	// check if valid company
	$q  = new DBQuery;
	$q->addTable('companies','com');
	$q->addQuery('company_name');
	$q->addWhere('com.company_id = '.$company_id);
	$sql = $q->prepare();
	$q->clear();
	$company_name = db_loadResult( $sql );
	if (!$dept_id && $company_name === null) {
		$AppUI->setMsg( 'badCompany', UI_MSG_ERROR );
		$AppUI->redirect();
	}

	// collect all the departments in the company
	$depts = array( 0 => '' );
	if ($company_id) {
		$q  = new DBQuery;
		$q->addTable('departments','dep');
		$q->addQuery('dept_id, dept_name, dept_parent');
		$q->addWhere('dep.dept_company = '.$company_id);
		$q->addWhere('dep.dept_id != '.$dept_id);
		$depts = $q->loadArrayList();
		$depts['0']  = array( 0, '- '.$AppUI->_('Select Unit').' -', -1 );
	}

	// collect all the users for the department owner list
	$q  = new DBQuery;
	$q->addTable('users','u');
	$q->addTable('contacts','con');
	$q->addQuery('user_id');
	$q->addQuery('CONCAT_WS(", ",contact_last_name, contact_first_name)'); 
	$q->addOrder('contact_first_name');
	$q->addWhere('u.user_contact = con.contact_id');
	$q->addOrder('contact_last_name, contact_first_name');
	$owners = arrayMerge( array( '0'=>'' ), $q->loadHashList() );

// setup the title block
	$ttl = $company_id > 0 ? "Edit Department" : "Add Department";
	$titleBlock = new CTitleBlock( $ttl, 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	$titleBlock->show();

	$tpl->assign('dept_id', $dept_id);
	$tpl->assign('company_id', $company_id);
	$tpl->assign('company_name', $company_name);

	//$tpl->assign('drow', $drow);	

	$dept_has_parents = (count($depts)) ? true : false;
	$tpl->assign('depts', $depts);
	$tpl->assign('owners', $owners); 

	$tpl->displayAddEdit($drow);
}
?>
