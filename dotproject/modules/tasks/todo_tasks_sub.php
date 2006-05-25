<?php
global $showEditCheckbox, $tasks, $taskPriority;
GLOBAL $m, $a, $date, $other_users, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks, $showLowTasks, $showEmptyDate, $user_id;
$perms =& $AppUI->acl();
$canDelete = $perms->checkModuleItem($m, 'delete');
?>
<form name="form_buttons" method="post" action="index.php?<?php echo "m=$m&amp;a=$a&amp;date=$date";?>">
<input type="hidden" name="show_form" value="1" />

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr>
	<td width="50%">
	<?php
	if ($other_users) {
		$users = dPgetUsers();
		unset($users[0]);
		echo $AppUI->_("Show Todo for:").
		arraySelect($users, 'show_user_todo', ' onchange="document.form_buttons.submit();"', $user_id);
	} ?>
	</td>
	<td align="right" width="50%">
		<?php echo $AppUI->_('Show'); ?>:
	</td>
	<td>
		<input type="checkbox" name="show_pinned" onclick="document.form_buttons.submit()" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Pinned Only'); ?>
	</td>
	<td>
		<input type="checkbox" name="show_arc_proj" onclick="document.form_buttons.submit()" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Archived Projects'); ?>
	</td>
	<td>
		<input type="checkbox" name="show_hold_proj" onclick="document.form_buttons.submit()" <?php echo $showHoldProjs ? 'checked="checked"' : ''; ?> />
	</td>
    <td nowrap="nowrap">
		<?php echo $AppUI->_('Projects on Hold'); ?>
	</td>
	<td>
		<input type="checkbox" name="show_dyn_task" onclick="document.form_buttons.submit()" <?php echo $showDynTasks ? 'checked="checked"' : ''; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Dynamic Tasks'); ?>
	</td>
	<td>
		<input type="checkbox" name="show_low_task" onclick="document.form_buttons.submit()" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Low Priority Tasks'); ?>
	</td>
	<td>
		<input type="checkbox" name="show_empty_date" onclick="document.form_buttons.submit()" <?php echo $showEmptyDate ? 'checked="checked"' : ''; ?> />
	</td>
	<td nowrap="nowrap">
		<?php echo $AppUI->_('Empty Dates'); ?>
	</td>
</tr>
</table>
</form>

<form name="form" method="post" action="index.php?<?php echo "m=$m&amp;a=$a&amp;date=$date";?>">
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="10"><?php echo $AppUI->_('Pin'); ?></th>
	<th width="20" colspan="2"><?php echo $AppUI->_('Progress');?></th>
	<th width="15" align="center"><?php echo $AppUI->_('P');?></th>
	<th colspan="2"><?php echo $AppUI->_('Task / Project');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Duration');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Due In');?></th>
	<?php if (dPgetConfig('direct_edit_assignment')) { echo '<th width="0">&nbsp;</th>'; } ?>
</tr>

<?php

/*** Tasks listing ***/
$now = new CDate();
$df = $AppUI->getPref('SHDATEFORMAT');

foreach ($tasks as $task) {
	$sign = 1;
	$start = intval( @$task['task_start_date'] ) ? new CDate( $task['task_start_date'] ) : null;
	$end = intval( @$task['task_end_date'] ) ? new CDate( $task['task_end_date'] ) : null;
	
	if (!$end && $start) {
		$end = $start;
		$end->addSeconds( @$task['task_duration']*$task['task_duration_type']*SEC_HOUR );
	}

	if ($end && $now->after( $end ))
		$sign = -1;

	$days = $end ? $now->dateDiff( $end ) * $sign : null;
	$task['task_due_in'] = $days;

	$task['node_id'] = $task['task_id'];
	showtask($task, 0, false, true);
} 
if (dPgetConfig('direct_edit_assignment')) {
?>
<tr>
	<td colspan="9" align="right" height="30">
		<input type="submit" class="button" value="<?php echo $AppUI->_('update task');?>" />
	</td>
	<td colspan="3" align="center">
<?php
	foreach($taskPriority as $k => $v)
		$options[$k] = $AppUI->_('set priority to ' . $v, UI_OUTPUT_RAW);
	$options['c'] = $AppUI->_('mark as finished', UI_OUTPUT_RAW);
	if ($canDelete) 
		$options['d'] = $AppUI->_('delete', UI_OUTPUT_RAW);

	echo arraySelect( $options, 'task_priority', 'size="1" class="text"', '0' );
	echo '</td>
</tr>';
}
?>
</table>
</form>

<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Future Task');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CCECAC">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Started and on time');?></td>
	<td bgcolor="#FFDD88">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Should have started');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Overdue');?></td>
</tr>
</table>
