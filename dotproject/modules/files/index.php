<?php
##
## Files modules: index page
##

$project_id = isset($HTTP_POST_VARS['project_id']) ? $HTTP_POST_VARS['project_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// SETUP FOR PROJECT LIST BOX
// projects that are denied access
$dsql = "
select project_id
from projects, permissions
where
permission_user = $user_cookie
and permission_grant_on = 'projects'
and permission_item = project_id
and permission_value = 0
";
$drc = mysql_query($dsql);
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
	$deny[] = $row[0];
}

$psql = "
select project_id,project_name
from projects, permissions
where
	permission_user = $user_cookie
	and permission_value <> 0
	and (
		(permission_grant_on = 'all')
		or (permission_grant_on = 'projects' and permission_item = -1)
		or (permission_grant_on = 'projects' and permission_item = project_id)
		)
" . (count($deny) > 0 ? 'and project_id not in (' . implode( ',', $deny ) . ')' : '') . "
order by project_name";
$prc = mysql_query($psql);

// SETUP FOR FILE LIST
$fsql = "
SELECT files.*, project_name, project_color_identifier, project_active, 
	user_first_name, user_last_name
FROM files, permissions
LEFT JOIN projects ON file_project = project_id
LEFT JOIN users ON file_owner = user_id
WHERE
	permission_user = $user_cookie
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
" . (count($deny) > 0 ? 'AND project_id NOT IN (' . implode( ',', $deny ) . ')' : '') . "
".($project_id ? "AND file_project = $project_id" : '')."
ORDER BY project_id , file_name
";

$frc =mysql_query($fsql);
echo mysql_error();

$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);
?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD><img src="./images/icons/folder.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap><span class="title">File Management</span></td>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickProject">
	<TD align="right" width="100%" nowrap>
		Project: <select name="project_id" onChange="document.pickProject.submit()" style="font-size:8pt;font-family:verdana;">
		<option value="0" <?php if($project_id == 0)echo " selected" ;?> >all
	<?php
	while ( $row = mysql_fetch_array( $prc ) ) {
		echo "<option value=" . $row["project_id"];
		if ($row["project_id"] == $project_id) {
			echo " selected";
		}
		echo ">" . $row["project_name"] ;
	}?>
		</select>
	</td>
</form>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type=hidden name=dosql value=searchfiles>
	<TD width="100%" align="right">
		<input class=button type=text name=s maxlength=30 size=20 value="Not implemented" disabled>
	</TD>
	<TD><img src="./images/shim.gif" width=5 height=5></td>
	<TD><input class=button type="submit" value="search" disabled></td>
	<TD><img src="./images/shim.gif" width=5 height=5></td>
</form>
	<TD align="right">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="add new file" onClick="javascript:window.location='./index.php?m=files&a=addedit';">
	<?php } ?>
	</td>
</tr>
</TABLE>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
<TR style="border: outset #eeeeee 2px;">
	<TD nowrap class="mboxhdr"></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Name</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Owner</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Date</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Type</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Size:</font></a></td>
</tr>
<?php
$fp=0;
while ($row = mysql_fetch_array( $frc )) {
	if ($fp != $row["file_project"]) {
?>
<TR bgcolor="#f4efe3">
	<TD colspan="6" bgcolor="#<?php echo $row["project_color_identifier"];?>" style="border: outset 2px #eeeeee">
<?php
	echo '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
		. $row["project_name"] . '</font>';
	?></td>
</TR>
<?php
	}
	$fp = $row["file_project"];
?>
<TR bgcolor="#f4efe3">
	<TD nowrap>
	<?php if (!$denyEdit) { ?>
		<A href="./index.php?m=files&a=addedit&file_id=<?php echo $row["file_id"];?>"><img src="./images/icons/pencil.gif" alt="edit file" border="0" width=12 height=12></a>
	<?php } ?>
	</td>
	<TD nowrap><A href="./fileviewer.php?file_id=<?php echo $row["file_id"];?>"><?php echo $row["file_name"];?></a></td>
	<TD nowrap><?php echo $row["user_first_name"].' '.$row["user_last_name"];?></td>
	<TD nowrap><?php echo $row["file_date"];?></td>
	<TD nowrap><?php echo $row["file_type"];?></td>
	<TD nowrap><?php echo intval($row["file_size"] / 1024);?>k</td>
</tr>
<?php }?>
</Table>
