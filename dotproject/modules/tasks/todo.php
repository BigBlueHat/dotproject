<?php

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

// query my sub-tasks

$sql = "
SELECT a.*,
	project_name, project_id, project_color_identifier
FROM projects,tasks AS a, user_tasks
LEFT JOIN tasks AS b ON a.task_id=b.task_parent
WHERE user_tasks.task_id = a.task_id
	AND b.task_id IS NULL
	AND user_tasks.user_id = $AppUI->user_id
	AND a.task_precent_complete != 100
	AND project_id = a.task_project
ORDER BY a.task_start_date, task_priority DESC
";

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
	<td nowrap width="100%">
		<span class="title">My Tasks To Do</span>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right" width="100%"></td>
</tr>
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

foreach ($tasks as $a) {
	if($a["task_end_date"] == "0000-00-00 00:00:00") {
		$a["task_end_date"] = "";
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
			<span style="padding:2px;background-color:<?php echo $a['project_color_identifier'];?>;color:<?php echo bestColor( $a["project_color_identifier"] );?>"><?php echo $a["project_name"];?></span>
		</a>
	</td>
	<td nowrap><?php echo fromDate(substr($a["task_start_date"], 0, 10));?></td>
	<td>
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

	<td nowrap>
        <?php
        	if($a["task_end_date"]) {
        		echo fromDate(substr($a["task_end_date"], 0, 10));
        	} else {
        		echo "n/a";
        	}
        ?>
	</td>

	<td nowrap>
	<?php
		$start_date = time2YMD(dbDate2time($a["task_start_date"]));
		if ($a["task_duration"]) {
			$end_date = strtotime( get_end_date($start_date, $a["task_duration"]) );

			$days = floor(($end_date - time())/ 86400);
			if($days == 0) {
				echo "<font color=brown>today</font>";
			} else {
				if($days<0) echo "<font color=red>";
				echo "$days days";
				if($days<0) echo "</font>";
			}
		}
	?>
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
