<?php

/*
 * TODO:
 * - add task info showing also parent tasks
 */

$project_id = isset( $_GET['project_id'] ) ? $_GET['project_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->rededirect( 'm=help&a=access_denied' );
}

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

// query my sub-tasks

$sql = "
		 SELECT a.*,
		 project_name, project_id, project_color_identifier
		 FROM projects, tasks AS a, user_tasks
		 LEFT JOIN tasks AS b ON a.task_id=b.task_parent and a.task_id != b.task_id
		 WHERE user_tasks.task_id = a.task_id
		 AND b.task_id IS NULL
		 AND user_tasks.user_id = $AppUI->user_id
		 AND a.task_precent_complete != 100
		 AND project_id = a.task_project" .
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

$crumbs = array();
$crumbs["?m=tasks"] = "tasks list";
?>
<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0" width="44" height="38"></td>
	<td nowrap width="100%"><h1>My Tasks To Do</h1></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<form name="form_buttons" method="post">		
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right" width="100%">
<input type=checkbox name="show_low_tasks" <?php echo @$_POST["show_low_tasks"] ? "checked" : "" ?> onclick='submit()'>show low priority tasks
	</td>
</tr>
</form>		
</table>

<table width="98%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<form name="form" method="post">
<tr>
	<th width="10">id</th>
	<th width="20">work</th>
	<th width="15" align="center">p</th>
	<th colspan="2">task / project</th>
	<th nowrap>start date</th>
	<th nowrap>duration&nbsp;&nbsp;</th>
	<th nowrap>finish date</th>
	<th nowrap>due in</th>
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
	if ($days < 0 && $a["task_precent_complete"] == 0) {
		$style = 'background-color:#FFeebb';
	}
	if ($end && $end->isValid()) {
		$days = $now->daysTo( $end );
		if ($days < 0) {
			$style = 'background-color:#CC6666;color:#ffffff';
		} else if ($now->daysTo( $start ) < 0) {
			$style = 'background-color:#e6eedd';
		}
		$end->setFormat( $date_format );
	}
?>
<tr>
	<td>
		<a href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>
	</td>

	<td align="right">
		<?php echo intval($a["task_precent_complete"]);?>%
	</td>

	<td>
<?php if ($a["task_priority"] < 0 ) {
	echo "<img src='./images/icons/low.gif' width=13 height=16>";
} else if ($a["task_priority"] > 0) {
	echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
}?>
	</td>

	<td width="50%">
		<a href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>"><?php echo $a["task_name"];?></a>
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
	<td colspan="6" align="right" height="30">update selected tasks priority</td>
	<td colspan="2" align="center">
		<input type="submit" class="button" value="update">
	</td>
	<td colspan="2" align="center">
<?php echo arraySelect( $priorities, 'task_priority', 'size="1" class="text"', '0' ); ?>
	</td>
</form>
</table>
Quick and Nasty Legend:<br />
clear - future task, green - started and on time, yellow - should have started, red - past due
