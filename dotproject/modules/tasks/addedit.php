<?php
$task_id = isset( $HTTP_GET_VARS['task_id'] ) ? $HTTP_GET_VARS['task_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

if(empty($project_id))$project_id =0;

//pull users;
if(empty($task_parent))$task_parent =0;

//Pull all users
$usql = "
SELECT user_first_name, user_last_name, user_id
FROM users
ORDER BY user_first_name, user_last_name
";

$urc = mysql_query( $usql );

//Pull users on this task
$tsql = "
SELECT t.task_id,
	u.user_id, u.user_username, u.user_first_name, u.user_last_name
FROM users u, user_tasks t
WHERE t.task_id =$task_id
	AND t.task_id <> 0
	AND t.user_id = u.user_id
";

$trc = mysql_query( $tsql );

$psql = "Select * from tasks where task_id = $task_id";
$prc = mysql_query( $psql );
if ($prow = mysql_fetch_array( $prc, MYSQL_ASSOC )) {
	//Pull specific project information
	$pisql="select project_name, project_id from projects where project_id =". $prow["task_project"];
	$pirc = mysql_query( $pisql );
	$pirow = mysql_fetch_array( $pirc, MYSQL_ASSOC );
} else {
	//Pull project information
	$pisql="select project_name, project_id from projects where project_id = $project_id";
	$pirc = mysql_query($pisql);
	$pirow = mysql_fetch_array( $pirc, MYSQL_ASSOC );
}
if (isset( $pirow["project_id"] )) {
	$project_id = $pirow["project_id"];
}

//Pull tasks for the parent list
$atsql="
SELECT task_name, task_id, task_project
FROM tasks
WHERE task_project = $project_id
	AND task_id <> $task_id
ORDER BY task_project";

$atrc = mysql_query( $atsql );

//------------------------------------ Start Page ----------------------------------------------//
?>

<SCRIPT language="JavaScript">
function popCalendar(x){
	var form = document.AddEdit;

	mm = <?php echo strftime("%m", time());?>;
	dd = <?php echo strftime("%d", time());?>;
	yy = <?php echo strftime("%Y", time());?>;

<?php  JScalendarDate("AddEdit"); ?>
	newwin = window.open( './calendar.php?form=AddEdit&page=tasks&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false' );
}

function submitIt(){
	var form = document.AddEdit;
	var fl = form.assigned.length -1;

	if (form.task_name.value.length < 3) {
		alert( "Please enter a valid task Name" );
		form.task_name.focus();
	} else if (form.task_start_date.value.length < 9) {
		alert( "Please enter a valid start date" );
		form.task_start_date.focus();
<?php if(REQUIRE_TASKS_DURATION) { ?>
	} else if (form.duration.value.length < 1) {
		alert( "Please enter the duration of this task" );
		form.duration.focus();
<?php } ?>
	} else {
		form.hassign.value = "";
		for (fl; fl > -1; fl--){
			form.hassign.value = "," + form.hassign.value +","+ form.assigned.options[fl].value
		}
		form.submit();
	}
}

function addUser() {
	var form = document.AddEdit;
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assigned.options[au].value + ","
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf( "," + form.resources.options[fl].value + "," ) == -1) {
			t = form.assigned.length
			opt = new Option( form.resources.options[fl].text, form.resources.options[fl].value );
			form.assigned.options[t] = opt
		}
	}
}

function removeUser() {
	var form = document.AddEdit;
	fl = form.assigned.length -1;

	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			form.assigned.options[fl] = null;
		}
	}
}

function addTaskDependency() {
	var form = document.AddEdit;
	var at = form.all_tasks.length -1;
	var td = form.task_dependencies.length -1;
	var tasks = "x";

	//build array of task dependencies
	for (td; td > -1; td--) {
		tasks = tasks + "," + form.task_dependencies.options[td].value + ","
	}

	//Pull selected resources and add them to list
	for (at; at > -1; at--) {
		if (form.all_tasks.options[at].selected && tasks.indexOf( "," + form.all_tasks.options[at].value + "," ) == -1) {
			t = form.task_dependencies.length
			opt = new Option( form.all_tasks.options[at].text, form.all_tasks.options[at].value );
			form.task_dependencies.options[t] = opt
		}
	}
}

function removeTaskDependency() {
	var form = document.AddEdit;
	td = form.task_dependencies.length -1;

	for (td; td > -1; td--) {
		if (form.task_dependencies.options[td].selected) {
			form.task_dependencies.options[td] = null;
		}
	}
}

function delIt() {
	if (confirm( "Are you sure that you would like to delete this task?\n" )) {
		var form = document.AddEdit;
		form.del.value=1;
		form.submit();
	}
}
</script>

