<?php
$task_id = isset( $HTTP_GET_VARS['task_id'] ) ? $HTTP_GET_VARS['task_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$tsql = "
SELECT tasks.*,
	project_name, project_color_identifier,
	u1.user_username as username
FROM tasks
LEFT JOIN users u1 ON u1.user_id = task_owner
LEFT JOIN projects ON project_id = task_project
WHERE task_id = $task_id
";

$trc = mysql_query( $tsql );
echo mysql_error();
$trow = mysql_fetch_array( $trc, MYSQL_ASSOC );

// Pull the task comments
$csql = "
SELECT user_username,
	comment_title, comment_body, comment_date
FROM tasks, task_comments
LEFT JOIN users ON users.user_id = task_comments.comment_user
WHERE task_id = $task_id
	AND comment_task = task_id
ORDER BY comment_date
";

$crc = mysql_query( $csql );
echo mysql_error();

//Pull users on this task
$usql = "
SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
FROM users u, user_tasks t
WHERE t.task_id =$task_id AND
	t.user_id = u.user_id
";

$usql = mysql_query( $usql );
echo mysql_error();
echo mysql_error();

//Pull files on this task
$fsql = "
SELECT file_id, file_name, file_size,file_type
FROM files
WHERE file_task = $task_id
	AND file_task <> 0
";

$fsql = mysql_query($fsql);
echo mysql_error();
?>

<script language="JavaScript">
function updateTask() {
	var form = document.update;
	if (form.comments.value.length < 1) {
		alert( "Please enter a worthwile commment" );
		form.comments.focus();
	} else if (isNaN( parseInt( form.complete.value+0 ) )) {
		alert( "The percent complete must be a integer" );
		form.complete.focus();
	} else if(form.complete.value  < 0 || form.complete.value > 100) {
		alert( "The percent complete must be a value between 0 and 100" );
		form.complete.focus();
	} else {
		form.submit();
	}
}
</script>

<table cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Manage Task</span></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%">
<tr>
	<td nowrap>
		<A href="./index.php?m=projects&a=view&project_id=<?php echo $trow["task_project"];?>"><?php echo $trow["project_name"];?></A>
		<b> : </b><A href="./index.php?m=tasks">Task List</a>
<?php if (!$denyEdit) { ?>
		<b>:</b> <A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $trow["task_id"];?>">Edit this task</a>
<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td style="border: outset #eeeeee 1px;background-color:<?php echo $trow["project_color_identifier"];?>" colspan="2">
		<font color="<?php echo bestColor( $trow["project_color_identifier"] ); ?>">
			<b>TASK: <?php echo @$trow["task_name"];?></b>
		</font>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<table width="100%" cellspacing=1>
		<tr>
			<td nowrap colspan=2><b>Details</b></td>
		</tr>
		<tr>
			<td align=right nowrap>Project:</td>
			<td bgcolor="#ffffff"><?php echo @$trow["project_name"];?></td>
		</tr>
		<tr>
			<td align=right nowrap>Task:</td>
			<td bgcolor="#ffffff"><?php echo @$trow["task_name"];?></td>
		</tr>
		<tr>
			<td align=right nowrap>Owner:</td>
			<td bgcolor="#ffffff"> <?php echo @$trow["username"];?></td>
		</tr>				<tr>
			<td align=right nowrap>Priority:</td>
			<td bgcolor="#ffffff">
		<?php
			if ($trow["task_priority"] == 0) {
				echo "Normal";
			} else if ($trow["task_priority"] < 0){
				echo "Low";
			} else {
				echo "High";
			}
		?>
			</td>
		</tr>
		<tr>
			<td align=right nowrap>Web Address:</td>
			<td bgcolor="#ffffff" width="300"><?php echo @$trow["task_related_url"];?></td>
		</tr>
		<tr>
			<td align=right nowrap>Milestone:</td>
			<td bgcolor="#ffffff" width="300"><?php if($trow["task_milestone"]){echo "Yes";}else{echo "No";}?></td>
		</tr>
		<tr>
			<td align=right nowrap>Percent Complete:</td>
			<td bgcolor="#ffffff" width="300"><?php echo @$trow["task_precent_complete"];?>%</td>
		</tr>
		<tr>
			<td align=right nowrap>Time worked:</td>
			<td bgcolor="#ffffff" width="300"><?php echo @$trow["task_hours_worked"];?></td>
		</tr>
		<tr>
			<td nowrap colspan=2><b>Dates and Targets</b></td>
		</tr>
		<tr>
			<td align=right nowrap>Start Date:</td>
			<td bgcolor="#ffffff" width="300"><?php echo fromDate(substr($trow["task_start_date"], 0, 10));?></td>
		</tr>
		<tr>
			<td align=right nowrap>End Date:</td>
			<td bgcolor="#ffffff" width="300"><?php if(intval($trow["task_end_date"]) == 0){echo "n/a";}else{echo fromDate(substr($trow["task_end_date"], 0, 10));}?></td>
		</tr>
		<tr>
			<td align=right nowrap>Expected Duration:</td>
			<td bgcolor="#ffffff" width="300"><?php
			$dur = returnDur( $trow["task_duration"] );
			echo $dur["value"] . " " . $dur["type"];
			?></td>
		</tr>
		<tr>
			<td align=right nowrap>Target Budget:</td>
			<td bgcolor="#ffffff" width="300"><?php echo $trow["task_target_budget"];?></td>
		</tr>
		<tr>
			<td nowrap colspan=2><b>full description</b></td>
		</tr>
		<tr>
			<td valign=top height=75 colspan=2 bgcolor="#ffffff">
				<?php $newstr = str_replace( chr(10), "<BR>", $trow["task_description"]);echo $newstr;?>
			</td>
		</tr>

		</table>
	</td>

	<td width="50%" align="right">
		<table cellspacing=0 cellpadding=2 width="100%">
		<form name="update" action="./index.php?m=tasks&a=view&task_id=<?php echo $task_id;?>" method="post">
		<input type="hidden" value="<?php echo uniqid("");?>" name="uniqueid">
		<input type="hidden" value="updatetask" name="dosql">
		<input type="hidden" value="<?php echo @$trow["task_id"];?>" name="task_id">
		<input type="hidden" value="<?php echo $user_cookie;?>" name="user_id">
		<input type="hidden" value="Update :<?php echo $$trow["task_name"];?>" name="comment_title">
		<input type="hidden" value="<?php echo @$trow["task_hours_worked"];?>" name="already_worked">
		<tr>
			<td colspan=2><b>Update Task</b></td>
			<td colspan=2>comments:</td>
		</tr>
		<tr>
			<td align="right" bgcolor="#cccccc" nowrap>hours worked<br>since last update
			 </td>
			<td bgcolor="#cccccc"><input type="text" name="worked" maxlength=3 size=4></td>
			<td bgcolor="#cccccc" rowspan=2><textarea name="comments" cols=25 rows=4></textarea></td>
		</tr>
		<tr>
			<td bgcolor="#cccccc" align="right">percent<br>
			complete</td>
			<td bgcolor="#cccccc">
		<?php
			echo arraySelect( $percent, 'complete', 'size=1', $trow["task_precent_complete"] ) . '%';
		?>
			</td>
		</tr>
		<tr>
			<td colspan=3 ALIGN="CENTER"><input type="button" value="update task" onClick="updateTask()"></td>
		</tr>
		</form>

		<tr>
			<td colspan=3><b>Assigned Users</b></td>
		</tr>
		<tr>
			<td colspan=3>
				<table width="100%" cellspacing=1 bgcolor="black">
				<?php while($row = mysql_fetch_array($usql)){?>
				<tr><td bgcolor="#ffffff"><?php echo $row["user_username"];?></td><td bgcolor="#ffffff"><?php echo $row["user_email"];?></td></tr>
				<?php };?>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=2><b>Attached Files</b></td>
			<td align=right>
				<A href="./index.php?m=files&a=addedit&project_id=<?php echo $trow["task_project"];?>&file_task=<?php echo $task_id;?>">Attach a file<img src="./images/icons/forum_folder.gif" align=absmiddle width=20 height=20 alt="attach a file to this task" border=0></a>
			</td>
		</tr>
		<tr>
			<td colspan=3>
				<table width="100%" cellspacing=1 bgcolor="black">
					<?php if(mysql_num_rows($fsql)==0)echo "<tr><td bgcolor=#ffffff>none</td></tr>";
					while($row = mysql_fetch_array($fsql)){?>
					<tr><td bgcolor="#eeeeee"><A href="./fileviewer.php?file_id=<?php echo $row["file_id"];?>"><?php echo $row["file_name"];?></a></td><td bgcolor="#ffffff"><?php echo $row["file_type"];?></td><td bgcolor="#eeeeee"><?php echo $row["file_size"];?></td></tr>
					<?php };?>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<B>Task Log and Comments</b>

<table border="0" cellpadding="2" cellspacing="1" width="98%" class="tbl">
<tr>
	<td></td>
</tr>
<tr>
	<th width="100">Action</th>
	<th width="100">User</th>
	<th>Comments</th>
	<th width="150" class="mboxhdr">Date</th>
</tr>
<?php while($row = mysql_fetch_array( $crc, MYSQL_ASSOC )) { ?>
<tr bgcolor="white" valign=top>
	<td width="100"><?php echo $row["comment_title"];?></td>
	<td width="100"><?php echo $row["user_username"];?></td>
	<td><?php $newstr = str_replace(chr(10), "<BR>",$row["comment_body"]);echo $newstr;?></td>
	<td width="150"><?php echo fromDate($row["comment_date"]);?></td>
</tr>
	<?php }?>
</table>

</body>
</html>

