<?php /* TASKS $Id$ */

/*
 * Gantt.php - by J. Christopher Pereira
 * TASKS $Id$
 */

error_reporting( E_ALL );	// this only for development testing

include ("{$AppUI->cfg['root_dir']}/lib/jpgraph/src/jpgraph.php");
include ("{$AppUI->cfg['root_dir']}/lib/jpgraph/src/jpgraph_gantt.php");

$project_id = defVal( @$_REQUEST['project_id'], 0 );
$f = defVal( @$_REQUEST['f'], 0 );
global $showLabels;
global $showWork;
global $locale_char_set;

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name
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
tasks.task_id, task_parent, task_name, task_start_date, task_end_date, task_duration, task_duration_type,
task_priority, task_percent_complete, task_order, task_project, task_milestone,
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

$tsql = "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_start_date";
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
		if($row["task_duration"]) {
			$row["task_end_date"] = db_unix2dateTime ( db_dateTime2unix( $row["task_start_date"] ) + SECONDS_PER_DAY * convert2days( $row["task_duration"], $row["task_duration_type"] ) );
		} else {
			$row["task_end_date"] = "";
		}
	}
		
	$projects[$row['task_project']]['tasks'][] = $row;
}

$width = dPgetParam( $_GET, 'width', 600 );
$start_date = dPgetParam( $_GET, 'start_date', 0 );
$end_date = dPgetParam( $_GET, 'end_date', 0 );

$count = 0;
$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY);

$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

$jpLocale = $AppUI->getConfig( 'jpLocale' );
if ($jpLocale) {
	$graph->scale->SetDateLocale( $jpLocale );
}

if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}

//$graph->scale->actinfo->SetFont(FF_ARIAL);
$graph->scale->actinfo->vgrid->SetColor('gray');
$graph->scale->actinfo->SetColor('darkgray');
if ($showWork=='1') {
	$graph->scale->actinfo->SetColTitles(array('Task name','Work','Start','Finish'),array(160,10, 70,70));
} else {
	$graph->scale->actinfo->SetColTitles(array('Task name','Duration','Start','Finish'),array(160,10, 70,70));
}

$graph->scale->tableTitle->Set($projects[$project_id]["project_name"]);
$graph->scale->tableTitle->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->scale->SetTableTitleBackground("#".$projects[$project_id]["project_color_identifier"]);
$graph->scale->tableTitle->Show(true);

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

$hide_task_groups = false;

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
for($i = 0; $i < count(@$gantt_arr); $i ++ ) {

	$a = $gantt_arr[$i][0];
	$level = $gantt_arr[$i][1];

	if($hide_task_groups) $level = 0;

	if ($locale_char_set=='utf-8') {
		$name = strlen( utf8_decode($a["task_name"]) ) > 25 ? substr( utf8_decode($a["task_name"]), 0, 22 ).'...' : utf8_decode($a["task_name"]) ;
	} else {
		//while using charset different than UTF-8 we need not to use utf8_deocde
		$name = strlen( $a["task_name"] ) > 25 ? substr( $a["task_name"], 0, 22 ).'...' : $a["task_name"] ;	
	}
	
	//using new jpGraph determines using Date object instead of string
	$start = $a["task_start_date"];
	$end = $a["task_end_date"];

	$end = new CDate($end);	
	$end->addDays(0);
	$end = $end->getDate();
	
	$start = new CDate($start);
	$start->addDays(0);
	$start = $start->getDate();
	
	$progress = $a["task_percent_complete"];
	$flags = ($a["task_milestone"]?"m":"");

	$cap = "";
	if(!$start || $start == "0000-00-00"){
		$start = !$end ? date("Y-m-d") : $end;
		$cap .= "(no start date)";
	}
	
	if(!$end) {
		$end = $start;
		$cap .= " (no end date)";
	} else {
		$cap = "";
	}
	
	$caption = "";
	if ($showLabels=='1') {
		$sql = "select ut.task_id, u.user_username, ut.perc_assignment from user_tasks ut, users u where u.user_id = ut.user_id and ut.task_id = ".$a["task_id"];
		$res = db_exec( $sql );
		while ($rw = db_fetch_row( $res )) {
			switch ($rw[2]) {
				case 100:
					$caption = $caption."".$rw[1].";";
					break;
				default:
					$caption = $caption."".$rw[1]."[".$rw[2]."%];";
					break;
			}
		}
		$caption = substr($caption, 0, strlen($caption)-1);
	}	
	
	if($flags == "m") {		
		$start = new CDate($start);
		$start->addDays(0);
		$s = $start->format("%Y-%m-%d");// 		
		$bar = new MileStone($row++, array($name, "", substr($s, 0, 10), substr($s, 0, 10)), $s, $s);		
		//caption of milestone shoud be date 
		if ($showLabels=='1') {			
			$caption = $start->format("%Y-%m-%d");		
		}
	} else {
		$type = $a["task_duration_type"];
		$dur = $a["task_duration"];
		if ($type == 24) {
			$dur *= $dPconfig['daily_working_hours'];
		} 
		
		if ($showWork=='1') {
			$work_hours = 0;
			$_days_sql = "SELECT ROUND(SUM(t.task_duration*u.perc_assignment/100),2) FROM tasks t left join user_tasks u on t.task_id = u.task_id WHERE t.task_id = ".$a['task_id']." AND t.task_duration_type = 24 AND t.task_milestone  ='0' AND t.task_dynamic = 0";
			$_hours_sql = "SELECT ROUND(SUM(t.task_duration*u.perc_assignment/100),2) FROM tasks t left join user_tasks u on t.task_id = u.task_id WHERE t.task_id = ".$a['task_id']." AND t.task_duration_type = 1 AND t.task_milestone  ='0' AND t.task_dynamic = 0";
			$work_hours = db_loadResult($_days_sql) * $dPconfig['daily_working_hours'];
			$work_hours += db_loadResult($_hours_sql);
			//due to the round above, we don't want to print decimals unless they really exist
			//$work_hours = rtrim($work_hours, "0");		
			$dur = $work_hours;

			/*
			$handle = fopen ( 'c:\a.txt', 'a+');
			fwrite($handle, $_days_sql);
			fclose($handle);
			*/
			
		}
		

		$dur .= " h";
		
		$bar = new GanttBar($row++, array($name, $dur, substr($start, 0, 10), substr($end, 0, 10)), $start, $end, $cap);
		$bar->progress->Set($progress/100);
	}
	//adding captions
	$bar->caption = new TextProperty($caption);
	$bar->caption->Align("left","center");		
	
	$sql = "SELECT dependencies_task_id FROM task_dependencies WHERE dependencies_req_task_id=" . $a["task_id"];
	$query = db_exec($sql);

	while($dep = db_fetch_assoc($query)) {
		// find row num of dependencies
		for($d = 0; $d < count($gantt_arr); $d++ ) {
			if($gantt_arr[$d][0]["task_id"] == $dep["dependencies_task_id"]) {
				$bar->SetConstrain($d, CONSTRAIN_ENDSTART);
			}
		}
	}
	$graph->Add($bar);
}

$graph->Stroke();
?>
