<?php /* CONTACTS $Id$ */
$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );

// check permissions for this record
//$canEdit = !getDenyEdit( $m, $contact_id );
//if (!$canEdit) {
//	$AppUI->redirect( "m=public&a=access_denied" );
//}

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
$ttl = "View Contact";
$titleBlock = new CTitleBlock( $ttl, 'monkeychat-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts", "contacts list" );
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete( 'delete contact', $canDelete, $msg );
}
$titleBlock->show();
?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td colspan="2">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
			<td><?php echo @$row->contact_first_name;?></td>
		</tr>
		<tr>
			<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name');?>:</td>
			<td><?php echo @$row->contact_last_name;?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Display Name');?>: </td>
			<td><?php echo @$row->contact_order_by;?></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Company');?>:</td>
			<td nowrap><?php echo @$row->contact_company;?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Department');?>:</td>
			<td nowrap><?php echo @$row->contact_department;?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Title');?>:</td>
			<td><?php echo @$row->contact_title;?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type');?>:</td>
			<td><?php echo @$row->contact_type;?></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="100"><?php echo $AppUI->_('Address');?>:</td>
			<td>
                                <?php echo @$row->contact_address1;?><br />
			        <?php echo @$row->contact_address2;?><br />
			        <?php echo @$row->contact_city . ', ' . @$row->contact_state . ' ' . @$row->contact_zip;?>
                        </td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Phone');?>:</td>
			<td><?php echo @$row->contact_phone;?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
			<td><?php echo @$row->contact_phone2;?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Mobile Phone');?>:</td>
			<td><?php echo @$row->contact_mobile;?></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Email');?>:</td>
			<td nowrap><a href="mailto:<?php echo @$row->contact_email;?>"><?php echo @$row->contact_email;?></a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Email');?>2:</td>
			<td nowrap><a href="mailto:<?php echo @$row->contact_email2;?>"><?php echo @$row->contact_email2;?></a></td>
		</tr>
		<tr>
			<td align="right">ICQ:</td>
			<td><?php echo @$row->contact_icq;?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
			<td nowrap><?php echo @substr($row->contact_birthday, 0, 10);?></td>
		</tr>
		</table>
	</td>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Contact Notes');?></strong><br />
		<?php echo @$row->contact_notes;?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:window.location='./index.php?m=contacts';" />
	</td>
</tr>
</form>
</table>
