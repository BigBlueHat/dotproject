<?php
//Companies

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$dsql = "
select company_id
from companies, permissions
where 
permission_user = $user_cookie
and permission_grant_on = 'companies' 
and permission_item = company_id
and permission_value = 0
";
$drc = mysql_query($dsql);
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
	$deny[] = $row[0];
}

$csql = "
select company_id, company_name,
	count(distinct projects.project_id) as countp, count(distinct projects2.project_id) as inactive,
	user_first_name, user_last_name
from permissions, companies
left join projects on companies.company_id = projects.project_company and projects.project_active <> 0
left join users on companies.company_owner = users.user_id
left join projects as projects2 on companies.company_id = projects2.project_company and projects2.project_active = 0
where
	permission_user = $user_cookie
	and permission_value <> 0 
	and (
		(permission_grant_on = 'all')
		or (permission_grant_on = 'companies' and permission_item = -1)
		or (permission_grant_on = 'companies' and permission_item = company_id)
		)
" . (count($deny) > 0 ? 'and company_id not in (' . implode( ',', $deny ) . ')' : '') . "
group by company_id
order by company_name
";

$cos =mysql_query($csql);

$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc, MYSQL_NUM);
?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/money.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Clients and Companies</span></td>
		<TD align="right" width="100%">
		<?php if (!$denyEdit) { ?>
			<input type="button" class=button value="new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
		<?php } ?>
		</td>
	</tr>
</TABLE>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD valign="top"><span id=""><b>Welcome <?php echo $urow[0];?>.</b>  This page show you a list of current clients and their active projects.</span></td>
	</tr>
</TABLE>
<TABLE width="95%" border=0 bgcolor="#f4efe3" cellpadding="0" cellspacing=1 height="400">
	<TR>
		<TD valign="top" colspan=2>
			<TABLE width="100%">
				<TR bgcolor="#878676">
					<TD width="60" bgcolor="#f4efe3" align="right">&nbsp; sort by:&nbsp; </td>
					<TD class="mboxhdr"><A href="#"><font color="white">Company Name</font></a></td>
					<TD class="mboxhdr"><A href="#"><font color="white">Active Projects</font></a></td>
					<TD class="mboxhdr"><A href="#"><font color="white">Archived Projects</font></a></td>
				</tr>
<?php while($row = mysql_fetch_array( $cos, MYSQL_ASSOC )){?>
				<TR>
					<TD width="60" class="smallNorm" align="right" valign="bottom">&nbsp; </td>
					<TD><A href="./index.php?m=companies&a=view&company_id=<?php echo $row["company_id"];?>"><?php echo $row["company_name"];?></A></td>
					<TD><?php echo $row["countp"];?></td>
					<TD><?php echo @$row["inactive"];?></td>
				</tr>
<?php }?>
			</TABLE>
		</td>
	</tr>
</Table>
