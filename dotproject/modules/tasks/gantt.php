<?php
/*
 *
 * Gantt.php - by J. Christopher Pereira
 *
 */

 /*
 	TODO:
 		- task groups start_date = min(children_start_date), end_date = max(children_end_date)
 		- show dependencies (not implemented in jpgraph)
 */
require_once( "../../includes/config.php" );
require_once( "$root_dir/includes/db_connect.php" );
require_once( "$root_dir/classdefs/ui.php" );

include ("$root_dir/lib/jpgraph/src/jpgraph.php");
include ("$root_dir/lib/jpgraph/src/jpgraph_gantt.php");
include ("$root_dir/includes/main_functions.php");
include ("$root_dir/functions/tasks_func.php");

$gantt_arr = array();

// START: from index.php


session_name( 'dotproject' );
session_start();
session_register( 'AppUI' );

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

if (!isset($HTTP_SESSION_VARS['AppUI']) || isset($HTTP_GET_VARS['logout'])) {
	$HTTP_SESSION_VARS['AppUI'] = new CAppUI;
}
$AppUI =& $HTTP_SESSION_VARS['AppUI'];

if ($AppUI->doLogin()) {
	session_unset();
	session_destroy();
	include "./includes/login.php";
	exit;
}

// END: from index.php

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_precent_complete)/SUM(t1.task_duration) as project_precent_complete
FROM permissions, projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_active <> 0
	AND permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
GROUP BY project_id
ORDER BY project_name
";
// echo "<pre>$psql</pre>";
$prc = db_exec( $psql );
echo db_error();
$pnums = db_num_rows( $prc );

$projects = array();
for ($x=0; $x < $pnums; $x++) {
	$z = db_fetch_assoc( $prc );
	$projects[$z["project_id"]] = $z;
}

// get any specifically denied tasks
$dsql = "
SELECT task_id
FROM tasks, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'tasks'
	AND permission_item = task_id
	AND permission_value = 0
";
$drc = db_exec( $dsql );
echo db_error();
$deny = array();
while ($row = db_fetch_row( $drc )) {
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

$tsql .= "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_order";
##echo "<pre>$tsql</pre>".mysql_error();##

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );

        // calculate or set blank task_end_date if unset
        if($row["task_end_date"] == "0000-00-00 00:00:00") {
        	$row["task_end_date"] = get_end_date($row["task_start_date"], $row["task_duration"]);
        }

	$projects[$row['task_project']]['tasks'][] = $row;
}

$count = 0;
$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY);
$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
//$graph->scale->SetDateLocale($AppUI->user_locale);
if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
	/* Add tasks to gantt chart */

	global $gantt_arr;

	$gantt_arr[] = array($a, $level);

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

// $hide_task_groups = true;

if($hide_task_groups) {
	for($i = 0; $i < count($gantt_arr); $i ++ ) {
		// remove task groups
		if($i != count($gantt_arr)-1 && $gantt_arr[$i + 1][1] > $gantt_arr[$i][1]) {
			// it's not a leaf => remove
			array_splice($gantt_arr, $i, 1);
			continue;
		}
	}
}

$row = 0;
for($i = 0; $i < count($gantt_arr); $i ++ ) {

	$a = $gantt_arr[$i][0];
	$level = $gantt_arr[$i][1];

	if($hide_task_groups) $level = 0;

	$name = strlen( $a["task_name"] ) > 25 ? substr( $a["task_name"], 0, 22 ).'...' : $a["task_name"] ;
	$start = substr($a["task_start_date"], 0, 10);
	$end = substr($a["task_end_date"], 0, 10);
	$progress = $a["task_precent_complete"];
	$flags = ($a["task_milestone"]?"m":"");

	if(!$end) {
		$end = $start;
		$cap = " (no end date)";
	} else {
		$cap = "";
	}

	if($flags == "m") {
		$bar = new MileStone($row++, $name, $start, $start);
	} else {
		$bar = new GanttBar($row++, str_repeat("   ", $level) . $name, $start, $end, $cap);
		$bar->progress->Set($progress/100);

		$sql = "select dependencies_task_id from task_dependencies where dependencies_req_task_id=" . $a["task_id"];
		$query = db_exec($sql);

		while($dep = db_fetch_assoc($query)) {
			// find row num of dependencies
			for($d = 0; $d < count($gantt_arr); $d++ ) {
				if($gantt_arr[$d][0]["task_id"] == $dep["dependencies_task_id"]) {
					$bar->SetConstrain($d, CONSTRAIN_ENDSTART);
				}
			}
		}
	}

	$graph->Add($bar);
}

$graph->Stroke();
?>
