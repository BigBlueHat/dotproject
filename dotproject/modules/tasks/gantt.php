<?php

/*
 *
 * Gantt.php - by J. Christopher Pereira
 *
 */

include ("../../lib/jpgraph/src/jpgraph.php");
include ("../../lib/jpgraph/src/jpgraph_gantt.php");

require "../../includes/config.php";
require "../../includes/db_connect.php";
require "../../includes/main_functions.php";
require "../../includes/permissions.php";

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
        echo '<script language="javascript">
        window.location="./index.php?m=help&a=access_denied";
        </script>
';
}

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_precent_complete)/SUM(t1.task_duration) as project_precent_complete
FROM permissions, projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_active <> 0
	AND permission_user = $thisuser_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
GROUP BY project_id
ORDER BY project_name
";
//echo "<pre>$psql</pre>";
$prc = mysql_query( $psql );
echo mysql_error();
$pnums = mysql_num_rows( $prc );

$projects = array();
for ($x=0; $x < $pnums; $x++) {
	$z = mysql_fetch_array( $prc, MYSQL_ASSOC );
	$projects[$z["project_id"]] = $z;
}

// get any specifically denied tasks
$dsql = "
SELECT task_id
FROM tasks, permissions
WHERE permission_user = $thisuser_id
	AND permission_grant_on = 'tasks'
	AND permission_item = task_id
	AND permission_value = 0
";
$drc = mysql_query( $dsql );
echo mysql_error();
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
        $deny[] = $row[0];
}

// pull tasks

$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
task_priority, task_precent_complete, task_duration, task_order, task_project, task_milestone,
project_name
";

$from = "tasks";
$join = "LEFT JOIN projects ON project_id = task_project";
$where = "project_active <> 0".($project_id ? "\nAND task_project = $project_id" : '');

switch ($f) {
	case 'all':
		$where .= "\nAND task_status > -1";
		break;
	case 'myproj':
		$where .= "\nAND task_status > -1\n	AND project_owner = $thisuser_id";
		break;
	case 'mycomp':
		$where .= "\nAND task_status > -1\n	AND project_company = $thisuser_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $thisuser_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_status > -1
	AND task_project = projects.project_id
	AND user_tasks.user_id = $thisuser_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
}

$tsql .= "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_order";
##echo "<pre>$tsql</pre>".mysql_error();##

$ptrc = mysql_query( $tsql );
$nums = mysql_num_rows( $ptrc );
echo mysql_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$row = mysql_fetch_array( $ptrc, MYSQL_ASSOC );

        // calculate or set blank task_end_date if unset
        if($row["task_end_date"] == "0000-00-00 00:00:00") {
        	if($row["task_duration"] != 0) {
	        	$row["task_end_date"] = date("Y-m-d H:i:s", strtotime(substr($row["task_start_date"], 0, 10) . " +" . $row["task_duration"] . " hours"));
        	} else {
	        	$row["task_end_date"] = "";
	        }
        }	
	
	$projects[$row['task_project']]['tasks'][] = $row;
}

$count = 0;
$graph = new GanttGraph();
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY);
$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$row = 0;

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
	/* Add tasks to gantt chart */
	
	global $graph, $row;
	
	$name = $a["task_name"];
	$start = substr($a["task_start_date"], 0, 10);
	$end = substr($a["task_end_date"], 0, 10);
	$progress = $a["task_precent_complete"];
	$flags = ($a["task_milestone"]?"m":"");
	
	if(!$end) {
		$end = $start;
		$cap = "?";
	} else {
		$cap = "";
	}
	
//	echo "$name<br>$flags<br><br>";	

	if($flags == "m") {
		$bar = new MileStone($row++, $name, $start, $start);
	} else {
		$bar = new GanttBar($row++, str_repeat("   ", $level) . $name, $start, $end, $cap); 
		$bar->progress->Set($progress/100);
	}
	$graph->Add($bar);
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

reset($projects);
$p = &$projects[$project_id];
$tnums = count( $p['tasks'] );

for ($i=0; $i < $tnums; $i++) {
	$t = $p['tasks'][$i];
	if ($t["task_parent"] == $t["task_id"]) {
		showtask( $t );
		findchild( $p['tasks'], $t["task_id"] );
	}
}

$graph->Stroke(); 
?>
