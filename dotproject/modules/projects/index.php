<?php

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

//Projects
$company_id = isset($HTTP_GET_VARS["company_id"]) ? $HTTP_GET_VARS["company_id"] :
	(isset($HTTP_POST_VARS["company_id"]) ? $HTTP_POST_VARS["company_id"] : $thisuser_company);

//Set up defaults
$orderby = isset($HTTP_GET_VARS["orderby"]) ? $HTTP_GET_VARS["orderby"] : 'project_end_date';
$active = isset($HTTP_GET_VARS["active"]) ? $HTTP_GET_VARS["active"] : 1;

// get read denied projects
$dsql = "
select project_id
from projects, permissions
where 
permission_user = $user_cookie
and permission_grant_on = 'projects' 
and permission_item = project_id
and permission_value = 0
";
//echo "<pre>$dsql</pre>";
$drc = mysql_query($dsql);
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
	$deny[] = $row[0];
}

// pull projects
$psql = "
select
	project_id, project_status, project_color_identifier, project_name,
	project_start_date, project_end_date, project_color_identifier,
	count(distinct t1.task_id) as total_tasks,
	count(distinct t2.task_id) as my_tasks,
	user_username,
	sum(t1.task_duration*t1.task_precent_complete)/sum(t1.task_duration) as project_precent_complete
from permissions,projects
left join users on projects.project_owner = users.user_id
left join tasks t1 on projects.project_id = t1.task_project
left join tasks t2 on projects.project_id = t2.task_project
	and t2.task_owner = $user_cookie
where project_active = $active"
.($company_id ? "\nand project_company = $company_id" : '')
."
	and permission_user = $user_cookie
	and permission_value <> 0 
	and (
		(permission_grant_on = 'all')
		or (permission_grant_on = 'projects' and permission_item = -1)
		or (permission_grant_on = 'projects' and permission_item = project_id)
		)
" . (count($deny) > 0 ? 'and project_id not in (' . implode( ',', $deny ) . ')' : '') . "
group by project_id
order by $orderby";
//echo "<pre>$psql</pre>";
$prc =mysql_query($psql);
$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);

?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickCompany">
<TR>
	<TD><img src="./images/icons/projects.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap><span class="title">Project Management</span></td>
	<TD align="right" width="100%">
		Company: <select name="company_id" onChange="document.pickCompany.submit()" style="font-size:8pt;font-family:verdana;">
		<option value="0" <?php if($company_id == 0) echo " selected" ;?> >all
<?php
	$csql = "select company_id,company_name from companies order by company_name";
	$crc = mysql_query($csql);

	while ($row = mysql_fetch_array( $crc )) {
		echo "<option value=" . $row["company_id"];
		if ($row["company_id"] == $company_id) {
			echo " selected";
		}
		echo ">" . $row["company_name"] ;
	}
?>
		</select><br>
		<?php include ("./includes/create_new_menu.php");?>
	</td>
</tr>
</form>
</TABLE>

<TABLE width="95%" border=0 cellpadding="1" cellspacing=0 bgcolor="#878676">
<TR height="20">
	<TD valign="top" width="100%" valign="bottom" bgcolor="#ffffff">
		<?php if($active <> 0){?>
		<span id=""> This page show you a list of <b>active</b> projects.</span>
		<?php }else{?>
		<span id=""> This page show you a list of <B>archived</b> projects.</span>
		<?php }?>
	</td>
	<TD>
		<TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0>
		<TR>
		<TD bgcolor="#f4efe3" align="center"><A href="<?php echo $PHP_SELF;?>?m=tasks">Tasks </A></td></table></TD>
			<TD bgcolor="#ffffff"><img src="images/shim.gif" width="10" height=5 border=0></td>
			<TD>
				<TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0><TR><TD <?php if($active == 0){?>bgcolor="#f4efe3"<?php }?> align="center"><A href="index.php?m=projects&active=1">Active</A></td></tr></table>
			</TD>
			<TD bgcolor="#ffffff"><img src="images/shim.gif" width="10" height=5 border=0></td>
			<TD>
				<TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0><TR><TD <?php if($active == 1){?>bgcolor="#f4efe3"<?php }?> align="center"><A href="index.php?m=projects&active=0">Archived</a></td></tr></table>
			</td>
		</tr>
</TABLE>

<TABLE width="95%" border=0 cellpadding="1" cellspacing=1 bgcolor="#878676">
	<TR><TD>

<TABLE width="100%" border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=1 height="400">
<TR bgcolor="#878676" height=20>
	<TD bgcolor="#f4efe3" align="right" class="smallNorm" width="65">&nbsp; sort by:&nbsp; </td>
	<TD nowrap>
		<A href="index.php?m=projects&orderby=project_name&company_id=<?php echo $company_id;?>"><font color="white">Project Name</font></a>
	</td>
	<TD nowrap>
		<A href="index.php?m=projects&orderby=user_username&company_id=<?php echo $company_id;?>"><font color="white">Owner</font></a>
	</td>
	<TD nowrap>
		<A href="index.php?m=projects&orderby=my_tasks%20desc&company_id=<?php echo $company_id;?>"><font color="white">My Tasks</font></a>
	</td>
	<TD nowrap>
		<A href="index.php?m=projects&orderby=total_tasks%20desc&company_id=<?php echo $company_id;?>"><font color="white">All Tasks</font></a>
	</td>
	<TD nowrap>
		<A href="index.php?m=projects&orderby=project_end_date&company_id=<?php echo $company_id;?>"><font color="white">Due Date:</font></a>
	</td>
</tr>

<?php
while ($row = mysql_fetch_array( $prc, MYSQL_ASSOC )) {
?>
<TR height=30>
	<TD bgcolor="#<?php echo $row["project_color_identifier"];?>" width="65" align="center" style="border: outset #eeeeee 2px;">
<?php
	echo '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
		. sprintf( "%.1f%%", $row["project_precent_complete"] )
		. '</font>';
	?></td>
	<TD><A href="./index.php?m=projects&a=view&project_id=<?php echo $row["project_id"];?>"><?php echo $row["project_name"];?></A></td>
	<TD nowrap><?php echo $row["user_username"];?></td>
	<TD nowrap><?php echo $row["my_tasks"];?></td>
	<TD nowrap><?php echo $row["total_tasks"];?></td>
	<TD nowrap><?php echo fromDate(substr($row["project_end_date"], 0, 10));?></td>
</tr>
<?php }?>
<TR>
	<TD colspan=6>&nbsp;</td>
</tr>
</Table>

</td></tr>
</TABLE>
