<?
//Projects

//First pull perms
$psql = "	select permission_value, permission_item 
					from 
					permissions, users 
					where 
					permission_user = user_id and 
					permission_value <> 0 and
					(permission_grant_on = '" . $m . "' or permission_grant_on = 'all')
					order by permission_value";

$perm =mysql_query($psql);
$pullperm = mysql_fetch_array($perm);

//Set up defaults
if(!isset($active)){$active=1;}


if(empty($orderby))$orderby = "project_end_date";
$csql = "select 
project_id, 
project_status, 
project_color_identifier, 
project_name, 
count(distinct t1.task_id)  as countt,  
count(distinct t2.task_owner)  as countmt,  
project_start_date, 
project_end_date, 
project_color_identifier, 
user_username, 
avg(t1.task_precent_complete)  as project_precent_complete 
from projects 
		left join users on projects.project_owner = users.user_id 
		left join tasks t1 on projects.project_id = t1.task_project 
		left join tasks t2 on projects.project_id = t2.task_project
		and t2.task_owner = $user_cookie
		where project_active = $active 
		group by project_id
		order by $orderby";
//echo $csql;
$cos =mysql_query($csql);


$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);
?>



<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/projects.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">Project Management</span></td>
		<TD align="right" width="100%"><?include ("./includes/create_new_menu.php");?></td>
	</tr>
</TABLE>
<TABLE width="95%" border=0 cellpadding="1" cellspacing=0 bgcolor="#878676">
	<TR height="20">
		<TD valign="top" width="100%" valign="bottom" bgcolor="#ffffff">
			<?if($active <> 0){?>
			<span id=""> This page show you a list of <b>active</b> projects.</span>
			<?}else{?>
			<span id=""> This page show you a list of <B>archived</b> projects.</span>
			<?}?>
		</td>
		<TD><TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0><TR><TD bgcolor="#f4efe3" align="center"><A href="<?echo $PHP_SELF;?>?m=tasks">Tasks </A></td></table></TD>
		<TD bgcolor="#ffffff"><img src="images/shim.gif" width="10" height=5 border=0></td>
		<TD><TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0><TR><TD <?if($active == 0){?>bgcolor="#f4efe3"<?}?> align="center"><A href="index.php?m=projects&active=1">Active</A></td></table></TD>
		<TD bgcolor="#ffffff"><img src="images/shim.gif" width="10" height=5 border=0></td>
		<TD><TABLE width="100" height="20" border=0 cellpadding="0" cellspacing=0><TR><TD <?if($active == 1){?>bgcolor="#f4efe3"<?}?> align="center"><A href="index.php?m=projects&active=0">Archived</a></td></table></td>
	</tr>
</TABLE>
<TABLE width="95%" border=0 cellpadding="1" cellspacing=1 bgcolor="#878676">
	<TR><TD>

<TABLE width="100%" border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=1 height="400">
				<TR bgcolor="#878676" height=20>
					<TD bgcolor="#f4efe3" align="right" class="smallNorm" width="65">&nbsp; sort by:&nbsp; </td>
					<TD nowrap><A href="index.php?m=projects&orderby=project_name"><font color="white">Project Name</font></a></td>
					<TD nowrap><A href="index.php?m=projects&orderby=user_username"><font color="white">Owner</font></a></td>
					<TD nowrap><A href="index.php?m=projects&orderby=countmt%20desc"><font color="white">My Tasks</font></a></td>
					<TD nowrap><A href="index.php?m=projects&orderby=countt%20desc"><font color="white">All Tasks</font></a></td>
					<TD nowrap><A href="index.php?m=projects&orderby=project_end_date"><font color="white">Due Date:</font></a></td>
				</tr>
				
				
				
<? while($row = mysql_fetch_array($cos)){?>
				<TR height=30>
				
					<TD bgcolor="#<?echo $row["project_color_identifier"];?>" width="65" align="center" style="border: outset #eeeeee 2px;">
					<?
					$r = hexdec(substr($row["project_color_identifier"], 0, 2)); 
					$g = hexdec(substr($row["project_color_identifier"], 2, 2)); 
					$b = hexdec(substr($row["project_color_identifier"], 4, 2)); 
					
					if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128) 
					{
					echo "<font color='white'>";
					};
					?>
					
					
					<?echo @number_format($row["project_precent_complete"]);?>%</td>
					<TD><A href="./index.php?m=projects&a=view&project_id=<?echo $row["project_id"];?>"><?echo $row["project_name"];?></A></td>
					<TD nowrap><?echo $row["user_username"];?></td>
					<TD nowrap><?echo $row["countmt"];?></td>
					<TD nowrap><?echo $row["countt"];?></td>
					<TD nowrap><?echo fromDate(substr($row["project_end_date"], 0, 10));?></td>
				</tr>
	<?}?>
	<TR><TD colspan=6> &nbsp;</td></tr>
</Table>
</td></tr>
</TABLE>
