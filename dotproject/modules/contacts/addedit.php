<?php /* CONTACTS $Id$ */
$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );

// check permissions for this record
$canEdit = !getDenyEdit( $m, $contact_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the record data
$msg = '';
$row = new CContact();
$canDelete = $row->canDelete( $msg, $contact_id );

if (!$row->load( $contact_id ) && $contact_id > 0) {
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if ($row->contact_private && $row->contact_owner != $AppUI->user_id
	&& $row->contact_owner && $contact_id != 0) {
// check only owner can edit
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$ttl = $contact_id > 0 ? "Edit Contact" : "Add Contact";
$titleBlock = new CTitleBlock( $ttl, 'monkeychat-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts", "contacts list" );
if ($canEdit && $contact_id) {
	$titleBlock->addCrumbDelete( 'delete contact', $canDelete, $msg );
}
$titleBlock->show();
?>

<script language="javascript">
<?php
	if ( is_numeric($row->getCompanyID()) ){
		echo "window.company_id=" . $row->getCompanyID() . ";\n";
		echo "window.company_value='" . $row->contact_company . "';\n";
	}
?>

function submitIt() {
	var form = document.changecontact;
	if (form.contact_last_name.value.length < 1) {
		alert( "<?php echo $AppUI->_('contactsValidName');?>" );
		form.contact_last_name.focus();
	} else if (form.contact_order_by.value.length < 1) {
		alert( "<?php echo $AppUI->_('contactsOrderBy');?>" );
		form.contact_order_by.focus();
	} else {
		form.submit();
	}
}

function popDepartment() {
//        window.open('./index.php?m=public&a=selector&dialog=1&callback=setDepartment&table=departments&hide_company=1&company_id=' + window.company_id, 'department','left=50,top=50,height=250,width=400,resizable');
	window.open("./index.php?m=contacts&a=select_contact_company&dialog=1&table_name=departments&company_id="+window.company_id, "company", "left=50,top=50,height=250,width=400,resizable");
}

function setDepartment( key, val ){
	var f = document.changecontact;
 	if (val != '') {
    	f.contact_department.value = val;
    }
}

function popCompany() {
//        window.open('./index.php?m=public&a=selector&dialog=1&callback=setCompany&table=companies', 'company','left=50,top=50,height=250,width=400,resizable');
	window.open("./index.php?m=contacts&a=select_contact_company&dialog=1&table_name=companies", "company", "left=50,top=50,height=250,width=400,resizable");
}

function setCompany( key, val ){
	var f = document.changecontact;
 	if (val != '') {
    	f.contact_company.value = val;
    	if ( window.company_id != key ){
    		f.contact_department.value = "";
    	}
    	window.company_id = key;
    	window.company_value = val;
    }
}

function delIt(){
	var form = document.changecontact;
	if(confirm( "<?php echo $AppUI->_('contactsDelete');?>" )) {
		form.del.value = "<?php echo $contact_id;?>";
		form.submit();
	}
}

function orderByName( x ){
	var form = document.changecontact;
	if (x == "name") {
		form.contact_order_by.value = form.contact_last_name.value + ", " + form.contact_first_name.value;
	} else {
		form.contact_order_by.value = form.contact_company.value;
	}
}

function companyChange() {
	var f = document.changecontact;
	if ( f.contact_company.value != window.company_value ){
		f.contact_department.value = "";
	} 
}

</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="changecontact" action="?m=contacts" method="post">
	<input type="hidden" name="dosql" value="do_contact_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="contact_project" value="0" />
	<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />
	<input type="hidden" name="contact_owner" value="<?php echo $row->contact_owner ? $row->contact_owner : $AppUI->user_id;?>" />

<tr>
	<td colspan="2">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
			<td>
				<input type="text" class="text" size=25 name="contact_first_name" value="<?php echo @$row->contact_first_name;?>" maxlength="50" />
			</td>
		</tr>
		<tr>
			<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name');?>:</td>
			<td>
				<input type="text" class="text" size=25 name="contact_last_name" value="<?php echo @$row->contact_last_name;?>" maxlength="50" <?php if($contact_id==0){?> onBlur="orderByName('name')"<?php }?> />
				<a href="#" onClick="orderByName('name')">[<?php echo $AppUI->_('use in display');?>]</a>
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Display Name');?>: </td>
			<td>
				<input type="text" class="text" size=25 name="contact_order_by" value="<?php echo @$row->contact_order_by;?>" maxlength="50" />
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Private Entry');?>: </td>
			<td>
				<input type="checkbox" value="1" name="contact_private" <?php echo (@$row->contact_private ? 'checked' : '');?> />
			</td>
		</tr>
		</table>
	</td>
</tr>
	<td valign="top" width="50%">
		<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Company');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_company" value="<?php echo @$row->contact_company;?>" maxlength="100" size="25" onChange="companyChange()" />
				<input type="button" class="button" value="<?php echo $AppUI->_('select company...');?>..." onclick="popCompany()" />
				<a href="#" onClick="orderByName('company')">[<?php echo $AppUI->_('use in display');?>]</a>
				</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Department');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_department" value="<?php echo @$row->contact_department;?>" maxlength="100" size="25" />
				<input type="button" class="button" value="<?php echo $AppUI->_('select department...');?>" onclick="popDepartment()" />
				</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Title');?>:</td>
			<td><input type="text" class="text" name="contact_title" value="<?php echo @$row->contact_title;?>" maxlength="50" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type');?>:</td>
			<td><input type="text" class="text" name="contact_type" value="<?php echo @$row->contact_type;?>" maxlength="50" size="25" /></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Address');?>1:</td>
			<td><input type="text" class="text" name="contact_address1" value="<?php echo @$row->contact_address1;?>" maxlength="60" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
			<td><input type="text" class="text" name="contact_address2" value="<?php echo @$row->contact_address2;?>" maxlength="60" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('City');?>:</td>
			<td><input type="text" class="text" name="contact_city" value="<?php echo @$row->contact_city;?>" maxlength="30" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('State');?>:</td>
			<td><input type="text" class="text" name="contact_state" value="<?php echo @$row->contact_state;?>" maxlength="30" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
			<td><input type="text" class="text" name="contact_zip" value="<?php echo @$row->contact_zip;?>" maxlength="11" size="25" /></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Phone');?>:</td>
			<td>
				<input type="text" class="text" name="contact_phone" value="<?php echo @$row->contact_phone;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
			<td>
				<input type="text" class="text" name="contact_phone2" value="<?php echo @$row->contact_phone2;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Mobile Phone');?>:</td>
			<td>
				<input type="text" class="text" name="contact_mobile" value="<?php echo @$row->contact_mobile;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Email');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_email" value="<?php echo @$row->contact_email;?>" maxlength="255" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Email');?>2:</td>
			<td>
				<input type="text" class="text" name="contact_email2" value="<?php echo @$row->contact_email2;?>" maxlength="255" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right">ICQ:</td>
			<td>
				<input type="text" class="text" name="contact_icq" value="<?php echo @$row->contact_icq;?>" maxlength="20" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_birthday" value="<?php echo @substr($row->contact_birthday, 0, 10);?>" maxlength="10" size="25" />(<?php echo $AppUI->_('yyyy-mm-dd');?>)
			</td>
		</tr>
		</table>
	</td>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Contact Notes');?></strong><br />
		<textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo @$row->contact_notes;?></textarea></td>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:window.location='./index.php?m=contacts';" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
