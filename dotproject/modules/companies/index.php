<?
//Companies

//First pull perms
$psql = "select permission_value, permission_item 
	from 
		permissions, users 
	where 
		permission_user = user_id and 
		permission_value <> 0 and
		(permission_grant_on = 'companies' or permission_grant_on = 'all')
	order by permission_value";

$perm =mysql_query($psql);
$pullperm = mysql_fetch_array($perm, MYSQL_NUM);
$pullperm[0]=1; // JBF - force use of else clause below
if($pullperm[0] < 0)
	{
	$csql = "select company_id, company_name, count(project_id)  as countp, user_first_name, user_last_name 
		from companies 
		left join projects on companies.company_id = projects.project_company
		left join users on companies.company_owner = users.user_id 
		group by company_id
		order by company_name";
	}
else
	{
	$csql = "select company_id, company_name, count(projects.project_id) as countp, count(projects2.project_id) as inactive, user_first_name, user_last_name
	from permissions, companies
	left join projects on companies.company_id = projects.project_company and projects.project_active <> 0
	left join users on companies.company_owner = users.user_id 
	left join projects as projects2 on companies.company_id = projects2.project_company and projects2.project_active = 0
	where (permission_value <> 0 and (permission_grant_on = 'all' or (permission_grant_on = 'companies' and permission_item = company_id)) and (permission_user = $user_cookie))
	group by company_id order by company_name";
	}
$cos =mysql_query($csql);

$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);
?>

<SCRIPT language="javascript">
function delMe(x, y){
if(confirm("Are you sure you want\nto delete user " + y + "?"))
	{
	parent.mainframe.location="./users.cfm?a=del&user_id=" + x;
	} 

}

</script>
<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/money.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Clients and Companies</span></td>
		<TD align="right" width="100%"><input type="button" class=button value="add new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';"></td>
	</tr>
</TABLE>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD valign="top"><span id=""><b>Welcome <?echo $urow[0];?>.</b>  This page show you a list of current clients and their active projects.</span></td>
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
				
				
				
<? while($row = mysql_fetch_array($cos)){?>
				<TR>
				
				
					<TD width="60" class="smallNorm" align="right" valign="bottom">&nbsp; </td>
					<TD><A href="./index.php?m=companies&a=addedit&company_id=<?echo $row["company_id"];?>"><?echo $row["company_name"];?></A></td>
					<TD><?echo $row["countp"];?></td>
					<TD><?echo @$row["inactive"];?></td>
				</tr>
	<?}?>
</Table>
