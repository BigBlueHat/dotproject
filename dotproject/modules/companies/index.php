<?php /* COMPANIES $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// get any records denied from viewing
$obj = new CCompany();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// retrieve list of records
$sql = "
SELECT company_id, company_name, company_type, company_description,
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

// setup the title block
$titleBlock = new CTitleBlock( 'Companies', 'handshake.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new company').'">', '',
		'<form action="?m=companies&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();
?>
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
	<th nowrap="nowrap">
		<a href="?m=companies&orderby=company_type" class="hdr"><?php echo $AppUI->_('Type');?></a>
	</th>
</tr>
<?php
$s = '';
foreach ($rows as $row) {
	$s .= $CR . '<tr>';
	$s .= $CR . '<td>&nbsp;</td>';
	$s .= $CR . '<td><a href="./index.php?m=companies&a=view&company_id=' . $row["company_id"] . '" title="'.$row['company_description'].'">' . $row["company_name"] .'</a></td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . $row["countp"] . '</td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . @$row["inactive"] . '</td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . $AppUI->_($types[@$row["company_type"]]) . '</td>';
	$s .= $CR . '</tr>';
}
echo "$s\n";
?>
</table>
