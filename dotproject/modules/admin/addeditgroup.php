<?php
//add or edit a system user
if(empty($user_id))$user_id = 0;
$usql = "select * from users left join companies on user_company = companies.company_id where user_id = $user_id";
$prc  = mysql_query($usql);
$prow = mysql_fetch_array($prc);

$csql ="select company_name, company_id from companies order by company_name";
$crc = mysql_query($csql);

?>
<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeuser;
	if(form.user_username.value.length < 3)
	{
		alert("Please enter a valid user name");
		form.user_username.focus();
	}
	else if(form.user_password.value.length < 4)
	{
		alert("Please enter a valid password\n(greater than 4 chars).");
		form.user_password.focus();
	}
	else if(form.user_password.value !=  form.user_password2.value)
	{
		alert("Your passwords do not match).");
		form.user_password.focus();
	}
	else if(form.user_email.value.length < 4)
	{
		alert("Your email is invalid, please try again.");
		form.user_email.focus();
	}
	else if(form.user_birthday.value.length > 0)
	{
		dar =form.user_birthday.value.split("-");
		if(dar.length < 3)
		{
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM_DD)\nor leave the field blank");
			form.user_birthday.focus();
		}
		else if(isNaN(parseInt(dar[0])) || isNaN(parseInt(dar[1])) || isNaN(parseInt(dar[2])))
		{
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM_DD)\nor leave the field blank");
			form.user_birthday.focus();
		}
		else if(parseInt(dar[1]) < 1 || parseInt(dar[1]) > 12)
		{
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM_DD)\nor leave the field blank");
			form.user_birthday.focus();
		}
		else if(parseInt(dar[2]) < 1 || parseInt(dar[2]) > 31)
		{
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM_DD)\nor leave the field blank");
			form.user_birthday.focus();
		}
		else if(parseInt(dar[0]) < 1900 || parseInt(dar[0]) > 2020)
		{
			alert("Please enter a valid Birthday date\nformat: (YYYY-MM_DD)\nor leave the field blank");
			form.user_birthday.focus();
		}	
		else
		{
		form.submit();
		}
	}
	else
	{
	form.submit();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
}
</script>
<?php //------------------------Begin HTML -------------------------------?>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
		
		<TD nowrap><span class="title">
		<?php if(!$prow["user_id"]){ echo "Add User";}else{echo "View/Edit User";}?></span></td>
		<TD valign="top" align="right" width="100%">&nbsp;</td>
	</tr>
</TABLE>

<TABLE width="95%" border=0  bgcolor="#f4efe3" cellpadding="0" cellspacing=1 height="400">
<form name="changeuser" action="./index.php?m=admin&a=dosql" method="post">
	
	<input type="hidden" name="user_id" value="<?php echo intval($prow["user_id"]);?>">
	

<TR height="20"><TD valign="top" bgcolor="#878676" colspan=2><font color="white"><b><i>Adding new user to the system</font></i></b></td></tr>
<tr><TD align="right" width="230">Username:</td><TD><input type="text" class="text" name="user_username" value="<?php echo $prow["user_username"];?>" maxlength="255"> <span class="smallNorm">(required)</span></td></tr>
<tr><TD align="right">Password:</td><TD><input type="password" class="text" name="user_password" value="<?php echo $prow["user_password"];?>" maxlength="20"> </td></tr>
<tr><TD align="right">Password2:</td><TD><input type="password" class="text" name="user_password2" value="<?php echo $prow["user_password"];?>" maxlength="20"> </td></tr>
<tr><TD align="right">First Name:</td><TD><input type="text" class="text" name="user_first_name" value="<?php echo $prow["user_first_name"];?>" maxlength="50"> <input type="text" class="text" name="user_last_name" value="<?php echo $prow["user_last_name"];?>" maxlength="50"></td></tr>
<tr><TD align="right">Company:</td><TD>
<select name="user_company">
<option value=0 <?php if($prow["user_company"]==0)echo " selected ";?>>N/A
<?php while($crow = mysql_fetch_array($crc)){
echo '<option value=' . $crow["company_id"];
if($crow["company_id"] == $prow["user_company"]) echo " selected";
echo '>' . $crow["company_name"];

}?>
</select>
</TD></TR>
<tr><TD align="right">Email:</td><TD><input type="text" class="text" name="user_email" value="<?php echo $prow["user_email"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Phone:</td><TD><input type="text" class="text" name="user_phone" value="<?php echo $prow["user_phone"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Home Phone:</td><TD><input type="text" class="text" name="user_home_phone" value="<?php echo $prow["user_home_phone"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Mobile:</td><TD><input type="text" class="text" name="user_mobile" value="<?php echo $prow["user_mobile"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Address1:</td><TD><input type="text" class="text" name="user_address1" value="<?php echo $prow["user_address1"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Address2:</td><TD><input type="text" class="text" name="user_address2" value="<?php echo $prow["user_address2"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">City:</td><TD><input type="text" class="text" name="user_city" value="<?php echo $prow["user_city"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">State:</td><TD><input type="text" class="text" name="user_state" value="<?php echo $prow["user_state"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Zip:</td><TD><input type="text" class="text" name="user_zip" value="<?php echo $prow["user_zip"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Country:</td><TD><input type="text" class="text" name="user_country" value="<?php echo $prow["user_country"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">ICQ#:</td><TD><input type="text" class="text" name="user_icq" value="<?php echo $prow["user_icq"];?>" maxlength="50"> AOL Nick: <input type="text" class="text" name="user_aol" value="<?php echo $prow["user_aol"];?>" maxlength="50"> </td></tr>
<tr><TD align="right">Birthday:</td><TD><input type="text" class="text" name="user_birthday" value="<?php echo substr($prow["user_birthday"],0,10);?>" maxlength="50"> format(YYYY-MM-DD)</td></tr>
<tr><TD align="left">&nbsp; &nbsp; &nbsp;<input class=button  type=button value="back" onClick="javascript:history.back(-1);"></td><TD align="right"><input type=button value="submit" onClick="submitIt()" class=button>&nbsp; &nbsp; &nbsp;</td></tr>
</TABLE>