<TABLE width="95%" border="0" cellpadding="0" cellspacing="1">
<form name="AddEdit" action="./index.php?m=tasks&project_id=<?php echo $project_id ?>" method="post">
<input name="dosql" type="hidden" value="addeditTask">
<input name="del" type="hidden" value="0">
<input name="task_id" type="hidden" value="<?php echo $task_id;?>">
<input name="task_project" type="hidden" value="<?php echo $project_id;?>">
<TR>
	<TD><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<TD nowrap><span class="title"><?php
		echo $task_id ? 'Edit Existing' : 'Create New';
		echo " task for ". $pirow['project_name'];
	?></span>
	</td>
	<TD align="right" width="100%" valign="bottom"><?php if($task_id){?><A href="javascript:delIt()">delete task<img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this task" border="0"></a><?php }?></td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%" >
<tr>
	<td class="allFormsTitleHeader" valign="middle">
		<b><?php	
			echo $task_id ? 'Edit the task using' : 'To create a new task complete';
			echo " the form below";
		?></b>
	</td>
</tr>
</table>

<table border="1" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
<tr class="basic" valign="top" width="50%">
	<td>
		<span id="ccstasknamestr"><span class="FormLabel">Task name</span> <span class="FormElementRequired">*</span></span><br><input type="text" name="task_name" value="<?php echo @$prow["task_name"];?>" size="40" maxlength="255">
	</td>
	<td>
		<TABLE width="100%" bgcolor="#dddddd">
		<TR>
			<TD>Status</TD>
			<TD><span class="FormLabel">priority</span> <span class="FormElementRequired">*</span></TD>
			<TD nowrap>Complete?</TD>
			<TD>Milestone?</TD>
		</TR>
		<TR>
			<TD>		
			<?php
				echo arraySelect( $status, 'task_status', 'size=1 class=text', $prow["task_status"] ) . '%';
			?>
			</TD>
			<TD nowrap>
			<?php
				echo arraySelect( $priority, 'task_priority', 'size=1 class=text', $prow["task_priority"] );
			?>
			</TD>
			<TD>		
			<?php
				echo arraySelect( $percent, 'task_precent_complete', 'size=1 class=text', $prow["task_precent_complete"] ) . '%';
			?>
			</TD>
			<TD>
				<input type=checkbox value=1 name="task_milestone" <?php if($prow["task_milestone"]){?>checked<?php }?>>
			</TD>
		</TR>
		</TABLE>
	</td>
</tr>
<tr class="basic" valign="top">
	<TD width="50%">
		Task owner
		<br><select name="task_owner" style="width:200px;">

		<?php while ($row = mysql_fetch_array( $urc )) { ?>
			<option value="<?php echo $row["user_id"];?>"
		<?php
		if ($task_id == 0 && $row["user_id"] == $user_cookie) {
			echo "selected";
		} else if ($prow["task_owner"] == $row["user_id"]) {
			echo "selected";
		}?>><?php echo $row["user_first_name"];?> <?php echo $row["user_last_name"];?>
		<?php }?>
		</select>
		<br><br>Related URL
		<br><input type="Text" name="task_related_url" value="<?php echo @$prow["task_related_url"];?>" size="40" maxlength="255"">
		<br>
		<table>
		<tr>
			<TD>Task Parent:</td>
			<TD><img src="./images/shim.gif" width=30 height=1></td>
			<TD>Task budget</td>
		</tr>
		<tr>
			<TD>
				<select name="task_parent" style="width:150px;"><option value="<?php echo $prow["task_id"];?>">None
					<?php
					while ($row = mysql_fetch_array( $atrc )) {
						echo '<option value="' . $row["task_id"].'"';
						if ($row["task_id"] == $prow["task_parent"] || $row["task_id"] == $task_parent) {
							echo ' selected';
						}
						echo '>'.$row["task_name"];
					}?>
				</select>
			</td>
			<TD><img src="./images/shim.gif" width=30 height=1></td>			
			<TD>$<input type="Text" name="task_target_budget" value="<?php echo @$prow["task_target_budget"];?>" size="10" maxlength="10"></td>			
		</tr>
		</table>
	</td>
	<td  align="center" width="50%">
		<TABLE width="300">
			<TR>
				<TD>
					<span id="startmmint"><span class="FormLabel">start date
					<br>(<?php echo dateFormat()?>)</span></span>
				</TD>
				<TD>
					<span id="targetmmint"><span class="FormLabel">finish date
					<br>(<?php echo dateFormat()?>)</span>
				</TD>
			</TR>
			<TR>
				<TD nowrap>
					<input type="text" name="task_start_date" value="<?php if(intval($prow["task_start_date"]) > 0){
						echo fromDate(substr($prow["task_start_date"], 0, 10));
					} else {
						echo fromDate(date("Y", time()) ."-" . date("m", time()) ."-" . date("d", time()));
					};?>" size="10" maxlength="10">
					<a href="#" onClick="popCalendar('task_start_date');"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> 
					<a href="#" onClick="popCalendar('task_start_date');">calendar</A> &nbsp; &nbsp; &nbsp;
				</td>
				<TD nowrap>
					<input type="text" name="task_end_date" value="<?php if(intval($prow["task_end_date"]) > 0) {
						echo fromDate(substr($prow["task_end_date"], 0, 10));
					} ?>" size="10" maxlength="10">
					<a href="#" onClick="popCalendar('task_end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('task_end_date')">calendar</A>
				</td>
			</tr>
			<TR>
				<TD colspan=2>Expected duration:</td>
			</tr>
			<TR>
				<TD colspan=2>
			<?php if (($prow["task_duration"]) > 24 ) {
				$newdir = ($prow["task_duration"] / 24);
				$dir = 24;
			} else {
				$newdir = ($prow["task_duration"]);
				$dir = 1;
			}
			if ($newdir ==0) {
				$newdir ="";
			}
			?>
			<input type="text" name="duration" maxlength =4 size=5 value="<?php echo $newdir;?>">
			<select name="dayhour">
				<option value="1" <?php  if ($dir ==1) echo "selected";?>>hour(s)
				<option value="24" <?php  if ($dir ==24) echo "selected";?>>day(s)
			</select>
			</td></tr>
		</table>
	</td>
