<?php /* TASKS $Id$ */
global $showEditCheckbox;

$showEditCheckbox = true;
// Project status from sysval, defined as a constant
$project_on_hold_status = 4;
$perms =& $AppUI->acl();

$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );
$date       = intval( dPgetParam( $_GET, 'date', '' ) );
$user_id    = $AppUI->user_id;
$no_modify	= false;
$other_users	= false;

if($perms->checkModule("admin","view")){ // let's see if the user has sysadmin access
	$other_users = true;
	if(($show_uid = dPgetParam($_REQUEST, "show_user_todo", 0)) != 0){ // lets see if the user wants to see anothers user mytodo
		$user_id = $show_uid;
		$no_modify = true;
		$AppUI->setState("user_id", $user_id);
	} else {
//		$user_id = $AppUI->getState("user_id");
	}
}

// check permissions
$canEdit = $perms->checkModule( $m, 'edit' );

// retrieve any state parameters
if (isset( $_POST['show_form'] )) {
	$AppUI->setState( 'TaskDayShowArc', dPgetParam( $_POST, 'show_arc_proj', 0 ) );
	$AppUI->setState( 'TaskDayShowLow', dPgetParam( $_POST, 'show_low_task', 0 ) );
	$AppUI->setState( 'TaskDayShowHold', dPgetParam($_POST, 'show_hold_proj', 0 ) );
	$AppUI->setState( 'TaskDayShowDyn', dPgetParam($_POST, 'show_dyn_task', 0) );
	$AppUI->setState( 'TaskDayShowPin', dPgetParam($_POST, 'show_pinned', 0));
}
$showArcProjs = $AppUI->getState( 'TaskDayShowArc', 0 );
$showLowTasks = $AppUI->getState( 'TaskDayShowLow', 1);
$showHoldProjs = $AppUI->getState( 'TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);

// if task priority set and items selected, do some work
$task_priority = dPgetParam( $_POST, 'task_priority', 99 );
$selected = dPgetParam( $_POST, 'selected_task', 0 );

if (is_array($selected) && count( $selected )) {
	foreach ($selected as $key => $val) {
		if ( $task_priority == 'c' ) {
			// mark task as completed
			$sql = "UPDATE tasks SET task_percent_complete=100 WHERE task_id=$val";
		} else if ( $task_priority == 'd' ) {
			// delete task
			$sql = "DELETE FROM tasks WHERE task_id=$val";
		} else if ( $task_priority > -2 && $task_priority < 2 ) {
			// set priority
			$sql = "UPDATE tasks SET task_priority=$task_priority WHERE task_id=$val";
		}
		db_exec( $sql );
		echo db_error();		
	}
}

$AppUI->savePlace();

$proj =& new CProject;
$tobj =& new CTask;

$allowedProjects = $proj->getAllowedSQL($AppUI->user_id);
$allowedTasks = $tobj->getAllowedSQL($AppUI->user_id, 'a.task_id');

// query my sub-tasks (ignoring task parents)

$q = new DBQuery;
$q->addQuery('ta.*');
$q->addQuery('project_name, project_id, project_color_identifier');
$q->addQuery('tp.task_pinned');
$q->addTable('projects', 'pr');
$q->addTable('tasks', 'ta');
$q->addTable('user_tasks', 'ut');
$q->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . $user_id);

$q->addWhere('ut.task_id = ta.task_id');
$q->addWhere("ut.user_id = '$user_id'");
$q->addWhere('( ta.task_percent_complete < 100 or ta.task_percent_complete is null)');
$q->addWhere("ta.task_end_date != ''");
$q->addWhere("ta.task_status = '0'");
$q->addWhere("pr.project_id = ta.task_project");
if (!$showArcProjs)
	$q->addWhere('project_active = 1');
if (!$showLowTasks)
	$q->addWhere('task_priority >= 0');
if (!$showHoldProjs)
	$q->addWhere('project_status != ' . $project_on_hold_status);
if (!$showDynTasks)
	$q->addWhere('task_dynamic = 0');
if ($showPinned)
	$q->addWhere('task_pinned = 1');

if (count($allowedTasks))
	$q->addWhere($allowedTasks);

if (count($allowedProjects))
	$q->addWhere($allowedProjects);

$q->addGroup('ta.task_id');
$q->addOrder('ta.task_end_date');
$q->addOrder('task_priority DESC');

$sql = $q->prepare();
//echo "<pre>$sql</pre>";
$tasks = db_loadList( $sql );


$priorities = array(
	'1' => 'high',
	'0' => 'normal',
        '-1' => 'low'
);

global $durnTypes;
$durnTypes = dPgetSysVal( 'TaskDurationType' );

