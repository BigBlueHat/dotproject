<?php
// Add / Edit Company
$company_id = isset($HTTP_GET_VARS['company_id']) ? $HTTP_GET_VARS['company_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m, $company_id );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
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

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Clients and Companies</span></td>
	<td align="right" width="100%">&nbsp;</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap>
		<a href="./index.php?m=companies">Companies List</a>
		<b>:</b> <a href="./index.php?m=companies&a=view&company_id=<?php echo $company_id;?>">View this Company</a>
	</td>
	<td width="50%" align="right">
		<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this comapny" border="0">delete company</a>
	</td>
</tr>
</table>

<table cellspacing="1" cellpadding="1" border="0" width="98%" class="std">
<form name="changeclient" action="?m=companies" method="post">
<input type="hidden" name="dosql" value="company_aed">
<input name="del" type="hidden" value="0">
<input type="hidden" name="company_id" value="<?php echo $company_id;?>">

<tr height="20">
	<th colspan="2">
		<b><i><?php if($company_id == 0){echo "Add";}else{echo "Edit";}?> Client Company </i></b>
	</th>
</tr>
<tr>
	<td align="right">Company Name:</td>
	<td><input type="text" class="text" name="company_name" value="<?php echo @$crow["company_name"];?>" size=50 maxlength="255"> (required)</td>
</tr>
<tr>
	<td align="right">Username:</td><td><input type="text" class="text" name="company_username" value="<?php echo @$crow["company_username"];?>" maxlength="30"> Not implemented</td>
</tr>
<tr>
	<td align="right">Password:</td><td><input type="text" class="text" name="company_password" value="<?php echo @$crow["company_password"];?>" maxlength="30"> Not implemented</td>
</tr>
<tr>
	<td align="right">Phone:</td><td><input type="text" class="text" name="company_phone1" value="<?php echo @$crow["company_phone1"];?>" maxlength="30"></td>
</tr>
<tr>
	<td align="right">Phone2:</td><td><input type="text" class="text" name="company_phone2" value="<?php echo @$crow["company_phone2"];?>" maxlength="50"> </td>
</tr>
<tr>
	<td align="right">Fax:</td><td><input type="text" class="text" name="company_fax" value="<?php echo @$crow["company_fax"];?>" maxlength="30"></td>
</tr>
<tr>
	<td colspan=2 align="center">
<img src="images/shim.gif" width="50" height="1">Address<BR>
<HR width="500" align="center" size=1></td>
</tr>
<tr>
	<td align="right">Address1:</td>
	<td><input type="text" class="text" name="company_address1" value="<?php echo @$crow["company_address1"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right">Address2:</td>
	<td><input type="text" class="text" name="company_address2" value="<?php echo @$crow["company_address2"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right">City:</td>
	<td><input type="text" class="text" name="company_city" value="<?php echo @$crow["company_city"];?>" size=50 maxlength="50"></td>
</tr>
<tr>
	<td align="right">State:</td>
	<td><input type="text" class="text" name="company_state" value="<?php echo @$crow["company_state"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right">Zip:</td>
	<td><input type="text" class="text" name="company_zip" value="<?php echo @$crow["company_zip"];?>" maxlength="15"></td>
</tr>
<tr>
	<td align="right">
		URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo @$crow["company_primary_url"];?>" name="company_primary_url" size=50 maxlength="255">
		<a href="#x" onClick="testURL('CompanyURLOne')">[test]</a>
	</td>
</tr>
<tr>
	<td align="right">Company Owner:</td>
	<td>
<?php
	echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', @$crow["company_owner"] );
?>
	</td>
</tr>

<tr>
	<td align="right">Description:</td>
	<td>&nbsp; </td>
</tr>
<tr>
	<td colspan="2" align="center">
		<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo @$crow["company_description"];?></textarea>
	</td>
</tr>
<tr>
	<td><input type="button" value="back" class="button" onClick="javascript:history.back(-1);"></td>
	<td align="right"><input type="button" value="submit" class="button" onClick="submitIt()"></td>
</tr>
</form>
</table>

<p>&nbsp;</p>
