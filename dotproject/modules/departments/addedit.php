<?php /* DEPARTMENTS $Id$ */
// Add / Edit Company
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions for this department
$denyEdit = getDenyEdit( $m, $dept_id );
if ($denyEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull data for this department
$sql = "
SELECT departments.*, company_name
FROM departments
LEFT JOIN companies ON company_id = dept_company
WHERE dept_id = $dept_id
";
if (!db_loadHash( $sql, $drow ) && $dept_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Department ID', 'users.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	if ($company_id) {
		$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	}
	$titleBlock->show();
} else {
	##echo $sql.db_error();##
	$company_id = $dept_id ? $drow['dept_company'] : $company_id;

	// check if valid company
	$sql = "SELECT company_name FROM companies WHERE company_id = $company_id";
	$company_name = db_loadResult( $sql );
	if (!$dept_id && $company_name === null) {
		$AppUI->setMsg( 'badCompany', UI_MSG_ERROR );
		$AppUI->redirect();
	}

	// collect all the departments in the company
	$depts = array( 0 => '' );
	if ($company_id) {
		$sql = "SELECT dept_id,dept_name,dept_parent FROM departments WHERE dept_company=$company_id AND dept_id";
		$depts = arrayMerge( array( '0'=>array( 0, '- Select Unit -', -1 ) ), db_loadHashList( $sql, 'dept_id' ));
	##echo $sql.db_error();##
	}

	// collect all the users for the department owner list
	$sql = "SELECT user_id,CONCAT(user_first_name,' ',user_last_name) FROM users ORDER BY user_first_name,user_last_name";
	$owners = arrayMerge( array( '0'=>'' ), db_loadHashList( $sql ) );

// setup the title block
	$ttl = $company_id > 0 ? "Edit Department" : "Add Department";
	$titleBlock = new CTitleBlock( $ttl, 'users.gif', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	$titleBlock->show();
?>
<script language="javascript">
function testURL( x ) {
	var test = "document.editFrm.dept_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}

function submitIt() {
	var form = document.editFrm;
	if (form.dept_name.value.length < 3) {
		alert( "Please enter a valid Company name" );
		form.dept_name.focus();
	} else {
		form.submit();
	}
}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=departments" method="post">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id;?>" />
	<input type="hidden" name="dept_company" value="<?php echo $company_id;?>" />

<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Department Company' );?>:</td>
	<td><strong><?php echo $company_name;?></strong></td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Department Name' );?>:</td>
	<td>
		<input type="text" class="text" name="dept_name" value="<?php echo @$drow["dept_name"];?>" size="50" maxlength="255" />
		<span class="smallNorm">(<?php echo $AppUI->_( 'required' );?>)</span>
	</td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Phone' );?>:</td>
	<td>
		<input type="text" class="text" name="dept_phone" value="<?php echo @$drow["dept_phone"];?>" maxlength="30" />
	</td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Fax' );?>:</td>
	<td>
		<input type="text" class="text" name="dept_fax" value="<?php echo @$drow["dept_fax"];?>" maxlength="30" />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Address' );?>1:</td>
	<td><input type="text" class="text" name="dept_address1" value="<?php echo @$drow["dept_address1"];?>" size="50" maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Address' );?>2:</td>
	<td><input type="text" class="text" name="dept_address2" value="<?php echo @$drow["dept_address2"];?>" size="50" maxlength="255"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'City' );?>:</td>
	<td><input type="text" class="text" name="dept_city" value="<?php echo @$drow["dept_city"];?>" size="50" maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'State' );?>:</td>
	<td><input type="text" class="text" name="dept_state" value="<?php echo @$drow["dept_state"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Zip' );?>:</td>
	<td><input type="text" class="text" name="dept_zip" value="<?php echo @$drow["dept_zip"];?>" maxlength="15"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'URL' );?><A name="x"></a></td>
	<td>
		<input type="text" class="text" value="<?php echo @$drow["dept_url"];?>" name="dept_url" size="50" maxlength="255">
		<a href="#x" onClick="testURL('dept_url')">[<?php echo $AppUI->_( 'test' );?>]</a>
	</td>
</tr>

<?php
if (count( $depts )) {
?>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Department Parent' );?>:</td>
	<td>
<?php
	echo arraySelectTree( $depts, 'dept_parent', 'class=text size=1', @$drow["dept_parent"] );
?>
	</td>
</tr>
<?php } else {
	echo '<input type="hidden" name="dept_parent" value="0">';
}
?>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Owner' );?>:</td>
	<td>
<?php
	echo arraySelect( $owners, 'dept_owner', 'size="1" class="text"', $drow["dept_owner"] );
?>
	</td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_( 'Description' );?>:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<textarea cols="70" rows="10" class="textarea" name="dept_desc"><?php echo @$drow["dept_desc"];?></textarea>
	</td>
</tr>

<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<?php } ?>