<?php
$task_id = isset( $_GET['task_id'] ) ? $_GET['task_id'] : 0;
$task_parent = isset( $_GET['task_parent'] ) ? $_GET['task_parent'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

$project_id = $AppUI->getState( 'ActiveProject' ) ? $AppUI->getState( 'ActiveProject' ) : 0;

// pull the task
$sql = "SELECT * FROM tasks WHERE task_id = $task_id";
db_loadHash( $sql, $task );
$task_parent = isset( $task['task_parent'] ) ? $task['task_parent'] : $task_parent;

// pull the related project
$sql = "SELECT project_name, project_id, project_color_identifier FROM projects WHERE project_id = $project_id";
db_loadHash( $sql, $project );

//Pull all users
$sql = "
SELECT user_id, CONCAT( user_first_name, ' ', user_last_name)
FROM users
ORDER BY user_first_name, user_last_name
";
$users = db_loadHashList( $sql );

//Pull users on this task
$sql = "
SELECT u.user_id, CONCAT( u.user_first_name, ' ', u.user_last_name )
FROM users u, user_tasks t
WHERE t.task_id =$task_id
	AND t.task_id <> 0
	AND t.user_id = u.user_id
";
$assigned = db_loadHashList( $sql );

// Pull tasks for the parent task list
$sql="
SELECT task_id, task_name
FROM tasks
WHERE task_project = $project_id
	AND task_id <> $task_id
ORDER BY task_project
";
$projTasks = arrayMerge( array( "{$task['task_id']}" => 'None' ), db_loadHashList( $sql ) );

// Pull tasks dependencies
$sql = "
SELECT t.task_id, t.task_name
FROM tasks t, task_dependencies td
WHERE td.dependencies_task_id = $task_id
	AND t.task_id = td.dependencies_req_task_id
";
$taskDep = db_loadList( $sql );

$crumbs = array();
$crumbs["?m=projects&a=view&project_id={$task['task_project']}"] = "view this project";
$crumbs["?m=tasks"] = "tasks list";
$crumbs["?m=tasks&a=addedit&task_id={$task['task_id']}"] = "view this task";
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
	var dl = form.task_dependencies.length -1;
	
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
		
		form.hdependencies.value = "";
		for (dl; dl > -1; dl--){
			form.hdependencies.value = "," + form.hdependencies.value +","+ form.task_dependencies.options[dl].value
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

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<form name="AddEdit" action="./index.php?m=tasks&project_id=<?php echo $project_id ?>" method="post">
<input name="dosql" type="hidden" value="addeditTask">
<input name="del" type="hidden" value="0">
<input name="task_id" type="hidden" value="<?php echo $task_id;?>">
<input name="task_project" type="hidden" value="<?php echo $project_id;?>">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td align="left" nowrap="nowrap" width="100%">
		<span class="title"><?php echo $AppUI->_( $task_id ? 'Edit Task' : 'New Task' );?></span>
	</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_TASK_VIEW' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right">
		<a href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="" border="0"><?php echo $AppUI->_('delete task');?></a>
	</td>
</tr>
</table>


<table border="1" cellpadding="4" cellspacing="0" width="98%" bgcolor="#eeeeee">
<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:<?php echo $project["project_color_identifier"];?>" >
		<font color="<?php echo bestColor( $project["project_color_identifier"] ); ?>">
			<b><?php echo $AppUI->_('Project');?>: <?php echo @$project["project_name"];?></b>
		</font>
	</td>
</tr>

<tr class="basic" valign="top" width="50%">
	<td>
		<span id="ccstasknamestr"><span class="FormLabel">Task name</span> <span class="FormElementRequired">*</span></span><br><input type="text" name="task_name" value="<?php echo @$task["task_name"];?>" size="40" maxlength="255">
	</td>
	<td>
		<table width="100%" bgcolor="#dddddd">
		<tr>
			<td>Status</td>
			<td><span class="FormLabel">priority</span> <span class="FormElementRequired">*</span></td>
			<td nowrap>Complete?</td>
			<td>Milestone?</td>
		</tr>
		<tr>
			<td>		
			<?php
				echo arraySelect( $status, 'task_status', 'size=1 class=text', $task["task_status"] ) . '%';
			?>
			</td>
			<td nowrap>
			<?php
				echo arraySelect( $priority, 'task_priority', 'size=1 class=text', $task["task_priority"] );
			?>
			</td>
			<td>		
			<?php
				echo arraySelect( $percent, 'task_precent_complete', 'size=1 class=text', $task["task_precent_complete"] ) . '%';
			?>
			</td>
			<td>
				<input type=checkbox value=1 name="task_milestone" <?php if($task["task_milestone"]){?>checked<?php }?>>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr class="basic" valign="top">
	<td width="50%">
		Task creator
		<br>
		<?php echo arraySelect( $users, 'task_owner', '"width:200px;"', $task["task_owner"] );?>
		<br><br>Related URL
		<br><input type="Text" name="task_related_url" value="<?php echo @$task["task_related_url"];?>" size="40" maxlength="255"">
		<br>
		<table>
		<tr>
			<td>Task Parent:</td>
			<td><img src="./images/shim.gif" width=30 height=1></td>
			<td>Task budget</td>
		</tr>
		<tr>
			<td>
				<?php echo arraySelect( $projTasks, 'task_parent', '', $task_parent ); ?>
			</td>
			<td><img src="./images/shim.gif" width=30 height=1></td>			
			<td>$<input type="Text" name="task_target_budget" value="<?php echo @$task["task_target_budget"];?>" size="10" maxlength="10"></td>			
		</tr>
		</table>
	</td>
	<td  align="center" width="50%">
		<table width="300">
			<tr>
				<td>
					<span id="startmmint"><span class="FormLabel">Start Date
					<br>(<?php echo dateFormat()?>)</span></span>
				</td>
				<td>
					<span id="targetmmint"><span class="FormLabel">Finish Date
					<br>(<?php echo dateFormat()?>)</span>
				</td>
			</tr>
			<tr>
				<td nowrap>
					<input type="text" name="task_start_date" value="<?php if(intval($task["task_start_date"]) > 0){
						echo fromDate(substr($task["task_start_date"], 0, 10));
					} else {
						echo fromDate(date("Y", time()) ."-" . date("m", time()) ."-" . date("d", time()));
					};?>" size="10" maxlength="10">
					<a href="#" onClick="popCalendar('task_start_date');"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> 
					<a href="#" onClick="popCalendar('task_start_date');">calendar</A> &nbsp; &nbsp; &nbsp;
				</td>
				<td nowrap>
					<input type="text" name="task_end_date" value="<?php if(intval($task["task_end_date"]) > 0) {
						echo fromDate(substr($task["task_end_date"], 0, 10));
					} ?>" size="10" maxlength="10" onclick="javascript:document.AddEdit.task_dynamic.checked=false">
					<a href="#" onClick="javascript:document.AddEdit.task_dynamic.checked=false;popCalendar('task_end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="javascript:document.AddEdit.task_dynamic.checked=false;popCalendar('task_end_date')">calendar</A>
				</td>
			</tr>
			<tr>
				<td>Expected duration:</td>				
				<td>Dynamic Task?</td>
			</tr>
			<tr>
				<td>
			<?php if (($task["task_duration"]) > 24 ) {
				$newdir = ($task["task_duration"] / 24);
				$dir = 24;
			} else {
				$newdir = ($task["task_duration"]);
				$dir = 1;
			}
			if ($newdir ==0) {
				$newdir ="";
			}
			?>
			<input type="text" name="duration" maxlength=4 size=5 value="<?php echo $newdir;?>">
			<select name="dayhour">
				<option value="1" <?php  if ($dir ==1) echo "selected";?>>hour(s)
				<option value="24" <?php  if ($dir ==24) echo "selected";?>>day(s)
			</select>
			</td>
			
			<td><input type="checkbox" name=task_dynamic value=1 <?php if($task["task_dynamic"]!="0") echo "checked"?>></td>
			</tr>
		</table>
	</td>
</tr>
<tr class="basic">
	<td>
		<table>
			<tr>
				<td>All tasks</td>
				<td></td>
				<td>Task dependencies</td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $projTasks, 'all_tasks', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
				<td nowrap>
					<input type="button" value=" << " onClick="removeTaskDependency()">
					<input type="button" value=" >> " onClick="addTaskDependency()">
				</td>
				<td>
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
			</tr>
		</table>		
	</td>
	<td>&nbsp;</td>
</tr>
<tr class="basic">
	<td valign="middle">
		<span id="fulldesctext"><span class="formlabel">Instructions:</span></span><br>
		<textarea name="task_description" cols="38" rows="10" wrap="virtual"><?php echo @$task["task_description"];?></textarea>
	</td>
	<td valign="middle">
		<table>
			<tr>
				<td>Resources</td>
				<td></td>
				<td>Assigned to Task</td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $users, 'resources', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
				<td nowrap>
					<input type="button" value=" << " onClick="removeUser()">
					<input type="button" value=" >> " onClick="addUser()">
				</td>
				<td>
					<?php echo arraySelect( $assigned, 'assigned', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3 align="center">
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
<input type="hidden" name="hdependencies">
</form>

</body>
</html>
