<?php /* TASKS $Id$ */

$project_id = isset( $_GET['project_id'] ) ? $_GET['project_id'] : 0;

// check permissions
$canEdit = !getDenyEdit( $m );

// if task priority set and items selected, do some work
$task_priority = isset( $_POST['task_priority'] ) ? $_POST['task_priority'] : 99;
$selected = isset( $_POST['selected'] ) ? $_POST['selected'] : 0;

if ($task_priority > -2 && $task_priority < 2 && count( $selected )) {
	foreach ($selected as $key => $val) {
		$sql = "UPDATE tasks SET task_priority=$task_priority WHERE task_id=$val";
		db_exec( $sql );
		echo db_error();
	}
	$AppUI->redirect( 'm=tasks&a=todo' );
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
  (!@$_POST["showArchivedProjects"] ? " AND project_active = 1" : "") .
  (!@$_POST["show_low_tasks"] ? " AND a.task_priority >= 0" : "") .  
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


$titleBlock = new CTitleBlock( 'My Tasks To Do', 'tasks.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
$titleBlock->addCrumbRight(
	'<input type=checkbox name="showArchivedProjects" ' . (@$_POST["showArchivedProjects"] ? "checked" : "")
	. 'onclick=\'submit()\'> '.$AppUI->_('show archived projects')
	. '<input type=checkbox name="show_low_tasks" ' . (@$_POST["show_low_tasks"] ? "checked" : "")
	. 'onclick=\'submit()\'> '.$AppUI->_('show low priority tasks'), '',
	'<form name="form_buttons" method="post">', '</form>' );
$titleBlock->show();
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<form name="form" method="post">
<tr>
	<th width="10"><?php echo $AppUI->_('Id');?></th>
	<th width="20"><?php echo $AppUI->_('Progress');?></th>
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
$date_format = $AppUI->getPref('SHDATEFORMAT');

foreach ($tasks as $a) {
	$style = '';
	
	$start = CDate::fromDateTime( $a["task_start_date"] );
	$start->setFormat( $date_format );

	$end = CDate::fromDateTime( $a["task_end_date"] );
	if ( $end && !$end->isValid() ) {
		if (@$a["task_duration"]) {
			$end = $start;
			$end->addHours( $a["task_duration"] );
		}
	}

	$days = $now->daysTo( $start );
	if ($days < 0 && $a["task_percent_complete"] == 0) {
		$style = 'background-color:#ffeebb';
	}
	if ($end && $end->isValid()) {
		$days = $now->daysTo( $end );
		if ($days < 0) {
			$style = 'background-color:#cc6666;color:#ffffff';
		} else if ($now->daysTo( $start ) < 0) {
			$style = 'background-color:#e6eedd';
		}
		$end->setFormat( $date_format );
	}
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
	<td nowrap style="<?php echo $style;?>"><?php echo $start->toString();?></td>
	<td style="<?php echo $style;?>">
	<?php if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
	       	// FIXME: this won't work for every language!
		$dt.="s";
	}
        echo ($dur!=0)?$dur . " " . $dt:"n/a";
	?>
	</td>

	<td nowrap style="<?php echo $style;?>"><?php echo ($end && $end->isValid()) ? $end->toString() : '-';?></td>

	<td nowrap align="right" style="<?php echo $style;?>">
		<?php echo $days; ?>
	</td>
	<td>
		<input type=checkbox name="selected[]" value="<?php echo $a["task_id"] ?>">
	</td>
</tr>
<?php } ?>
<tr>
	<td colspan="6" align="right" height="30"><?php echo $AppUI->_('update selected tasks priority');?></td>
	<td colspan="2" align="center">
		<input type="submit" class="button" value="<? echo $AppUI->_('update');?>">
	</td>
	<td colspan="2" align="center">
<?php echo arraySelect( $priorities, 'task_priority', 'size="1" class="text"', '0' ); ?>
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
