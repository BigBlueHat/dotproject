<?php
//add or edit a system user
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$sql = "
SELECT users.*, 
	company_id, company_name, 
	dept_name
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
db_loadHash( $sql, $user );

// pull companies
$sql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
$companies = arrayMerge( array( 0 => '' ), db_loadHashList( $sql ) );

$crumbs = array();
$crumbs["?m=admin"] = "users list";
$crumbs["?m=admin&a=viewuser&user_id=$user_id"] = "view this user";
$crumbs["?m=admin&a=permissions&user_id=$user_id"] = "edit preferences";
?>
<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeuser;
	if (form.user_username.value.length < 3) {
		alert("<?php echo $AppUI->_('adminValidUserName');?>");
		form.user_username.focus();
	} else if (form.user_password.value.length < 4) {
		alert("<?php echo $AppUI->_('adminValidPassword');?>");
		form.user_password.focus();
	} else if (form.user_password.value !=  form.password_check.value) {
		alert("<?php echo $AppUI->_('adminPasswordsDiffer');?>");
		form.user_password.focus();
	} else if (form.user_email.value.length < 4) {
		alert("<?php echo $AppUI->_('adminInvalidEmail');?>");
		form.user_email.focus();
	} else if (form.user_birthday.value.length > 0) {
		dar = form.user_birthday.value.split("-");
		if (dar.length < 3) {
			alert("<?php echo $AppUI->_('adminInvalidBirthday');?>");
			form.user_birthday.focus();
		} else if (isNaN(parseInt(dar[0])) || isNaN(parseInt(dar[1])) || isNaN(parseInt(dar[2]))) {
			alert("<?php echo $AppUI->_('adminInvalidBirthday');?>");
			form.user_birthday.focus();
		} else if (parseInt(dar[1]) < 1 || parseInt(dar[1]) > 12) {
		    // There appears to be a bug with this part of the Birthday Validation
		    // Providing the single digit months (i.e. 1-9) in the MM format (01-09)
		    // causes the validation function to fail. Can someone please fix and
		    // remove this comment.  TIA (JRP 30 Aug 2002).
			alert("<?php echo $AppUI->_('adminInvalidMonth').' '.$AppUI->_('adminInvalidBirthday');?>");
			form.user_birthday.focus();
		} else if (parseInt(dar[2]) < 1 || parseInt(dar[2]) > 31) {
			alert("<?php echo $AppUI->_('adminInvalidDay').' '.$AppUI->_('adminInvalidBirthday');?>");
			form.user_birthday.focus();
		} else if(parseInt(dar[0]) < 1900 || parseInt(dar[0]) > 2020) {
			alert("<?php echo $AppUI->_('adminInvalidYear').' '.$AppUI->_('adminInvalidBirthday');?>");
			form.user_birthday.focus();
		} else {
			form.submit();
		}
	} else {
		form.submit();
	}
}

function popDept() {
	var f = document.changeuser;
	if (f.selectedIndex == 0) {
		alert( 'Please select a company first!' );
	} else {
		window.open('./selector.php?callback=setDept&table=departments&company_id='
			+ f.user_company.options[f.user_company.selectedIndex].value
			+ '&dept_id='+f.user_department.value,'dept','left=50,top=50,height=250,width=400,resizable')
	}
}

// Callback function for the generic selector
function setDept( key, val ) {
	var f = document.changeuser;
	if (val != '') {
		f.user_department.value = key;
		f.dept_name.value = val;
	} else {
		f.user_department.value = '0';
		f.dept_name.value = '';
	}
}

</script>
<?php //------------------------Begin HTML -------------------------------?>
<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap>
		<span class="title"><?php echo $user["user_id"] ? $AppUI->_( 'Edit User' ) : $AppUI->_( 'Add User' );?></span>
	</td>
	<td valign="top" align="right" width="100%">&nbsp;</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_USER_EDIT' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="0" cellspacing="1" height="400" class="std">

