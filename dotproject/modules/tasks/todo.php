<?php /* TASKS $Id$ */

$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );
$date = intval( dPgetParam( $_GET, 'date', '' ) );

// check permissions
$canEdit = !getDenyEdit( $m );

// retrieve any state parameters
if (isset( $_POST['show_form'] )) {
	$AppUI->setState( 'TaskDayShowArc', dPgetParam( $_POST, 'show_arc_proj', 0 ) );
	$AppUI->setState( 'TaskDayShowLow', dPgetParam( $_POST, 'show_low_task', 0 ) );
}
$showArcProjs = $AppUI->getState( 'TaskDayShowArc' ) !== NULL ? $AppUI->getState( 'TaskDayShowArc' ) : 0;
$showLowTasks = $AppUI->getState( 'TaskDayShowLow' ) !== NULL ? $AppUI->getState( 'TaskDayShowLow' ) : 1;

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

// query my sub-tasks (ignoring task parents)

$sql = "
		 SELECT a.*,
		 project_name, project_id, project_color_identifier,
		 parent.task_name as parent_name
		 FROM projects, tasks AS a, user_tasks
		 LEFT JOIN tasks AS b ON a.task_id=b.task_parent and a.task_id != b.task_id
  		 LEFT JOIN tasks AS parent ON a.task_parent = parent.task_id
		 WHERE user_tasks.task_id = a.task_id
		 AND b.task_id IS NULL
		 AND user_tasks.user_id = $AppUI->user_id
		 AND a.task_percent_complete != 100
		 AND project_id = a.task_project" .
  (!$showArcProjs ? " AND project_active = 1" : "") .
  (!$showLowTasks ? " AND a.task_priority >= 0" : "") .  
  " GROUP BY a.task_id
	ORDER BY a.task_start_date, task_priority DESC
";
// echo "<pre>$sql</pre>";
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
	<td align="right" width="100%">
		<?php echo $AppUI->_('Show'); ?>:
	</td>
	<td>
		<input type=checkbox name="show_arc_proj" onclick="document.form_buttons.submit()" <?php echo $showArcProjs ? 'checked="checked"' : ""; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Archived Projects'); ?>
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

foreach ($tasks as $a) {
	$style = '';
	$sign = 1;
	
	$start = intval( @$a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$end = intval( @$a["task_end_date"] ) ? new CDate( $a["task_end_date"] ) : null;

	if (!$end) {
		$end = $start;
		$end->addSeconds( @$a["task_duration"]*$a["task_duration_type"]*SEC_HOUR );
	}

	if ($now->after( $start ) && $a["task_percent_complete"] == 0) {
		$style = 'background-color:#ffeebb';
	}

	if ($now->after( $end )) {
		$sign = -1;
		$style = 'background-color:#cc6666;color:#ffffff';
	} else if ($now->after( $start )) {
		$style = 'background-color:#e6eedd';
	}

	$days = $now->dateDiff( $end ) * $sign;

?>
<tr>
	<td>
<?php if ($canEdit) { ?>
		<a href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>
<?php } ?>
	</td>
	<td align="right">
		<?php echo intval($a["task_percent_complete"]);?>%
	</td>

	<td>
<?php if ($a["task_priority"] < 0 ) {
	echo "<img src='./images/icons/low.gif' width=13 height=16>";
} else if ($a["task_priority"] > 0) {
	echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
}?>
	</td>

	<td width="50%">
		<a href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>" title='<?php echo ( isset($a['parent_name']) ? '*** ' . $AppUI->_('Parent Task') . " ***\n" . htmlspecialchars($a['parent_name']) . "\n\n" : '' ) . '*** ' . $AppUI->_('Description') . " ***\n" . htmlspecialchars($a['task_description']) ?>'><?php echo $a["task_name"];?></a>
	</td>
	<td width="50%">
		<a href="./index.php?m=projects&a=view&project_id=<?php echo $a["project_id"];?>">
			<span style="padding:2px;background-color:#<?php echo $a['project_color_identifier'];?>;color:<?php echo bestColor( $a["project_color_identifier"] );?>"><?php echo $a["project_name"];?></span>
		</a>
	</td>
	<td nowrap style="<?php echo $style;?>"><?php echo $start->format( $df );?></td>
	<td style="<?php echo $style;?>">
<?php
	echo $a['task_duration'] . ' ' . $AppUI->_( $durnTypes[$a['task_duration_type']] );
?>
	</td>

	<td nowrap style="<?php echo $style;?>"><?php echo $end->format( $df );?></td>

	<td nowrap align="right" style="<?php echo $style;?>">
		<?php echo $days; ?>
	</td>
	<td>
		<input type=checkbox name="selected[]" value="<?php echo $a["task_id"] ?>">
	</td>
</tr>
<?php } ?>
<tr>
	<td colspan="7" align="right" height="30">
		<input type="submit" class="button" value="<? echo $AppUI->_('update task');?>">
	</td>
	<td colspan="3" align="center">
<?php
foreach($priorities as $k => $v) {
	$options[$k] = $AppUI->_('set priority to ' . $v);
}
$options['c'] = $AppUI->_('mark as finished');
$options['d'] = $AppUI->_('delete');
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
