<?php /* COMPANIES $Id$ */
// Add / Edit Company
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions for this company
$canEdit = !getDenyEdit( $m, $company_id );

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull data
$sql = "SELECT companies.*,users.user_first_name,users.user_last_name
	FROM companies
	LEFT JOIN users ON users.user_id = companies.company_owner
	WHERE companies.company_id = $company_id";
$res = db_exec( $sql );
$crow = db_fetch_assoc( $res );

// collect all the users for the company owner list
$owners = array( '0'=>'' );
$osql = "SELECT user_id,user_first_name,user_last_name FROM users";
$orc = db_exec($osql);
while ($orow = db_fetch_row( $orc )) {
	$owners[$orow[0]] = "$orow[1] $orow[2]";
}

$crumbs = array();
$crumbs["?m=companies"] = "company list";
$crumbs["?m=companies&a=view&company_id=$company_id"] = "view this company";
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

function delIt() {
	if (confirm( "Are you sure you would like\nto delete this company?" )) {
		var form = document.changeclient;
		form.del.value=1;
		form.submit();
	}
}
</script>

<table width="100%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0" /></td>
	<td nowrap="nowrap"><h1><?php echo $company_id ? $AppUI->_( 'Edit Company' ) : $AppUI->_( 'Add Company' );?></h1></td>
	<td align="right" width="100%">&nbsp;</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_COMP_EDIT' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr>
	<td width="50%" nowrap="nowrap"><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right">
	<?php if ($canDelete) {
		echo '<a href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="" border="0" />' . $AppUI->_( 'delete company' ) . '</a>';
	} ?>
	</td>
</tr>
</table>

<table cellspacing="1" cellpadding="1" border="0" width="100%" class="std">
<form name="changeclient" action="?m=companies" method="post">
<input type="hidden" name="dosql" value="company_aed">
<input name="del" type="hidden" value="0">
<input type="hidden" name="company_id" value="<?php echo $company_id;?>">

<tr>
	<td align="right"><?php echo $AppUI->_('Company Name');?>:</td>
	<td>
		<input type="text" class="text" name="company_name" value="<?php echo @$crow["company_name"];?>" size=50 maxlength="255"> (<?php echo $AppUI->_('required');?>)
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
	<td>
		<input type="text" class="text" name="company_phone1" value="<?php echo @$crow["company_phone1"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
	<td>
		<input type="text" class="text" name="company_phone2" value="<?php echo @$crow["company_phone2"];?>" maxlength="50">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
	<td>
		<input type="text" class="text" name="company_fax" value="<?php echo @$crow["company_fax"];?>" maxlength="30">
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
	<td><input type="text" class="text" name="company_address1" value="<?php echo @$crow["company_address1"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
	<td><input type="text" class="text" name="company_address2" value="<?php echo @$crow["company_address2"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input type="text" class="text" name="company_city" value="<?php echo @$crow["company_city"];?>" size=50 maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
	<td><input type="text" class="text" name="company_state" value="<?php echo @$crow["company_state"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
	<td><input type="text" class="text" name="company_zip" value="<?php echo @$crow["company_zip"];?>" maxlength="15"></td>
</tr>
<tr>
	<td align="right">
		URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo @$crow["company_primary_url"];?>" name="company_primary_url" size=50 maxlength="255">
		<a href="#x" onClick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test');?>]</a>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Company Owner');?>:</td>
	<td>
<?php
	echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', @$crow["company_owner"] );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Description');?>:</td>
	<td>&nbsp; </td>
</tr>
<tr>
	<td colspan="2" align="center">
		<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo @$crow["company_description"];?></textarea>
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);"></td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()"></td>
</tr>
</form>
</table>

<p>&nbsp;</p>
