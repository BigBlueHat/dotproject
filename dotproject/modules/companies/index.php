<?php /* COMPANIES $Id$ */
$AppUI->savePlace();

if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';

// get any companies denied from viewing
$deny = array();
$sql = "
SELECT company_id
FROM companies, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'companies'
	AND permission_item = company_id
	AND permission_value = 0
";
$res = db_exec($sql);
while ($row = db_fetch_row( $res )) {
	$deny[] = $row[0];
}

$sql = "
SELECT company_id, company_name,
	count(distinct projects.project_id) as countp, count(distinct projects2.project_id) as inactive,
	user_first_name, user_last_name
FROM permissions, companies
LEFT JOIN projects ON companies.company_id = projects.project_company and projects.project_active <> 0
LEFT JOIN users ON companies.company_owner = users.user_id
LEFT JOIN projects AS projects2 ON companies.company_id = projects2.project_company AND projects2.project_active = 0
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'companies' and permission_item = -1)
		OR (permission_grant_on = 'companies' and permission_item = company_id)
		)
" . (count($deny) > 0 ? 'and company_id not in (' . implode( ',', $deny ) . ')' : '') . "
GROUP BY company_id
ORDER BY $orderby
";

$rows = db_loadList( $sql );
?>
<table width="100%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0" /></td>
	<td nowrap="nowrap"><h1><?php echo $AppUI->_('Clients & Companies');?></h1></td>
	<td align="right" width="100%">
	<?php if ($canEdit) { ?>
		<input type="button" class="button" value="<?php echo $AppUI->_('new company');?>" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
	<?php } ?>
	</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_COMP_IDX' );?></td>
</tr>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<td nowrap="nowrap" width="60" align="right">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=company_name" class="hdr"><?php echo $AppUI->_('Company Name');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=countp" class="hdr"><?php echo $AppUI->_('Active Projects');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=inactive" class="hdr"><?php echo $AppUI->_('Archived Projects');?></a>
	</th>
</tr>
<?php
$s = '';
foreach ($rows as $row) {
	$s .= $CR . '<tr>';
	$s .= $CR . '<td>&nbsp;</td>';
	$s .= $CR . '<td><a href="./index.php?m=companies&a=view&company_id=' . $row["company_id"] . '">' . $row["company_name"] .'</a></td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . $row["countp"] . '</td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . @$row["inactive"] . '</td>';
	$s .= $CR . '</tr>';
}
echo "$s\n";
?>
</table>
