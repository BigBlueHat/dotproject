<?php /* PUBLIC $Id$ */
$user_id = @$AppUI->user_id;

// check for a non-zero user id
if ($user_id) {
	$old_pwd = dPgetParam( $_POST, 'old_pwd', null );
	$new_pwd1 = dPgetParam( $_POST, 'new_pwd1', null );
	$new_pwd2 = dPgetParam( $_POST, 'new_pwd2', null );

	// has the change form been posted
	if ($old_pwd && $new_pwd1 && $new_pwd2 && $new_pwd1 == $new_pwd2 ) {
		// check that the old password matches
		$sql = "SELECT user_id FROM users WHERE user_password = password('$old_pwd') AND user_id=$user_id";
		if (db_loadResult( $sql ) == $user_id) {
			require_once( "{$AppUI->cfg['root_dir']}/classdefs/admin.php" );
			$user = new CUser();
			$user->user_id = $user_id;
			$user->user_password = $new_pwd1;

			if (($msg = $user->store())) {
				$AppUI->setMsg( $msg, UI_MSG_ERROR );
			} else {
				echo $AppUI->_('chgpwUpdated');
			}
		} else {
			echo $AppUI->_('chgpwWrongPW');
		}
	} else {
?>
<script language="javascript">
function submitIt() {
	var f = document.frmEdit;
	var msg = '';

	if (f.old_pwd.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('chgpwValidOld');?>";
		f.old_pwd.focus();
	}
	if (f.new_pwd1.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('chgpwValidNew');?>";
		f.new_pwd1.focus();
	}
	if (f.new_pwd1.value != f.new_pwd2.value) {
		msg += "\n<?php echo $AppUI->_('chgpwNoMatch');?>";
		f.new_pwd2.focus();
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}
</script>
<h1><?php echo $AppUI->_('Change User Password');?></h1>
<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std">
<form name="frmEdit" method="post" onsubmit="return false">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Current Password');?></td>
	<td><input type="password" name="old_pwd" class="text"></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('New Password');?></td>
	<td><input type="password" name="new_pwd1" class="text"></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Repeat New Password');?></td>
	<td><input type="password" name="new_pwd2" class="text"></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="right" nowrap="nowrap"><input type="button" value="<?php echo $AppUI->_('submit');?>" onclick="submitIt()" class="button"></td>
</tr>
<form>
</table>
<?php
	}
} else {
	echo $AppUI->_('chgpwLogin');
}
?>