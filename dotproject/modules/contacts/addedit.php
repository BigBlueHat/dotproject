<?php 
// Add / Edit contact
if(empty($contact_id))$contact_id = 0;

//Pull contact information
$csql = "Select * from contacts where contacts.contact_id = $contact_id";
$crc = mysql_query($csql);
$crow = mysql_fetch_array($crc);

//Pull Company Information
$sql = "select company_name, company_id from companies order by company_name";
$rc = mysql_query($sql);

?>


<SCRIPT language="javascript">
function submitIt(){
	var form = document.changecontact;
	if(form.contact_last_name.value.length < 1)
	{
		alert("Please enter a valid contact name");
		form.contact_last_name.focus();
	}
	if(form.contact_order_by.value.length < 1)
	{
		alert("Please enter a order by value");
		form.contact_order_by.focus();
	}


	else
	{
	form.submit();
	}
}


function delIt(){
var form = document.changecontact;
if(confirm("Are you sure you would like\nto delete this contact?"))
	{
	form.del.value="<?php echo $contact_id;?>";
	form.submit();
	}
}


function orderByName(x){
	var form = document.changecontact;
	if(x == "name"){
		form.contact_order_by.value = form.contact_last_name.value + ", " + form.contact_first_name.value;
	}
	else{
		form.contact_order_by.value = form.contact_company.value;
	}

}


</script>
<TABLE border=0 cellpadding="0" cellspacing=1 width="91%">
	<TR>
		<TD width="44"><img src="./images/icons/contacts.gif" alt="" border="0"></td>
		<TD width="100%"><span class="title">Contacts</span></td>
		<TD valign="bottom">&nbsp;</td>
	</tr>
	<TR>
		<TD colspan=2 nowrap>This page allows you to view and edit a contact's personal information.</td>
		<TD align="right" nowrap><A href="javascript:delIt()">delete contact <img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this contact" border="0"></a></td>
	</tr>
</TABLE>

<TABLE border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=0 width="91%">

<form name="changecontact" action="?m=contacts" method="post">
<input type="hidden" name="dosql" value="addeditdel_contact">
<input type="hidden" name="del" value="0">
<input type="hidden" name="contact_project" value="0">
<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>">
<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>">
<TR bgcolor="#878676" height="20" style="border: outset #eeeeee 2px;">
	<TD valign="top" colspan=2><b><i><?php if($contact_id == 0){echo "Add";}else{echo "Edit";}?> contact </i></b></td>
	<TD align="right" colspan=2>&nbsp;</td>
</tr>
<TR>
	<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
	<TD colspan=2></td>
	<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
	</tr>
<tr>
	<TD colspan=2>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="black" width="100%">
					<tr bgcolor="#f4efe3"><TD align="right" width="100">Contact Name: </td><TD><input type="text" class="text" size=25 name="contact_first_name" value="<?php echo @$crow["contact_first_name"];?>" maxlength="50"> <input type="text" class="text" size=25 name="contact_last_name" value="<?php echo @$crow["contact_last_name"];?>" maxlength="50" <?php if($contact_id==0){?> onBlur="orderByName('name')"<?php }?>><span class="smallNorm">(first - last)</span> <a href="#" onClick="orderByName('name')">[order by]</a></td></tr>
					<tr bgcolor="#f4efe3"><TD align="right" width="100">Order Name As: </td><TD><input type="text" class="text" size=25 name="contact_order_by" value="<?php echo @$crow["contact_order_by"];?>" maxlength="50"></td></tr>
			</table>
	</TD>
</TR>
	<TD valign="top">
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver">
					<tr bgcolor="#eeeeee"><TD align="right" width="100">Company:</td><TD nowrap><input type="text" class="text" name="contact_company" value="<?php echo @$crow["contact_company"];?>" maxlength="30" size=25> <a href="#" onClick="orderByName('company')">[order by]</a></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">Contact Title:</td><TD><input type="text"  name="contact_title" value="<?php echo @$crow["contact_title"];?>" maxlength="20" size=25></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">Contact Type:</td><TD><input type="text"  name="contact_type" value="<?php echo @$crow["contact_type"];?>" maxlength="20" size=25></td></tr>

				  <tr bgcolor="#eeeeee"><TD align="right" width="100">Address1:</td><TD><input type="text" class="text" name="contact_address1" value="<?php echo @$crow["contact_address1"];?>" maxlength="30" size=25></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">Address2:</td><TD><input type="text" class="text" name="contact_address2" value="<?php echo @$crow["contact_address2"];?>" maxlength="30" size=25></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">City:</td><TD><input type="text" class="text" name="contact_city" value="<?php echo @$crow["contact_city"];?>" maxlength="30" size=25></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">State:</td><TD><input type="text" class="text" name="contact_state" value="<?php echo @$crow["contact_state"];?>" maxlength="30" size=25></td></tr>
					<tr bgcolor="#eeeeee"><TD align="right">Zip:</td><TD><input type="text" class="text" name="contact_zip" value="<?php echo @$crow["contact_zip"];?>" maxlength="11" size=25></td></tr>
				<tr bgcolor="#eeeeee"><TD align="right" width="100">Phone:</td><TD><input type="text" class="text" name="contact_phone" value="<?php echo @$crow["contact_phone"];?>" maxlength="30" size=25></td></tr>
				<tr bgcolor="#eeeeee"><TD align="right">Phone2:</td><TD><input type="text" class="text" name="contact_phone2" value="<?php echo @$crow["contact_phone2"];?>" maxlength="30" size=25></td></tr>
				<tr bgcolor="#eeeeee"><TD align="right">Mobile Phone:</td><TD><input type="text" class="text" name="contact_mobile" value="<?php echo @$crow["contact_mobile"];?>" maxlength="30" size=25></td></tr>
				<tr bgcolor="#eeeeee"><TD align="right" width="100">Primary Email:</td><TD nowrap><input type="text" class="text" name="contact_email" value="<?php echo @$crow["contact_email"];?>" maxlength="50" size=25> </td></tr>
				<tr bgcolor="#eeeeee"><TD align="right">Email 2:</td><TD><input type="text" class="text" name="contact_email2" value="<?php echo @$crow["contact_email2"];?>" maxlength="50" size=25></td></tr>
				<tr bgcolor="#eeeeee"><TD align="right">ICQ:</td><TD><input type="text" class="text" name="contact_icq" value="<?php echo @$crow["contact_icq"];?>" maxlength="20" size=25> </td></tr>
				<tr bgcolor="#eeeeee"><TD align="right">Birthday:</td><TD nowrap><input type="text" class="text" name="contact_birthday" value="<?php echo @substr($crow["contact_birthday"], 0, 10);?>" maxlength="10" size=25>(yyyy-mm-dd) </td></tr>
			</table>
		
	</td>
	<TD valign="top">
		<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver">
			<tr bgcolor="#eeeeee">
				<TD><b>Contact Notes</b><br>
				<textarea class="textarea" name="contact_notes"><?php echo @$crow["contact_notes"];?></textarea></TD>
			</TR>
		</TABLE>
	</td>
</tr>

<TR>
<TD><input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=contacts';"></td>
<TD align="right"><input type="button" value="submit" class=button onClick="submitIt()"></td></tr>
</form>
</TABLE>


