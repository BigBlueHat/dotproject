<?php /* TASKS $Id$ */
$task_id = isset( $_GET['task_id'] ) ? $_GET['task_id'] : 0;
$task_parent = isset( $_GET['task_parent'] ) ? $_GET['task_parent'] : 0;

// check permissions
$canEdit = !getDenyEdit( $m );

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$durTypes = dPgetSysVal( 'TaskDurationType' );

// pull the task
$sql = "SELECT * FROM tasks WHERE task_id = $task_id";

if (!db_loadHash( $sql, $task ) && $task_id > 0) {
	$AppUI->setMsg( "Invalid Task ID", UI_MSG_ERROR );
	$AppUI->redirect();
}

$task_parent = isset( $task['task_parent'] ) ? $task['task_parent'] : $task_parent;

// check for a valid project parent
$task_project = dPgetParam( $task, 'task_project', 0 );
if (!$task_project) {
	$task_project = dPgetParam( $_GET, 'task_project', 0 );
	if (!$task_project) {
		$AppUI->setMsg( "badTaskProject", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$ts = db_dateTime2unix( $task["task_start_date"] );
$start_date = new CDate( ($ts < 0 ? null : $ts), $df );
$start_date->setTime( 0, 0, 0 );

$ts = db_dateTime2unix( $task["task_end_date"] );
$end_date = new CDate( ($ts < 0 ? null : $ts), $df );
$end_date->setTime( 0, 0, 0 );

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

// setup the title block
$ttl = $task_id > 0 ? "Edit Task" : "Add Task";
$titleBlock = new CTitleBlock( $ttl, 'tasks.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
$titleBlock->addCrumb( "?m=projects&a=view&project_id=$task_project", "view this project" );
$titleBlock->addCrumb( "?m=tasks&a=view&task_id={$task['task_id']}", "view this task" );
$titleBlock->show();
?>

<SCRIPT language="JavaScript">
var calendarField = '';
var calWin = null;

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.editFrm.task_' + field + '.value' );
	calWin = window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

function submitIt(){
	var form = document.editFrm;
	var fl = form.assigned.length -1;
	var dl = form.task_dependencies.length -1;

	if (form.task_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('taskName');?>" );
		form.task_name.focus();
	} else if (!form.task_start_date.value) {
		alert( "<?php echo $AppUI->_('taskValidStartDate');?>" );
		form.task_start_date.focus();
	} else if (!form.task_end_date.value) {
		alert( "<?php echo $AppUI->_('taskValidEndDate');?>" );
		form.task_end_date.focus();
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
	var form = document.editFrm;
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
	var form = document.editFrm;
	fl = form.assigned.length -1;

	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			form.assigned.options[fl] = null;
		}
	}
}

function addTaskDependency() {
	var form = document.editFrm;
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
	var form = document.editFrm;
	td = form.task_dependencies.length -1;

	for (td; td > -1; td--) {
		if (form.task_dependencies.options[td].selected) {
			form.task_dependencies.options[td] = null;
		}
	}
}

var workHours = <?php echo $AppUI->getConfig( 'daily_working_hours' );?>;
var hourMSecs = 3600*1000;

function calcDuration() {
	var f = document.editFrm;

	var s = new Date( f.task_start_date.value*1000 );
	var e = new Date( f.task_end_date.value*1000 );

	var durn = (e - s) / hourMSecs;
	var durnType = parseFloat(f.task_duration_type.value);
	durn /= durnType;

	if (durnType == 1) {
		durn *= (workHours / 24);
	}
	f.task_duration.value = durn;
}

function calcFinish() {
	var f = document.editFrm;
	var durn = parseFloat(f.task_duration.value);
	var durnType = parseFloat(f.task_duration_type.value);

	var s = new Date( f.task_start_date.value*1000 );
	var inc = (durn) * durnType * hourMSecs;

	if (durnType == 1) {
		inc /= workHours;
	}
	var e = new Date( s.getTime() + inc );
	f.task_end_date.value = (s.getTime() + inc)/1000;
// this is the easy way out for the moment
	alert( 'NOTE: Finish date has been updated ['+f.task_end_date.value+'] although the formatted date has not' );
}
</script>

<table border="1" cellpadding="4" cellspacing="0" width="100%" class="std">
<form name="editFrm" action="?m=tasks&project_id=<?php echo $task_project;?>" method="post">
	<input name="dosql" type="hidden" value="do_task_aed" />
	<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
	<input name="task_project" type="hidden" value="<?php echo $task_project;?>" />
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
		<br /><input type="text" class="text" name="task_name" value="<?php echo @$task["task_name"];?>" size="40" maxlength="255" />
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
				<?php echo arraySelect( $percent, 'task_percent_complete', 'size="1" class="text"', $task["task_percent_complete"] ) . '%';?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Milestone' );?>?</td>
			<td>
				<input type=checkbox value=1 name="task_milestone" <?php if($task["task_milestone"]){?>checked<?php }?> />
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
		<br /><input type="text" class="text" name="task_related_url" value="<?php echo @$task["task_related_url"];?>" size="40" maxlength="255" />
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
			<td>$<input type="text" class="text" name="task_target_budget" value="<?php echo @$task["task_target_budget"];?>" size="10" maxlength="10" /></td>
		</tr>
		</table>
	</td>
	<td  align="center" width="50%">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_start_date" value="<?php echo $start_date->getTimestamp();?>" />
					<input type="text" name="start_date" value="<?php echo $start_date->toString();?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('start_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Finish Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_end_date" value="<?php echo $end_date ? $end_date->getTimestamp() : '-1';?>" />
					<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->toString() : '';?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('end_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Expected Duration' );?>:</td>
				<td nowrap="nowrap">
					<input type="text" class="text" name="task_duration" maxlength="8" size="6" value="<?php echo dPgetParam( $task, 'task_duration', 0);?>" />
				<?php
					echo arraySelect( $durTypes, 'task_duration_type', 'class="text"', $task["task_duration_type"] );
				?>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Calculate' );?>:</td>
				<td nowrap="nowrap">
					<input type="button" value="<?php echo $AppUI->_('Duration');?>" onclick="calcDuration()" class="button" />
					<input type="button" value="<?php echo $AppUI->_('Finish Date');?>" onclick="calcFinish()" class="button" />
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Dynamic Task' );?>?</td>
				<td nowrap="nowrap">
					<input type="checkbox" name="task_dynamic" value="1" <?php if($task["task_dynamic"]!="0") echo "checked"?> />
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
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addTaskDependency()" /></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeTaskDependency()" /></td>
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
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser()" /></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser()" /></td>
			</tr>
			</tr>
			<tr>
				<td colspan=3 align="center">
					<input type="checkbox" name="notify" value="1" /> <?php echo $AppUI->_( 'notifyChange' );?>
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

<table border="0" cellspacing="0" cellpadding="3" width="100%">
<tr>
	<td height="40" width="35%">
		* <?php echo $AppUI->_( 'requiredField' );?>
	</td>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class="button" type="button" name="cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = '?<?php echo $AppUI->getPlace();?>';}" />
			</td>
			<td>
				<input class="button" type="button" name="btnFuseAction" value="save" onClick="submitIt();" />
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="hassign" />
<input type="hidden" name="hdependencies" />
</form>

</body>
</html>
