<?php /* CONTACTS $Id$ */
// Add / Edit contact
$contact_id = isset( $HTTP_GET_VARS['contact_id'] ) ? $HTTP_GET_VARS['contact_id'] : 0;

// check permissions
$canEdit = !getDenyEdit( $m, $contact_id );

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

//Pull contact information
$sql = "SELECT * FROM contacts WHERE contacts.contact_id=$contact_id";

if (!db_loadHash( $sql, $contact ) && $contact_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid Contact ID', 'contacts.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=contacts", "contacts list" );
	$titleBlock->show();
} else {
// check only owner can edit
	if ($contact['contact_private'] && $contact['contact_owner'] != $AppUI->user_id
		 && $contact['contact_owner'] && $contact_id != 0) {
		$AppUI->redirect( "m=public&a=access_denied" );
	}

// setup the title block
	$ttl = $contact_id > 0 ? "Edit Contact" : "Add Contact";
	$titleBlock = new CTitleBlock( $ttl, 'monkeychat-48.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=contacts", "contacts list" );
	if ($canDelete) {
		$titleBlock->addCrumbRight(
			'<a href="javascript:delIt()">'
				. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
				. $AppUI->_('delete contact') . '</a>'
		);
	}
	$titleBlock->show();
?>
<script language="javascript">
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
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="changecontact" action="?m=contacts" method="post">
	<input type="hidden" name="dosql" value="do_contact_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="contact_project" value="0" />
	<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />

<tr>
	<td colspan="2">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
			<td>
				<input type="text" class="text" size=25 name="contact_first_name" value="<?php echo @$contact["contact_first_name"];?>" maxlength="50">
			</td>
		</tr>
		<tr>
			<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name');?>:</td>
			<td>
				<input type="text" class="text" size=25 name="contact_last_name" value="<?php echo @$contact["contact_last_name"];?>" maxlength="50" <?php if($contact_id==0){?> onBlur="orderByName('name')"<?php }?>>
				<a href="#" onClick="orderByName('name')">[<?php echo $AppUI->_('use in display');?>]</a>
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Display Name');?>: </td>
			<td>
				<input type="text" class="text" size=25 name="contact_order_by" value="<?php echo @$contact["contact_order_by"];?>" maxlength="50">
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Private Entry');?>: </td>
			<td>
				<input type="checkbox" value="1" name="contact_private" <?php echo (@$contact["contact_private"] ? 'checked' : '');?>>
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
				<input type="text" class="text" name="contact_company" value="<?php echo @$contact["contact_company"];?>" maxlength="100" size="25">
				<a href="#" onClick="orderByName('company')">[<?php echo $AppUI->_('use in display');?>]</a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Title');?>:</td>
			<td><input type="text" class="text" name="contact_title" value="<?php echo @$contact["contact_title"];?>" maxlength="50" size="25"></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type');?>:</td>
			<td><input type="text" class="text" name="contact_type" value="<?php echo @$contact["contact_type"];?>" maxlength="50" size="25"></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Address');?>1:</td>
			<td><input type="text" class="text" name="contact_address1" value="<?php echo @$contact["contact_address1"];?>" maxlength="30" size="25"></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
			<td><input type="text" class="text" name="contact_address2" value="<?php echo @$contact["contact_address2"];?>" maxlength="30" size="25"></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('City');?>:</td>
			<td><input type="text" class="text" name="contact_city" value="<?php echo @$contact["contact_city"];?>" maxlength="30" size="25"></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('State');?>:</td>
			<td><input type="text" class="text" name="contact_state" value="<?php echo @$contact["contact_state"];?>" maxlength="30" size="25"></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
			<td><input type="text" class="text" name="contact_zip" value="<?php echo @$contact["contact_zip"];?>" maxlength="11" size="25"></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Phone');?>:</td>
			<td>
				<input type="text" class="text" name="contact_phone" value="<?php echo @$contact["contact_phone"];?>" maxlength="30" size="25">
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
			<td>
				<input type="text" class="text" name="contact_phone2" value="<?php echo @$contact["contact_phone2"];?>" maxlength="30" size="25">
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Mobile Phone');?>:</td>
			<td>
				<input type="text" class="text" name="contact_mobile" value="<?php echo @$contact["contact_mobile"];?>" maxlength="30" size="25">
			</td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Email');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_email" value="<?php echo @$contact["contact_email"];?>" maxlength="50" size="25">
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Email');?>2:</td>
			<td>
				<input type="text" class="text" name="contact_email2" value="<?php echo @$contact["contact_email2"];?>" maxlength="50" size="25">
			</td>
		</tr>
		<tr>
			<td align="right">ICQ:</td>
			<td>
				<input type="text" class="text" name="contact_icq" value="<?php echo @$contact["contact_icq"];?>" maxlength="20" size="25">
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
			<td nowrap>
				<input type="text" class="text" name="contact_birthday" value="<?php echo @substr($contact["contact_birthday"], 0, 10);?>" maxlength="10" size=25>(<?php echo $AppUI->_('yyyy-mm-dd');?>)
			</td>
		</tr>
		</table>
	</td>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Contact Notes');?></strong><br />
		<textarea class="textarea" name="contact_notes" rows="20" cols="40"><?php echo @$contact["contact_notes"];?></textarea></td>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_('back');?>" class=button onClick="javascript:window.location='./index.php?m=contacts';">
	</td>
	<td align="right">
		<input type="button" value="<?php echo $AppUI->_('submit');?>" class=button onClick="submitIt()">
	</td>
</tr>
</form>
</table>
<?php } ?>