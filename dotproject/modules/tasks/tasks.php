<?php /* $Id$ */
GLOBAL $m, $a, $project_id, $f, $min_view, $query_string;
/*
	tasks.php

	This file contains common task list rendering code used by
	modules/tasks/index.php and modules/projects/vw_tasks.php

	External used variables:

	* $min_view: hide some elements when active (used in the vw_tasks.php)
	* $project_id
	* $f
	* $query_string
*/

if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

// process reordering actions

// TODO: requires to know the neworder

if(isset($movetask)) {
	if($movetask == "u")
	{
		/*
		// move up tasks with low order
		$sql = "update tasks set task_order = task_order - 1 where task_order < $order";
		db_exec($sql);
		echo db_error();

		// select tasks in same level as the task to be moved
		$sql = "select task_id, task_order from tasks where task_project = $task_project and task_order = $order order by task_order";
		$last_task_id = -1;
		$arr = db_exec($sql);
		while($row = db_fetch_assoc($arr)) {
			// scroll task
			db_exec("update tasks set task_order = task_order - 1 where task_id = " . $row["task_id"]);
			echo db_error();

			if($row["task_id"] == $task_id) {
				// we reached the task to be moved

				// move previous task down
				if($last_task_id != -1) {
					db_exec("update tasks set task_order = task_order + 1 where task_id = $last_task_id");
					echo db_error();
				}

				// stop scrolling
				break;
			}

			$last_task_id = $row["task_id"];
		}
		*/

		$sql = "SELECT task_id, task_order FROM tasks WHERE task_project = $task_project AND task_parent = task_id AND task_order <= $order AND task_id != $task_id ORDER BY task_order desc";
		$dsql = db_exec( $sql );
		if ($darr = db_fetch_assoc( $dsql )){
			$neworder = $darr["task_order"] - 1;

			$sql = "UPDATE tasks SET task_order = task_order -1 WHERE task_order <= $neworder";
			//echo $sql;
			db_exec($sql);
			echo db_error();

			$sql = "UPDATE tasks SET task_order = $neworder WHERE task_id = $task_id";
			//echo $sql;
			db_exec($sql);
			echo db_error();
		}
	} else if($movetask == "d") {
		$sql = "SELECT task_id, task_order FROM tasks WHERE task_project = $task_project AND task_parent = task_id and task_order >= $order AND task_id != $task_id ORDER BY task_order";
		$dsql = db_exec( $sql );
		if ($darr = db_fetch_assoc( $dsql )) {
			$neworder = $darr["task_order"] + 1;

			$sql = "update tasks set task_order = task_order +1 where task_order >= $neworder";
			//echo $sql;
			db_exec( $sql );

			$sql = "update tasks set task_order = $neworder where task_id = $task_id";
			//echo $sql;
			db_exec( $sql );
		}
	}
}

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_precent_complete)/SUM(t1.task_duration) as project_precent_complete
FROM permissions, projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
GROUP BY project_id
ORDER BY project_name
";
//echo "<pre>$psql</pre>";
$prc = db_exec( $psql );
echo db_error();

$projects = array();
while ($row = db_fetch_assoc( $prc )) {
	$projects[$row["project_id"]] = $row;
}

// get any specifically denied tasks
$sql = "
SELECT task_id, task_id
FROM tasks, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'tasks'
	AND permission_item = task_id
	AND permission_value = 0
";
$deny = db_loadHashList( $sql );

// pull tasks
$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
task_priority, task_precent_complete, task_duration, task_order, task_project
";

$from = "tasks";
$join = "LEFT JOIN projects ON project_id = task_project";
$where = $project_id ? "\ntask_project = $project_id" : 'project_active <> 0';

switch ($f) {
	case 'all':
		$where .= "\nAND task_status > -1";
		break;
	case 'myproj':
		$where .= "\nAND task_status > -1\n	AND project_owner = $AppUI->user_id";
		break;
	case 'mycomp':
		$where .= "\nAND task_status > -1\n	AND project_company = $AppUI->user_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_status > -1
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
}

$where .= count($deny) > 0 ? "\nAND tasks.task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

$tsql = "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_order";
##echo "<pre>$tsql</pre>".db_error();##

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array

for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	$projects[$row['task_project']]['tasks'][] = $row;
}

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
	global $AppUI, $done, $query_string;
	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$done[] = $a['task_id'];

	$start_date = $a["task_start_date"] ? new CDate( db_dateTime2unix( $a["task_start_date"] ) ) : null;
	$end_date = $a["task_end_date"] ? new CDate( db_dateTime2unix( $a["task_end_date"] ) ) : null;

	$s = '<tr>';
