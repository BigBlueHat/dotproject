<?php
// Add / Edit Company

if(empty($company_id))$company_id = 0;
$csql = "Select companies.*,users.user_first_name,users.user_last_name
	from companies
	left join users on users.user_id = companies.company_owner
	where companies.company_id = $company_id";
$crc = mysql_query($csql);
$crow = mysql_fetch_array($crc);

// collect all the users for the company owner list
$owners = array();
$osql = "select user_id,user_first_name,user_last_name from users";
$orc = mysql_query($osql);
while ($orow = mysql_fetch_array($orc)) {
	$owners[] = $orow;
}
?>

<SCRIPT language="javascript">
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

<TABLE width="90%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/money.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Clients and Companies</span></td>
		<TD align="right" width="100%">&nbsp;</td>
	</tr>
	<TR>
		<TD valign="top" align="right" width="100%" colspan=3>
			<?php if($company_id != 0){?>
			<TABLE cellpadding=1 cellpadding=1 border=0 bgcolor="#dddddd">
				<TR>
					<TD bgcolor="white">
						<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this comapny" border="0">delete company</a>
					</td>
			</tr>
			</table>
			<?php }?>
		</td>
	</tr>
</TABLE>

<TABLE width="90%" border=0 bgcolor="#f4efe3" cellpadding="0" cellspacing=1 height="400">
<form name="changeclient" action="?m=companies" method="post">
<input type="hidden" name="dosql" value="company_aed">
<input name="del" type="hidden" value="0">
<input type="hidden" name="company_id" value="<?php echo $company_id;?>">

<TR height="20"><TD bgcolor="#878676" valign="top" colspan=2><b><i><?php if($company_id == 0){echo "Add";}else{echo "Edit";}?> Client Company </i></b></td></tr>
<tr><TD align="right">Company Name:</td><TD><input type="text" class="text" name="company_name" value="<?php echo @$crow["company_name"];?>" size=50 maxlength="255"> <span class="smallNorm">(required)</span></td></tr>
<tr><TD align="right">Username:</td><TD><input type="text" class="text" name="company_username" value="<?php echo @$crow["company_username"];?>" maxlength="30"> Not implemented</td></tr>
<tr><TD align="right">Password:</td><TD><input type="text" class="text" name="company_password" value="<?php echo @$crow["company_password"];?>" maxlength="30"> Not implemented</td></tr>
<tr><TD align="right">Phone:</td><TD><input type="text" class="text" name="company_phone1" value="<?php echo @$crow["company_phone1"];?>" maxlength="30"></td></tr>
<tr><TD align="right">Phone2:</td><TD><input type="text" class="text" name="company_phone2" value="<?php echo @$crow["company_phone2"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Fax:</td><TD><input type="text" class="text" name="company_fax" value="<?php echo @$crow["company_fax"];?>" maxlength="30"></td></tr>
<TR><TD colspan=2 align="center">
<img src="images/shim.gif" width="50" height="1">Address<BR>
<HR width="500" align="center" size=1></td></tr>
<tr><TD align="right">Address1:</td><TD><input type="text" class="text" name="company_address1" value="<?php echo @$crow["company_address1"];?>" size=50 maxlength="255"></td></tr>
<tr><TD align="right">Address2:</td><TD><input type="text" class="text" name="company_address2" value="<?php echo @$crow["company_address2"];?>" size=50 maxlength="255"></td></tr>
<tr><TD align="right">City:</td><TD><input type="text" class="text" name="company_city" value="<?php echo @$crow["company_city"];?>" size=50 maxlength="50"></td></tr>
<tr><TD align="right">State:</td><TD><input type="text" class="text" name="company_state" value="<?php echo @$crow["company_state"];?>" maxlength="50"></td></tr>
<tr><TD align="right">Zip:</td><TD><input type="text" class="text" name="company_zip" value="<?php echo @$crow["company_zip"];?>" maxlength="15"></td></tr>
<tr><TD align="right">URL http://<A name="x"></a></td><TD><input type="text" class="text" value="<?php echo @$crow["company_primary_url"];?>" name="company_primary_url" size=50 maxlength="255"> <a href="#x" onClick="testURL('CompanyURLOne')"><span class="smallNorm">[test]</span></a></td></tr>

<tr>
	<TD align="right">Company Owner:</td>
	<TD>
<?
$n = count( $owners );
echo '<select class=text name="company_owner" size=1>';
for ($i=0; $i < $n; $i++) {
	echo '<option value='.$owners[$i]['user_id'];
	if ($owners[$i]['user_id'] == $user_cookie) {
		echo ' selected';
	}
	echo '>'.$owners[$i]['user_first_name'].' '.$owners[$i]['user_last_name'];
}
echo '</select>';
?>
	</td>
</tr>

<TR><TD align="right">Description:</td><td>&nbsp; </td></tr>
<TR><TD colspan=2 align="center">
<textarea cols="70" rows="10" class="textareaclass" name="company_description">
<?php echo @$crow["company_description"];?>
</textarea>
</td></tr>

<TR><TD><input type="button" value="back" class="button" onClick="javascript:history.back(-1);"></td><TD align="right"><input type="button" value="submit" class="button" onClick="submitIt()"></td></tr>
</form>
</TABLE>
&nbsp;<br>&nbsp;<br>&nbsp;

</body>
</html>