<form name="changeuser" action="./index.php?m=admin&a=dosql" method="post">
<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>">

<tr>
	<td align="right" width="230"><?php echo $AppUI->_('Login Name');?>:</td>
	<td>
	<?php if(@$user["user_username"]){?>
	<input type="hidden" class="text" name="user_username" value="<?php echo $user["user_username"];?>"><strong><?php echo $user["user_username"];?></strong>
	<?php }else{?>
		<input type="text" class="text" name="user_username" value="<?php echo $user["user_username"];?>" maxlength="50" size=40> 	 <span class="smallNorm">(<?php echo $AppUI->_('required');?>)</span>
	<?php }?></td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('User Type');?>:</td>
	<td>
<?php
	echo arraySelect( $utypes, 'user_type', 'class=text size=1', $user["user_type"] );
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Password');?>:</td>
	<td><input type="password" class="text" name="user_password" value="<?php echo $user["user_password"];?>" maxlength="20" size=40> </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Password');?>2:</td>
	<td><input type="password" class="text" name="password_check" value="<?php echo $user["user_password"];?>" maxlength="20" size=40> </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
	<td><input type="text" class="text" name="user_first_name" value="<?php echo $user["user_first_name"];?>" maxlength="50"> <input type="text" class="text" name="user_last_name" value="<?php echo $user["user_last_name"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Company');?>:</td>
	<td>
<?php
	echo arraySelect( $companies, 'user_company', 'class=text size=1', $user["user_company"] );
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Department');?>:</td>
	<td>
		<input type="hidden" name="user_department" value="<?php echo @$user["user_department"];?>">
		<input type="text" class="text" name="dept_name" value="<?php echo @$user["dept_name"];?>" size="40" disabled>
		<input type="button" class="button" value="select dept..." onclick="popDept()">
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email');?>:</td>
	<td><input type="text" class="text" name="user_email" value="<?php echo $user["user_email"];?>" maxlength="50" size=40> </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
	<td><input type="text" class="text" name="user_phone" value="<?php echo $user["user_phone"];?>" maxlength="50" size=40> </td>
	</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Home Phone');?>:</td>
	<td><input type="text" class="text" name="user_home_phone" value="<?php echo $user["user_home_phone"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Mobile');?>:</td>
	<td><input type="text" class="text" name="user_mobile" value="<?php echo $user["user_mobile"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
	<td><input type="text" class="text" name="user_address1" value="<?php echo $user["user_address1"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
	<td><input type="text" class="text" name="user_address2" value="<?php echo $user["user_address2"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input type="text" class="text" name="user_city" value="<?php echo $user["user_city"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
	<td><input type="text" class="text" name="user_state" value="<?php echo $user["user_state"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
	<td><input type="text" class="text" name="user_zip" value="<?php echo $user["user_zip"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Country');?>:</td>
	<td><input type="text" class="text" name="user_country" value="<?php echo $user["user_country"];?>" maxlength="50" size=40> </td>
</tr>
<tr>
	<td align="right">ICQ#:</td>
	<td><input type="text" class="text" name="user_icq" value="<?php echo $user["user_icq"];?>" maxlength="50"> AOL Nick: <input type="text" class="text" name="user_aol" value="<?php echo $user["user_aol"];?>" maxlength="50"> </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
	<td><input type="text" class="text" name="user_birthday" value="<?php if(intval($user["user_birthday"])!=0) { echo substr($user["user_birthday"],0,10);}?>" maxlength="50" size=40> format(YYYY-MM-DD)</td>
</tr>
<tr>
	<td align="right" valign=top><?php echo $AppUI->_('Email').' '.$AppUI->_('Signature');?>:</td>
	<td><textarea class="text" cols=50 name="user_signature" style="height: 50px"><?php echo @$user["user_signature"];?></textarea></td>
</tr>

<tr>
	<td align="left">
		<input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button">
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button">
	</td>
</tr>
</table>
