<?php /* TASKS $Id$ */

include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_gantt.php");

global $company_id, $dept_ids, $department, $locale_char_set, $proFilter, $projectStatus, $showInactive, $showLabels;

$projectStatus = dPgetSysVal( 'ProjectStatus' );
$projectStatus = arrayMerge( array( '-2' => $AppUI->_('All w/o in progress')), $projectStatus);
if ($proFilter == '-1'){
        $filter1 = '';
} elseif ($proFilter == '-2'){
        //$proFilter = '-1';
        $filter1 = " AND project_status != 3 ";
} else {
        $filter1 = " AND project_status = $proFilter ";
}
if ($company_id != 0) {
        $filter1 .= " AND project_company = $company_id ";
}
//$filter1 = ($proFilter == '-1') ? '' : " AND project_status = $proFilter ";
$filter1 .= ($showInactive == '1') ? '' : " AND project_active <> 0 ";

// pull valid projects and their percent complete information
$sql = "
SELECT DISTINCT project_id, project_color_identifier, project_name, project_start_date, project_end_date, t1.task_end_date AS project_actual_end_date,
SUM(task_duration*task_duration_type*task_percent_complete)/sum(task_duration*task_duration_type) as project_percent_complete,
project_status, project_active
FROM permissions, projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)".
        $filter1
."
GROUP BY project_id
ORDER BY project_name, task_end_date DESC
";
// echo "<pre>$psql</pre>";
$projects = db_loadList( $sql );

$width      = dPgetParam( $_GET, 'width', 600 );
$start_date = dPgetParam( $_GET, 'start_date', 0 );
$end_date   = dPgetParam( $_GET, 'end_date', 0 );

$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);

$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

$jpLocale = dPgetConfig( 'jpLocale' );
if ($jpLocale) {
	$graph->scale->SetDateLocale( $jpLocale );
}

if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}

//$graph->scale->actinfo->SetFont(FF_ARIAL);
$graph->scale->actinfo->vgrid->SetColor('gray');
$graph->scale->actinfo->SetColor('darkgray');
$graph->scale->actinfo->SetColTitles(array( $AppUI->_('Project name'), $AppUI->_('Start Date'), $AppUI->_('Finish'), $AppUI->_('Actual End')),array(160,10, 70,70));


$tableTitle = ($proFilter == '-1') ? $AppUI->_('All Projects') : $projectStatus[$proFilter];
$graph->scale->tableTitle->Set($tableTitle);

// Use TTF font if it exists
// try commenting out the following two lines if gantt charts do not display
if (is_file( TTF_DIR."arialbd.ttf" ))
	$graph->scale->tableTitle->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->scale->SetTableTitleBackground("#eeeeee");
$graph->scale->tableTitle->Show(true);

//This kludgy function echos children tasks as threads

$row = 0;

if(sizeof($projects) == 0) {
 $d = new CDate();
 $bar = new GanttBar($row++, array(' '.$AppUI->_('No projects found'),  ' ', ' ', ' '), $d->getDate(), $d->getDate(), ' ', 0.6);
 $bar->title->SetCOlor('red');
 $graph->Add($bar);
}

foreach($projects as $p) {

	if ( $locale_char_set=='utf-8' && function_exists("utf_decode") ) {
		$name = strlen( utf8_decode($p["project_name"]) ) > 25 ? substr( utf8_decode($p["project_name"]), 0, 22 ).'...' : utf8_decode($p["project_name"]) ;
	} else {
		//while using charset different than UTF-8 we need not to use utf8_deocde
		$name = strlen( $p["project_name"] ) > 25 ? substr( $p["project_name"], 0, 22 ).'...' : $p["project_name"] ;
	}

	//using new jpGraph determines using Date object instead of string
	$start = ($p["project_start_date"] > "0000-00-00 00:00:00") ? $p["project_start_date"] : date("Y-m-d H:i:s");
	$end_date   = $p["project_end_date"];
        $actual_end = $p["project_actual_end_date"] ? $p["project_actual_end_date"] : " ";


	$end_date = new CDate($end_date);
//	$end->addDays(0);
	$end = $end_date->getDate();

	$start = new CDate($start);
//	$start->addDays(0);
	$start = $start->getDate();

	$progress = $p['project_percent_complete'];

	$caption = "";
	if(!$start || $start == "0000-00-00"){
		$start = !$end ? date("Y-m-d") : $end;
		$caption .= "(no start date)";
	}

	if(!$end) {
		$end = $start;
		$caption .= " (no end date)";
	} else {
		$cap = "";
	}

        if ($showLabels){
                $caption .= $projectStatus[$p['project_status']].", ";
                $caption .= $p['project_active'] <> 0 ? $AppUI->_('active') : $AppUI->_('inactive');
        }

        $bar = new GanttBar($row++, array($name, substr($start, 0, 10), substr($end, 0, 10), substr($actual_end, 0, 10)), $start, $actual_end, $cap, 0.6);
        $bar->progress->Set($progress/100);

        $bar->title->SetFont(FF_FONT1,FS_NORMAL,10);
        $bar->SetFillColor("#".$p['project_color_identifier']);
        $bar->SetPattern(BAND_SOLID,"#".$p['project_color_identifier']);

	//adding captions
	$bar->caption = new TextProperty($caption);
	$bar->caption->Align("left","center");

        // gray out templates, completes, on ice, on hold
        if ($p['project_status'] != '3' || $p['project_active'] == '0') {
                $bar->caption->SetColor('darkgray');
                $bar->title->SetColor('darkgray');
                $bar->SetColor('darkgray');
                $bar->SetFillColor('gray');
                //$bar->SetPattern(BAND_SOLID,'gray');
                $bar->progress->SetFillColor('darkgray');
                $bar->progress->SetPattern(BAND_SOLID,'darkgray',98);
        }

	$graph->Add($bar);
}

$graph->Stroke();
?>