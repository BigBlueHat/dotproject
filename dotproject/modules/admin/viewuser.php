<?php /* ADMIN $Id$ */
$AppUI->savePlace();

$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;

// pull data
$sql = "
SELECT users.*, 
	company_id, company_name, 
	dept_name, dept_id
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
if (!db_loadHash( $sql, $user )) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=admin", "users list" );
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View User', 'helix-setup-user.png', $m, "$m.$a" );
	if ($canRead) {
	  $titleBlock->addCrumb( "?m=admin", "users list" );
	}
	if ($canEdit || $user_id == $AppUI->user_id) {
	      $titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=$user_id", "edit this user" );
	      $titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );
	      $titleBlock->addCrumbRight(
			'<a href="#" onclick="popChgPwd();return false">' . $AppUI->_('change password') . '</a>'
	      );
	}
	$titleBlock->show();
?>
<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit || $user_id == $AppUI->user_id) {
?>
function popChgPwd() {
	window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $user['user_id']; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );
}
<?php } ?>
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Login Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_username"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('User Type');?>:</td>
			<td class="hilite" width="100%"><?php echo $utypes[$user["user_type"]];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Real Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_first_name"].' '.$user["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=companies&a=view&company_id=<?php echo @$user["company_id"];?>"><?php echo @$user["company_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Department');?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=departments&a=view&dept_id=<?php echo @$user["dept_id"];?>"><?php echo $user["dept_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Home Phone');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_home_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Mobile');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_mobile"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite" width="100%"><?php
				echo @$user["user_address1"]
					.( ($user["user_address2"]) ? '<br />'.$user["user_address2"] : '' )
					.'<br />'.$user["user_city"]
					.'&nbsp;&nbsp;'.$user["user_state"]
					.'&nbsp;&nbsp;'.$user["user_zip"]
					.'<br />'.$user["user_country"]
					;
			?></td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Birthday');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_birthday"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>ICQ#:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_icq"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>AOL Nick:</td>
			<td class="hilite" width="100%"><a href="aim:<?php echo @$user["user_aol"];?>"><?php echo @$user["user_aol"];?></a></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Email');?>:</td>
			<td class="hilite" width="100%"><?php echo '<a href="mailto:'.@$user["user_email"].'">'.@$user["user_email"].'</a>';?></td>
		</tr>
		<tr>
			<td colspan="2"><strong><?php echo $AppUI->_('Signature');?>:</strong></td>
		</tr>
		<tr>
			<td class="hilite" width="100%" colspan="2">
				<?php echo str_replace( chr(10), "<br />", $user["user_signature"]);?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<?php	
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=admin&a=viewuser&user_id=$user_id", "{$dPconfig['root_dir']}/modules/admin/", $tab );
	$tabBox->add( 'vw_usr_proj', 'Owned Projects' );
	$tabBox->add( 'vw_usr_perms', 'Permissions' );
	$tabBox->add( 'vw_usr_log', 'User Log');
	$tabBox->add( 'vw_usr_roles', 'Roles' );
	$tabBox->show();
}
?>