if (!@$min_view) {
	$titleBlock = new CTitleBlock( 'My Tasks To Do', 'applet-48.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=tasks", "tasks list" );
	$titleBlock->show();
}

?>

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<form name="form_buttons" method="post" action="index.php?<?php echo "m=$m&a=$a&date=$date";?>">
<input type="hidden" name="show_form" value="1" />

<tr>
	<td width="50%">
		<?php
	if ($other_users) {
		echo $AppUI->_("Show Todo for:");
		?>
		<select name="show_user_todo" onchange="document.form_buttons.submit()">
<?php
                $usersql = "
                SELECT user_id, user_username, contact_first_name, contact_last_name
                FROM users, contacts
                WHERE user_contact = contact_id
                ";


                if (($rows = db_loadList( $usersql, NULL )))
                {
                        foreach ($rows as $row)
                        {
                                if ( $user_id == $row["user_id"])
                                        echo "<OPTION VALUE='".$row["user_id"]."' SELECTED>".$row["user_username"];
                                else
                                        echo "<OPTION VALUE='".$row["user_id"]."'>".$row["user_username"];
			                  }
							  }
			}
		?>
		</select>
	</td>
	<td align="right" width="50%">
		<?php echo $AppUI->_('Show'); ?>:
	</td>
	<td>
		<input type=checkbox name="show_pinned" onclick="document.form_buttons.submit()" <?php echo $showPinned ? 'checked="checked"' : ""; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Pinned Only'); ?>
	</td>
	<td>
		<input type=checkbox name="show_arc_proj" onclick="document.form_buttons.submit()" <?php echo $showArcProjs ? 'checked="checked"' : ""; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Archived Projects'); ?>
	</td>
	<td>
		<input type=checkbox name="show_hold_proj" onclick="document.form_buttons.submit()" <?php echo $showHoldProjs ? 'checked="checked"' : ""; ?> />
	</td>
    <td nowrap="nowrap">
		<?php echo $AppUI->_('Projects on Hold'); ?>
	</td>
	<td>
		<input type=checkbox name="show_dyn_task" onclick="document.form_buttons.submit()" <?php echo $showDynTasks ? 'checked="checked"' : ""; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Dynamic Tasks'); ?>
	</td>
	<td>
		<input type=checkbox name="show_low_task" onclick="document.form_buttons.submit()" <?php echo $showLowTasks ? 'checked="checked"' : ""; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Low Priority Tasks'); ?>
	</td>
</tr>
</form>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<form name="form" method="post" action="index.php?<?php echo "m=$m&a=$a&date=$date";?>">
<tr>
	<th width='10'>&nbsp;</th>
	<th width='10'><?php echo $AppUI->_('Pin'); ?></th>
	<th width="20" colspan="2"><?php echo $AppUI->_('Progress');?></th>
	<th width="15" align="center"><?php echo $AppUI->_('P');?></th>
	<th colspan="2"><?php echo $AppUI->_('Task / Project');?></th>
	<th nowrap><?php echo $AppUI->_('Start Date');?></th>
	<th nowrap><?php echo $AppUI->_('Duration');?></th>
	<th nowrap><?php echo $AppUI->_('Finish Date');?></th>
	<th nowrap><?php echo $AppUI->_('Due In');?></th>
	<th width="0">&nbsp;</th>
</tr>

<?php

/*** Tasks listing ***/
$now = new CDate();
$df = $AppUI->getPref('SHDATEFORMAT');

foreach ($tasks as $task) {
	$sign = 1;
	$start = intval( @$task["task_start_date"] ) ? new CDate( $task["task_start_date"] ) : null;
	$end = intval( @$task["task_end_date"] ) ? new CDate( $task["task_end_date"] ) : null;
	
	if (!$end && $start) {
		$end = $start;
		$end->addSeconds( @$task["task_duration"]*$task["task_duration_type"]*SEC_HOUR );
	}

	if ($end && $now->after( $end )) {
		$sign = -1;
	} 

	$days = $end ? $now->dateDiff( $end ) * $sign : null;
	$task['task_due_in'] = $days;

	showtask($task, 0, false, true);

} ?>
<tr>
	<td colspan="9" align="right" height="30">
		<input type="submit" class="button" value="<?php echo $AppUI->_('update task');?>">
	</td>
	<td colspan="3" align="center">
<?php
foreach($priorities as $k => $v) {
	$options[$k] = $AppUI->_('set priority to ' . $v, UI_OUTPUT_RAW);
}
$options['c'] = $AppUI->_('mark as finished', UI_OUTPUT_RAW);
$options['d'] = $AppUI->_('delete', UI_OUTPUT_RAW);
echo arraySelect( $options, 'task_priority', 'size="1" class="text"', '0' );
?>
	</td>
</form>
</table>

<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Future Task');?></td>
	<td bgcolor="#e6eedd">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Started and on time');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffeebb">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Should have started');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Overdue');?></td>
</tr>
</table>
