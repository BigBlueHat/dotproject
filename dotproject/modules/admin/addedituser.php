<?php
//add or edit a system user
$user_id = isset($HTTP_GET_VARS['user_id']) ? $HTTP_GET_VARS['user_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$usql = "
SELECT users.*, 
	company_id, company_name, 
	dept_name
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
$prc  = mysql_query( $usql );
$prow = mysql_fetch_array( $prc, MYSQL_ASSOC );

$csql ="SELECT company_name, company_id FROM companies ORDER BY company_name";
$crc = mysql_query( $csql );
$companies = array( 0 => '' );
while($crow = mysql_fetch_array( $crc, MYSQL_ASSOC )) {
	$companies[$crow['company_id']] = $crow['company_name'];
}

?>
<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeuser;
	if (form.user_username.value.length < 3) {
		alert("Please enter a valid user name");
		form.user_username.focus();
	} else if (form.user_password.value.length < 4) {
		alert("Please enter a valid password\n(greater than 4 chars).");
		form.user_password.focus();
	} else if (form.user_password.value !=  form.user_password2.value) {
		alert("Your passwords do not match).");
		form.user_password.focus();
	} else if (form.user_email.value.length < 4) {
		alert("Your email is invalid, please try again.");
		form.user_email.focus();
	} else if (form.user_birthday.value.length > 0) {
		dar = form.user_birthday.value.split("-");
		if (dar.length < 3) {
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM-DD)\nor leave the field blank");
			form.user_birthday.focus();
		} else if (isNaN(parseInt(dar[0])) || isNaN(parseInt(dar[1])) || isNaN(parseInt(dar[2]))) {
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM-DD)\nor leave the field blank");
			form.user_birthday.focus();
		} else if (parseInt(dar[1]) < 1 || parseInt(dar[1]) > 12) {
		    // There appears to be a bug with this part of the Birthday Validation
		    // Providing the single digit months (i.e. 1-9) in the MM format (01-09)
		    // causes the validation function to fail. Can someone please fix and
		    // remove this comment.  TIA (JRP 30 Aug 2002).
			alert("The month you have provided is invalid (try M instead of MM).\n\nPlease enter a valid Birthday date\nformat: (YYYY-MM-DD)\nor leave the field blank");
			form.user_birthday.focus();
		} else if (parseInt(dar[2]) < 1 || parseInt(dar[2]) > 31) {
			alert("The day you have provided is invalid.\n\nPlease enter a valid Birthday date\nformat: (YYYY-MM-DD)\nor leave the field blank");
			form.user_birthday.focus();
		} else if(parseInt(dar[0]) < 1900 || parseInt(dar[0]) > 2020) {
			alert("The year you have provided is invalid.\n\nPlease enter a valid Birthday date\nformat: (YYYY-MM-DD)\nor leave the field blank");
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
		<span class="title">
		<?php if(!$prow["user_id"]){ echo "Add User";}else{echo "Edit User";}?>
		</span>
	</td>
	<td valign="top" align="right" width="100%">&nbsp;</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="90%">
<tr>
	<td width="50%" nowrap>
	<a href="./index.php?m=admin">Users List</a>
	<b>:</b> <a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>">View this User</a>
	</td>
</tr>
</table>

<table width="98%" border="0" cellpadding="0" cellspacing="1" height="400" class="std">

<form name="changeuser" action="./index.php?m=admin&a=dosql" method="post">
<input type="hidden" name="user_id" value="<?php echo intval($prow["user_id"]);?>">

<tr height="20">
	<th colspan="2">User Details</th>
</tr>
<tr>
	<td align="right" width="230">Username:</td>
	<td>
	<?php if(@$prow["user_username"]){?>
	<input type="hidden" class="text" name="user_username" value="<?php echo $prow["user_username"];?>"><strong><?php echo $prow["user_username"];?></strong>
	<?php }else{?>
		<input type="text" class="text" name="user_username" value="<?php echo $prow["user_username"];?>" maxlength="50" size=40> 	 <span class="smallNorm">(required)</span>
	<?php }?></td></tr>
<tr>
	<td align="right">User Type:</td>
	<td>
<?php
	echo arraySelect( $utypes, 'user_type', 'class=text size=1', $prow["user_type"] );
?>
	</td>
</tr>
<tr>
	<td align="right">Password:</td>
	<td><input type="password" class="text" name="user_password" value="<?php echo $prow["user_password"];?>" maxlength="20" size=40> </td>
</tr>
<tr>
	<td align="right">Password2:</td>
	<td><input type="password" class="text" name="user_password2" value="<?php echo $prow["user_password"];?>" maxlength="20" size=40> </td>
</tr>
<tr>
	<td align="right">First Name:</td>
	<td><input type="text" class="text" name="user_first_name" value="<?php echo $prow["user_first_name"];?>" maxlength="50"> <input type="text" class="text" name="user_last_name" value="<?php echo $prow["user_last_name"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right">Company:</td>
	<td>
<?php
	echo arraySelect( $companies, 'user_company', 'class=text size=1', $prow["user_company"] );
?>
	</td>
</tr>
<tr>
	<td align="right">Department:</td>
	<td>
		<input type="hidden" name="user_department" value="<?php echo @$prow["user_department"];?>">
		<input type="text" class="text" name="dept_name" value="<?php echo @$prow["dept_name"];?>" size="40" disabled>
		<input type="button" class="button" value="select dept..." onclick="popDept()">
	</td>
</tr>
<tr>
	<td align="right">Email:</td>
	<td><input type="text" class="text" name="user_email" value="<?php echo $prow["user_email"];?>" maxlength="50" size=40> </td>
</tr>
<tr>
	<td align="right">Phone:</td>
	<td><input type="text" class="text" name="user_phone" value="<?php echo $prow["user_phone"];?>" maxlength="50" size=40> </td>
	</tr>
<tr>
	<td align="right">Home Phone:</td>
	<td><input type="text" class="text" name="user_home_phone" value="<?php echo $prow["user_home_phone"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">Mobile:</td>
	<td><input type="text" class="text" name="user_mobile" value="<?php echo $prow["user_mobile"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">Address1:</td>
	<td><input type="text" class="text" name="user_address1" value="<?php echo $prow["user_address1"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">Address2:</td>
	<td><input type="text" class="text" name="user_address2" value="<?php echo $prow["user_address2"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">City:</td>
	<td><input type="text" class="text" name="user_city" value="<?php echo $prow["user_city"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">State:</td>
	<td><input type="text" class="text" name="user_state" value="<?php echo $prow["user_state"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">Zip:</td>
	<td><input type="text" class="text" name="user_zip" value="<?php echo $prow["user_zip"];?>" maxlength="50" size=40> </td></tr>
<tr>
	<td align="right">Country:</td>
	<td><input type="text" class="text" name="user_country" value="<?php echo $prow["user_country"];?>" maxlength="50" size=40> </td>
</tr>
<tr>
	<td align="right">Locales:</td>
	<td>
<?php
	echo arraySelect( $AppUI->locales, 'user_locale', 'class=text size=1', $prow["user_locale"] );
?>
	</td>
</tr>
<tr>
	<td align="right">ICQ#:</td>
	<td><input type="text" class="text" name="user_icq" value="<?php echo $prow["user_icq"];?>" maxlength="50"> AOL Nick: <input type="text" class="text" name="user_aol" value="<?php echo $prow["user_aol"];?>" maxlength="50"> </td>
</tr>
<tr>
	<td align="right">Birthday:</td>
	<td><input type="text" class="text" name="user_birthday" value="<?php if(intval($prow["user_birthday"])!=0) { echo substr($prow["user_birthday"],0,10);}?>" maxlength="50" size=40> format(YYYY-MM-DD)</td>
</tr>
<tr>
	<td align="right" valign=top>Email Signature:</td>
	<td><textarea class="text" cols=50 name="signature" style="height: 50px"><?php echo @$prow["signature"];?></textarea></td>
</tr>

<tr>
	<td align="left">&nbsp; &nbsp; &nbsp;<input class=button  type=button value="back" onClick="javascript:history.back(-1);"></td>
	<td align="right"><input type=button value="submit" onClick="submitIt()" class=button>&nbsp; &nbsp; &nbsp;</td>
</tr>
</table>
