<?php
//Companies

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

// get any companies denied from viewing
$deny = array();
$sql = "
SELECT company_id
FROM companies, permissions
WHERE permission_user = $user_cookie
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
WHERE permission_user = $user_cookie
	AND permission_value <> 0 
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'companies' and permission_item = -1)
		OR (permission_grant_on = 'companies' and permission_item = company_id)
		)
" . (count($deny) > 0 ? 'and company_id not in (' . implode( ',', $deny ) . ')' : '') . "
GROUP BY company_id
ORDER BY company_name
";

$rows = db_loadList( $sql );
?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<table width="98%" border=0 cellpadding=0 cellspacing=1>
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Clients and Companies</span></td>
	<td align="right" width="100%">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
	<?php } ?>
	</td>
</tr>
</table>

<table width="98%" border=0 cellpadding=0 cellspacing=1>
<tr>
	<td valign="top">
		<b>Welcome <?php echo $AppUI->user_first_name;?>.</b>  This page show you a list of current clients and their active projects.
	</td>
</tr>
</table>

<table width="98%" border=0 cellpadding=2 cellspacing=1 class="tbl">
<tr>
	<td nowrap width="60" align="right">&nbsp; sort by:&nbsp; </td>
	<th nowrap><a href="#"><font color="white">Company Name</font></a></th>
	<th nowrap><a href="#"><font color="white">Active Projects</font></a></th>
	<th nowrap><a href="#"><font color="white">Archived Projects</font></a></th>
</tr>
<?php foreach ($rows as $row){?>
<tr>
	<td>&nbsp;</td>
	<td><a href="./index.php?m=companies&a=view&company_id=<?php echo $row["company_id"];?>"><?php echo $row["company_name"];?></A></td>
	<td width=125 align=center nowrap><?php echo $row["countp"];?></td>
	<td width=125 align=center nowrap><?php echo @$row["inactive"];?></td>
</tr>
<?php }?>
</table>
