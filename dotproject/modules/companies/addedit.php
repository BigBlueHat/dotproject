<?php /* COMPANIES $Id$ */
// Add / Edit Company
$company_id = dPgetParam( $_GET, "company_id", 0 );

// check permissions for this company
$canEdit = !getDenyEdit( $m, $company_id );

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";
if (!db_loadHash( $sql, $company ) && $company_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Company ID', 'money.gif', $m, 'ID_HELP_COMP_EDIT' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
} else {

	// collect all the users for the company owner list
	$owners = array( '0'=>'' );
	$osql = "SELECT user_id,user_first_name,user_last_name FROM users";
	$orc = db_exec($osql);
	while ($orow = db_fetch_row( $orc )) {
		$owners[$orow[0]] = "$orow[1] $orow[2]";
	}

// setup the title block
	$ttl = $company_id > 0 ? "Edit Company" : "Add Company";
	$titleBlock = new CTitleBlock( $ttl, 'money.gif', $m, 'ID_HELP_COMP_EDIT' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	$titleBlock->show();
?>

<script language="javascript">
function submitIt() {
	var form = document.changeclient;
	if (form.company_name.value.length < 3) {
		alert( "Please enter a valid Company name" );
		form.company_name.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = "document.changeclient.company_primary_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}
</script>

<table cellspacing="1" cellpadding="1" border="0" width="100%" class="std">
<form name="changeclient" action="?m=companies&a=do_company_aed" method="post">
<input type="hidden" name="company_id" value="<?php echo $company_id;?>">

<tr>
	<td align="right"><?php echo $AppUI->_('Company Name');?>:</td>
	<td>
		<input type="text" class="text" name="company_name" value="<?php echo @$company["company_name"];?>" size=50 maxlength="255"> (<?php echo $AppUI->_('required');?>)
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
	<td>
		<input type="text" class="text" name="company_phone1" value="<?php echo @$company["company_phone1"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
	<td>
		<input type="text" class="text" name="company_phone2" value="<?php echo @$company["company_phone2"];?>" maxlength="50">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
	<td>
		<input type="text" class="text" name="company_fax" value="<?php echo @$company["company_fax"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<td colspan=2 align="center">
		<img src="images/shim.gif" width="50" height="1" /><?php echo $AppUI->_('Address');?><br />
		<hr width="500" align="center" size=1 />
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
	<td><input type="text" class="text" name="company_address1" value="<?php echo @$company["company_address1"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
	<td><input type="text" class="text" name="company_address2" value="<?php echo @$company["company_address2"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input type="text" class="text" name="company_city" value="<?php echo @$company["company_city"];?>" size=50 maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
	<td><input type="text" class="text" name="company_state" value="<?php echo @$company["company_state"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
	<td><input type="text" class="text" name="company_zip" value="<?php echo @$company["company_zip"];?>" maxlength="15"></td>
</tr>
<tr>
	<td align="right">
		URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo @$company["company_primary_url"];?>" name="company_primary_url" size=50 maxlength="255">
		<a href="#x" onClick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test');?>]</a>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Company Owner');?>:</td>
	<td>
<?php
	echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', @$company["company_owner"] );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Description');?>:</td>
	<td>&nbsp; </td>
</tr>
<tr>
	<td colspan="2" align="center">
		<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo @$company["company_description"];?></textarea>
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);"></td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()"></td>
</tr>
</form>
</table>

<p>&nbsp;</p>
<?php } ?>