<?php
$task_id = isset( $_GET['task_id'] ) ? $_GET['task_id'] : 0;
$task_parent = isset( $_GET['task_parent'] ) ? $_GET['task_parent'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

// pull the task
$sql = "SELECT * FROM tasks WHERE task_id = $task_id";
db_loadHash( $sql, $task );
$task_parent = isset( $task['task_parent'] ) ? $task['task_parent'] : $task_parent;
$task_project = @$task['task_project'] ? $task['task_project'] : $AppUI->getProject();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = $task["task_start_date"] ? CDate::fromDateTime( $task["task_start_date"] ) : new CDate();
$start_date->setFormat( $df );

if ($task["task_end_date"]) {
	$end_date = CDate::fromDateTime( $task["task_end_date"] );
	$end_date->setFormat( $df );
} else {
	$end_date = null;
}

// pull the related project
$sql = "SELECT project_name, project_id, project_color_identifier FROM projects WHERE project_id = $task_project";
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
WHERE task_project = $task_project
	AND task_id <> $task_id
ORDER BY task_project
";

$projTasks = array( "{$task['task_id']}" => 'None' );
$res = db_exec( $sql );
while ($row = db_fetch_row( $res )) {
	if (strlen( $row[1] ) > 25) {
		$row[1] = substr( $row[1], 0, 22 ).'...';
	}
	$projTasks[$row[0]] = $row[1];
}

// Pull tasks dependencies
$sql = "
SELECT t.task_id, t.task_name
FROM tasks t, task_dependencies td
WHERE td.dependencies_task_id = $task_id
	AND t.task_id = td.dependencies_req_task_id
";
$taskDep = db_loadHashList( $sql );

$crumbs = array();
$crumbs["?m=projects&a=view&project_id=$task_project"] = "view this project";
$crumbs["?m=tasks"] = "tasks list";
$crumbs["?m=tasks&a=view&task_id={$task['task_id']}"] = "view this task";
?>

<SCRIPT language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.AddEdit.task_' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.AddEdit.task_' + calendarField );
	fld_fdate = eval( 'document.AddEdit.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

function submitIt(){
	var form = document.AddEdit;
	var fl = form.assigned.length -1;
	var dl = form.task_dependencies.length -1;
	
	if (form.task_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('taskName');?>" );
		form.task_name.focus();
	} else if (form.task_start_date.value.length < 9) {
		alert( "<?php echo $AppUI->_('taskValidStartDate');?>" );
		form.task_start_date.focus();
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
	if (confirm( "<?php echo $AppUI->_('taskDelete');?>\n" )) {
		var form = document.AddEdit;
		form.del.value=1;
		form.submit();
	}
}
</script>

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<form name="AddEdit" action="?m=tasks&project_id=<?php echo $task_project;?>" method="post">
<input name="dosql" type="hidden" value="task_aed">
<input name="del" type="hidden" value="0">
<input name="task_id" type="hidden" value="<?php echo $task_id;?>">
<input name="task_project" type="hidden" value="<?php echo $task_project;?>">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td align="left" nowrap="nowrap" width="100%"><h1><?php echo $AppUI->_( $task_id ? 'Edit Task' : 'New Task' );?></h1></td>
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


<table border="1" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:#<?php echo $project["project_color_identifier"];?>" >
		<font color="<?php echo bestColor( $project["project_color_identifier"] ); ?>">
			<strong><?php echo $AppUI->_('Project');?>: <?php echo @$project["project_name"];?></strong>
		</font>
	</td>
</tr>

<tr valign="top" width="50%">
	<td>
		<?php echo $AppUI->_( 'Task Name' );?> *
		<br /><input type="text" class="text" name="task_name" value="<?php echo @$task["task_name"];?>" size="40" maxlength="255">
	</td>
	<td>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Status' );?></td>
			<td>		
				<?php echo arraySelect( $status, 'task_status', 'size="1" class="text"', $task["task_status"] ) . '%';?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Priority' );?> *</td>
			<td nowrap>
				<?php echo arraySelect( $priority, 'task_priority', 'size="1" class="text"', $task["task_priority"] );?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Progress' );?></td>
			<td>		
				<?php echo arraySelect( $percent, 'task_precent_complete', 'size="1" class="text"', $task["task_precent_complete"] ) . '%';?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Milestone' );?>?</td>
			<td>
				<input type=checkbox value=1 name="task_milestone" <?php if($task["task_milestone"]){?>checked<?php }?>>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
		<?php echo $AppUI->_( 'Task Creator' );?>
		<br />
	<?php echo arraySelect( $users, 'task_owner', 'class="text"', !isset($task["task_owner"]) ? $AppUI->user_id : $task["task_owner"] );?>
		<br /><br /><?php echo $AppUI->_( 'Web Address' );?>
		<br /><input type="text" class="text" name="task_related_url" value="<?php echo @$task["task_related_url"];?>" size="40" maxlength="255"">
		<br />
		<table>
		<tr>
			<td><?php echo $AppUI->_( 'Task Parent' );?>:</td>
			<td><img src="./images/shim.gif" width=30 height=1></td>
			<td><?php echo $AppUI->_( 'Target Budget' );?></td>
		</tr>
		<tr>
			<td>
				<?php echo arraySelect( $projTasks, 'task_parent', 'class="text"', $task_parent ); ?>
			</td>
			<td><img src="./images/shim.gif" width=30 height=1></td>			
			<td>$<input type="text" class="text" name="task_target_budget" value="<?php echo @$task["task_target_budget"];?>" size="10" maxlength="10"></td>			
		</tr>
		</table>
	</td>
	<td  align="center" width="50%">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_start_date" value="<?php echo $start_date->getTimestamp();?>">
					<input type="text" name="start_date" value="<?php echo $start_date->toString();?>" class="text" disabled="disabled">
					<a href="#" onClick="popCalendar('start_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Finish Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_end_date" value="<?php echo $end_date ? $end_date->getTimestamp() : '-1';?>">
					<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->toString() : '';?>" class="text" disabled="disabled">
					<a href="#" onClick="popCalendar('end_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Expected Duration' );?>:</td>				
				<td nowrap="nowrap">
			<?php if (($task["task_duration"]) > 24 ) {
				$newdir = ($task["task_duration"] / 24);
				$dir = 24;
			} else {
				$newdir = ($task["task_duration"]);
				$dir = 1;
			}
			if ($newdir ==0) {
				$newdir ="0";
			}
			?>
					<input type="text" class="text" name="task_duration" maxlength=4 size=5 value="<?php echo $newdir;?>">
					<select name="dayhour" size="1" class="text">
						<option value="1" <?php  if ($dir ==1) echo "selected";?>>hour(s)
						<option value="24" <?php  if ($dir ==24) echo "selected";?>>day(s)
					</select>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Dynamic Task' );?>?</td>
				<td nowrap="nowrap">
					<input type="checkbox" name=task_dynamic value="1" <?php if($task["task_dynamic"]!="0") echo "checked"?>>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'All Tasks' );?></td>
				<td><?php echo $AppUI->_( 'Task Dependencies' );?></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $projTasks, 'all_tasks', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addTaskDependency()"></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeTaskDependency()"></td>
			</tr>
		</table>		
	</td>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'Resources' );?></td>
				<td><?php echo $AppUI->_( 'Assigned to Task' );?></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $users, 'resources', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $assigned, 'assigned', 'style="width:150px" size="10" style="font-size:9pt;" multiple="multiple"', null ); ?>
				</td>
			<tr>
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser()"></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser()"></td>
			</tr>
			</tr>
			<tr>
				<td colspan=3 align="center">
					<input type="checkbox" name="notify" value="1"> <?php echo $AppUI->_( 'notifyChange' );?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td  colspan="2" valign="top">
		<?php echo $AppUI->_( 'Description' );?>:
		<br />
		<textarea name="task_description" class="textarea" cols="60" rows="10" wrap="virtual"><?php echo @$task["task_description"];?></textarea>
	</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="3" width="98%">
<tr>
	<td height="40" width="35%">
		* <?php echo $AppUI->_( 'requiredField' );?>
	</td>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class="button" type="button" name="cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = '?<?php echo $AppUI->getPlace();?>';}">
			</td>
			<td>
				<input class="button" type="button" name="btnFuseAction" value="save" onClick="submitIt();">
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