</tr>
<tr class="basic">
	<td>
		<TABLE>
			<TR>
				<TD>All tasks</td>
				<TD></td>
				<TD>Task dependencies</td>
			</TR>
			<TR>
				<TD>
					<Select multiple name="all_tasks" style="width:150px" size="10" style="font-size:9pt;">
				<?php
					mysql_data_seek( $atrc, 0 );
					while ($row = mysql_fetch_array( $atrc)) {
						echo "<option value='".$row["task_id"]."'>". $row["task_name"];
					}
				?>
					</select>
				</td>
				<TD nowrap>
					<input type="button" value=" << " onClick="removeTaskDependency()">
					<input type="button" value=" >> " onClick="addTaskDependency()">
				</td>
				<TD>
					<Select multiple name="task_dependencies" style="width:150px" size="10" style="font-size:9pt;">
				<?php
					/*
					mysql_data_seek( $atrc, 0 );
					while ($row = mysql_fetch_array( $atrc, MYSQL_ASSOC )) {
						echo "<option value='".$row["task_id"]."'>". $row["task_name"];
					}
					*/
				?>
					</select>
				</td>
			</tr>
		</table>		
	</td>
	<td>&nbsp;</td>
</tr>
<tr class="basic">
	<td valign="middle">
		<span id="fulldesctext"><span class="formlabel">Instructions:</span></span><br>
		<textarea name="task_description" cols="38" rows="10" wrap="virtual"><?php echo @$prow["task_description"];?></textarea>
	</td>
	<td valign="middle">
		<TABLE>
			<TR>
				<TD>Resources</td>
				<TD></td>
				<TD>Assigned to Task</td>
			</TR>
			<TR>
				<TD>
					<Select multiple name="resources" style="width:150px" size="10" style="font-size:9pt;">
				<?php
					mysql_data_seek( $urc, 0 );
					while ($row = mysql_fetch_array( $urc, MYSQL_ASSOC )) {
						echo "<option value=\"".$row["user_id"]."\">". $row["user_first_name"] ." " . $row["user_last_name"];
					}
				?>
					</select>
				</td>
				<TD nowrap>
					<input type="button" value=" << " onClick="removeUser()">
					<input type="button" value=" >> " onClick="addUser()">
				</td>
				<TD>
					<Select multiple name="assigned" style="width:150px" size="10" style="font-size:9pt;">
				<?php
					mysql_data_seek( $urc, 0 );
					while ($row = mysql_fetch_array( $trc, MYSQL_ASSOC )) {
						echo "<option value=\"".$row["user_id"]."\">" . $row["user_first_name"] ." " .$row["user_last_name"];
					}
				?>
					</select>
				</td>
			</tr>
			<TR>
				<TD colspan=3 align="center">
					<input type="checkbox" name="notify" value="1"> Notify Assignees of Task by Email
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="3" width="95%">
<tr class="basic">
	<td height="40" width="35%">
		<span class="FormElementRequired">*</span> <span class="FormInstruction">indicates required field</span>
	</td>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class=button type="Button" name="Cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=tasks&project_id=<?php echo $project_id ?>';}">
			</td>
			<td>
				<input class=button type="Button" name="btnFuseAction" value="save" onClick="submitIt();">
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="hassign">
</form>

</body>
</html>