// edit icon
	$s .= '<td><a href="?m=tasks&a=addedit&task_id='.$a["task_id"].'"><img src="./images/icons/pencil.gif" alt="'.$AppUI->_( 'Edit Task' ).'" border="0" width="12" height="12"></a></td>';
// percent complete
	$s .= '<td align="right">'.intval( $a["task_precent_complete"] ).'%</td>';
// priority
	$s .= '<td>';
	if ($a["task_priority"] < 0 ) {
		$s .= "<img src='./images/icons/low.gif' width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		$s .= '<img src="./images/icons/' . $a["task_priority"] .'.gif" width=13 height=16>';
	}
	$s .= '</td>';
// dots
	$s .= '<td width="90%">';
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
// arrows
	$s .= '<img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow'.$a["task_id"].'">';
	$s .= '<map name="arrow'.$a["task_id"].'"><area coords="0,0,10,7" href="' . $query_string . '&task_project=' . $a["task_project"] . '&task_id=' . $a["task_id"] . '&order=' . $a["task_order"] . '&movetask=u">';
	$s .= '<area coords="0,8,10,14" href="'.$query_string . '&task_project=' . $a["task_project"] . '&task_id=' . $a["task_id"] . '&order=' . $a["task_order"] . '&movetask=d"></map>';
// name link
	$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id='.$a["task_id"].'">'.$a["task_name"].'</a></td>';
// start date
	$s .= '<td nowrap="nowrap">'.($start_date ? $start_date->toString( $df ) : '-').'</td>';
// duration
	$s .= '<td align="right">';
	if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
		// FIXME: this won't work for every language!
		$dt .= "s";
	}
	$s .= ($dur != 0) ? "$dur $dt" : "n/a";
	$s .= '</td>';
// end date
	$s .= '<td nowrap="nowrap">'.($end_date ? $end_date->toString( $df ) : '-').'</td>';

	$s .= '</tr>';

	echo $s;
}

function findchild( &$tarr, $parent, $level=0 ){
	GLOBAL $projects;
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
			showtask( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["task_id"], $level);
		}
	}
}

$crumbs = array();
$crumbs["?m=tasks&a=todo"] = "my todo";
?>

<?php if (!$min_view) { ?>
<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right" width="100%"></td>
</tr>
</table>
<?php } ?>

<table width="98%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="20"><?php echo $AppUI->_('Work');?></th>
	<th width="15" align="center">&nbsp;</th>
	<th width="200"><?php echo $AppUI->_('Task Name');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Duration');?>&nbsp;&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?></th>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );
foreach ($projects as $k => $p) {
	$tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
	if ($tnums) {
//echo '<pre>'; print_r($p); echo '</pre>';
		if (!$min_view) {
?>
<tr>
	<td>
		<a href="index.php?m=tasks&f=<?php echo $f;?>&project_id=<?php echo $project_id ? 0 : $k;?>">
			<img src="./images/icons/<?php echo $project_id ? 'expand.gif' : 'collapse.gif';?>" width="16" height="16" border="0" alt="<?php echo $project_id ? 'show other projects' : 'show only this project';?>">
		</a>
	</td>
	<td colspan="8">
		<table width="100%" border="0">
		<tr>
			<td nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
				<A href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<span style='color:<?php echo bestColor( @$p["project_color_identifier"] ); ?>;text-decoration:none;'><B><?php echo @$p["project_name"];?></b></span></a>
			</td>
			<td width="<?php echo (101 - intval(@$p["project_precent_complete"]));?>%">
				<?php echo (intval(@$p["project_precent_complete"]));?>%
			</td>
		</tr>
		</table>
</tr>
<?php
		}
		GLOBAL $done;
		$done = array();
		for ($i=0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			if ($t["task_parent"] == $t["task_id"]) {
				showtask( $t );
				findchild( $p['tasks'], $t["task_id"] );
			}
		}
// check that any 'orphaned' user tasks are also display
		for ($i=0; $i < $tnums; $i++) {
			if ( !in_array( $p['tasks'][$i]["task_id"], $done )) {
				showtask( $p['tasks'][$i], 1 );
			}
		}

		if($tnums && $AppUI->cfg['enable_gantt_charts'] && !$min_view) { ?>
		<tr>
			<td colspan="8" align=right>
				<input type="button" class=button value="see gant chart" onClick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';">
			</td>
		</tr>
		<?php }
	}
}
?>
</table>
