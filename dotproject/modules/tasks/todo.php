<?php /* TASKS $Id$ */

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
}
$showArcProjs = $AppUI->getState( 'TaskDayShowArc' ) !== NULL ? $AppUI->getState( 'TaskDayShowArc' ) : 0;
$showLowTasks = $AppUI->getState( 'TaskDayShowLow' ) !== NULL ? $AppUI->getState( 'TaskDayShowLow' ) : 1;
$showHoldProjs = $AppUI->getState( 'TaskDayShowHold' ) !== NULL ? $AppUI->getState( 'TaskDayShowHold' ) : 0;
$showDynTasks = $AppUI->getState('TaskDayShowDyn') !== NULL ? $AppUI->getState( 'TaskDayShowDyn' ) : 0;

// if task priority set and items selected, do some work
$task_priority = dPgetParam( $_POST, 'task_priority', 99 );
$selected = dPgetParam( $_POST, 'selected', 0 );

if ($selected && count( $selected )) {
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

$sql = "
		 SELECT a.*,
		 project_name, project_id, project_color_identifier". 
//                 , parent.task_name as parent_name
"		 FROM projects, tasks AS a, user_tasks".
//		 LEFT JOIN tasks AS b ON a.task_id=b.task_parent and a.task_id != b.task_id
//  		 LEFT JOIN tasks AS parent ON a.task_parent = parent.task_id
"		 WHERE user_tasks.task_id = a.task_id".
//		 AND b.task_id IS NULL
"		 AND user_tasks.user_id = $user_id
		 AND ( a.task_percent_complete < 100  OR a.task_percent_complete IS NULL )" . 
//		 AND a.task_start_date != ''
"		 AND a.task_end_date != ''
		 AND a.task_status = '0' 
		 AND project_id = a.task_project" .  		
  (!$showArcProjs ? " AND project_active = 1" : "") .
  (!$showLowTasks ? " AND a.task_priority >= 0" : "") .  
  (!$showHoldProjs ? " AND project_status != $project_on_hold_status" : "") .
  (!$showDynTasks ? " AND a.task_dynamic = 0": "");

if (count($allowedTasks))
	$sql .= " AND " . implode(" AND ", $allowedTasks);

if (count($allowedProjects))
	$sql .= " AND " . implode(" AND ", $allowedProjects);

$sql .=   " GROUP BY a.task_id
	ORDER BY a.task_end_date, task_priority DESC
";
//echo "<pre>$sql</pre>";
$tasks = db_loadList( $sql );


$priorities = array(
	'1' => 'high',
	'0' => 'normal',
        '-1' => 'low'
);

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
	$style = '';
	$sign = 1;
	$start = intval( @$task["task_start_date"] ) ? new CDate( $task["task_start_date"] ) : null;
	$end = intval( @$task["task_end_date"] ) ? new CDate( $task["task_end_date"] ) : null;
	
	if (!$end && $start) {
		$end = $start;
		$end->addSeconds( @$task["task_duration"]*$task["task_duration_type"]*SEC_HOUR );
	}

	if ($start && $now->after( $start ) && $task["task_percent_complete"] == 0) {
		$style = 'background-color:#ffeebb';
	} else if ($now->after( $start )) {
		$style = 'background-color:#e6eedd';
	}

	if ($end && $now->after( $end )) {
		$sign = -1;
		$style = 'background-color:#cc6666;color:#ffffff';
	} 

	$days = $end ? $now->dateDiff( $end ) * $sign : null;

?>
<tr>
	<td>
<?php if ($canEdit) { ?>
		<a href="./index.php?m=tasks&a=addedit&task_id=<?php echo $task["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>
<?php } ?>
	</td>
	<td align="right">
		<?php echo intval($task["task_percent_complete"]);?>%
	</td>

	<td>
<?php if ($task["task_priority"] < 0 ) {
	echo "<img src='./images/icons/priority-". -$task["task_priority"] .".gif' width=13 height=16>";
} else if ($task["task_priority"] > 0) {
	echo "<img src='./images/icons/priority+" . $task["task_priority"] .".gif' width=13 height=16>";
}?>
	</td>

	<td width="50%">
		<a href="./index.php?m=tasks&a=view&task_id=<?php echo $task["task_id"];?>" title="<?php echo ( isset($task['parent_name']) ? '*** ' . $AppUI->_('Parent Task') . " ***\n" . htmlspecialchars($task['parent_name'], ENT_QUOTES) . "\n\n" : '' ) . '*** ' . $AppUI->_('Description') . " ***\n" . htmlspecialchars($task['task_description'], ENT_QUOTES) ?>"><?php echo htmlspecialchars($task["task_name"], ENT_QUOTES);?></a>
	</td>
	<td width="50%">
		<a href="./index.php?m=projects&a=view&project_id=<?php echo $task["project_id"];?>">
			<span style="padding:2px;background-color:#<?php echo $task['project_color_identifier'];?>;color:<?php echo bestColor( $task["project_color_identifier"] );?>"><?php echo $task["project_name"];?></span>
		</a>
	</td>
	<td nowrap style="<?php echo $style;?>"><?php if($start)echo $AppUI->_($start->getDayName(1)).'&nbsp;'.$start->format( $df );?></td>
	<td style="<?php echo $style;?>">
<?php
	echo $task['task_duration'] . ' ' . $AppUI->_( $durnTypes[$task['task_duration_type']] );
?>
	</td>

	<td nowrap style="<?php echo $style;?>"><?php echo $end ? $AppUI->_($end->getDayName(1)).'&nbsp;'.$end->format( $df ) : "";?></td>

	<td nowrap align="right" style="<?php echo $style;?>">
		<?php echo $days; ?>
	</td>
	<td>
		<input type=checkbox name="selected[]" value="<?php echo $task["task_id"] ?>">
	</td>
</tr>
<?php } ?>
<tr>
	<td colspan="7" align="right" height="30">
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
