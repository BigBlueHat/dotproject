<?php /* DEPARTMENTS $Id$ */
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;

// check permissions
$canRead = !getDenyRead( $m, $dept_id );
$canEdit = !getDenyEdit( $m, $dept_id );

if ($canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// pull data
$sql = "
SELECT departments.*,company_name, user_first_name, user_last_name
FROM departments, companies
LEFT JOIN users ON user_id = dept_owner
WHERE dept_id = $dept_id
	AND dept_company = company_id
";
if (!db_loadHash( $sql, $dept )) {
	$titleBlock = new CTitleBlock( 'Invalid Department ID', 'users.gif', $m, 'ID_HELP_DEPT_VIEW' );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
} else {
	$company_id = $dept['dept_company'];

	// setup the title block
	$titleBlock = new CTitleBlock( 'View Department', 'users.gif', $m, 'ID_HELP_DEPT_VIEW' );
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new department').'">', '',
			'<form action="?m=departments&a=addedit&company_id='.$company_id.'&dept_parent='.$dept_id.'" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=companies", "company list" );
	if ($canEdit) {
		$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
		$titleBlock->addCrumb( "?m=departments&a=addedit&dept_id=$dept_id", "edit this department" );

		if ($canDelete) {
			$titleBlock->addCrumbRight(
				'<a href="javascript:delIt()">'
					. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
					. $AppUI->_('delete department') . '</a>'
			);
		}
	}
	$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('departmentDelete');?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=departments&a=do_dept_aed" method="post">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id;?>" />
</form>

<tr valign="top">
	<td width="50%">
		<strong>Details</strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap>Company:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Department:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["dept_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Owner:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["user_first_name"].' '.@$dept["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Fax:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap>Address:</td>
			<td bgcolor="#ffffff"><?php
				echo @$dept["dept_address1"]
					.( ($dept["dept_address2"]) ? '<br />'.$dept["dept_address2"] : '' )
					.'<br />'.$dept["dept_city"]
					.'&nbsp;&nbsp;'.$dept["dept_state"]
					.'&nbsp;&nbsp;'.$dept["dept_zip"]
					;
			?></td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<strong>Description</strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff" width="100%"><?php echo str_replace( chr(10), "<br />", $dept["dept_desc"]);?>&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<?php
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=departments&a=view&dept_id=$dept_id", "{$AppUI->cfg['root_dir']}/modules/departments/", $tab );
}
?>
