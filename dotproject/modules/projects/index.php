<?php  /* PROJECTS $Id$ */
$AppUI->savePlace();

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );

// Let's update project status!
if(isset($_GET["update_project_status"]) && isset($_GET["project_status"]) && isset($_GET["project_id"]) ){
	$projects_id = $_GET["project_id"]; // This must be an array

	foreach($projects_id as $project_id){
		$sql = "UPDATE projects
		        SET project_status = '{$_GET['project_status']}'
				WHERE project_id   = '$project_id'";
		db_exec( $sql );
		echo db_error();
	}
}
// End of project status update

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

if (isset( $_POST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', intval( $_POST['company_id'] ) );
}
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;

$company_prefix = 'company_';

if (isset( $_POST['department'] )) {
	$AppUI->setState( 'ProjIdxDepartment', $_POST['department'] );
	

	//if department is set, ignore the company_id field
	unset($company_id);
}
$department = $AppUI->getState( 'ProjIdxDepartment' ) !== NULL ? $AppUI->getState( 'ProjIdxDepartment' ) : $company_prefix.$AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the 
// $department variable, and populate the $company_id variable.
if(!(strpos($department, $company_prefix)===false)){
	$company_id = substr($department,strlen($company_prefix));
	$AppUI->setState( 'ProjIdxCompany', $company_id );
	unset($department);
}

if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';

// get any records denied from viewing
$obj = new CProject();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// Let's delete temproary tables
$sql = "DROP TABLE IF EXISTS tasks_sum, tasks_summy, tasks_critical, tasks_problems";
db_exec($sql);

// Task sum table
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003

$working_hours = $dPconfig['daily_working_hours'];

// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$sql = "
CREATE TEMPORARY TABLE tasks_sum
 SELECT task_project,
 COUNT(distinct task_id) AS total_tasks,
 SUM(task_duration * task_percent_complete * IF(task_duration_type = 24, ".$working_hours.", task_duration_type))/
		SUM(task_duration * IF(task_duration_type = 24, ".$working_hours.", task_duration_type)) AS project_percent_complete
 FROM tasks GROUP BY task_project
";

$tasks_sum = db_exec($sql);

// temporary My Tasks
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
$sql = "
CREATE TEMPORARY TABLE tasks_summy
 SELECT task_project, COUNT(distinct task_id) AS my_tasks
 FROM tasks
 WHERE task_owner = $AppUI->user_id GROUP BY task_project
";

$tasks_summy = db_exec($sql);

// temporary critical tasks
$sql = "
CREATE TEMPORARY TABLE tasks_critical
 SELECT task_project, task_id AS critical_task, task_end_date AS project_actual_end_date
 FROM tasks
 LEFT JOIN projects ON project_id = task_project
 GROUP BY task_project
 ORDER BY task_end_date DESC
";

$tasks_critical = db_exec($sql);

// temporary task problem logs
$sql = "
CREATE TEMPORARY TABLE tasks_problems
 SELECT task_project, task_log_problem
 FROM tasks
 LEFT JOIN task_log ON task_log_task = task_id
 WHERE task_log_problem > '0'
 GROUP BY task_project
";

$tasks_problems = db_exec($sql);

if(isset($department)){
	//If a department is specified, we want to display projects from the department, and all departments under that, so we need to build that list of departments
	$dept_ids = array();
	$sql = 'SELECT dept_id, dept_parent FROM departments ORDER BY dept_parent, dept_name';
	$rows = db_loadList( $sql );
	addDeptId($rows, $department);
	$dept_ids[] = $department;
}

// retrieve list of records
// modified for speed
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
// get the list of permitted companies
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
if(count($companies) == 0) $companies = array(0);

$sql = "
SELECT
	projects.project_id, project_active, project_status,
	project_color_identifier, project_name, project_description,
	project_start_date, project_end_date, project_color_identifier,
	project_company, company_name, project_status, project_priority,
        tasks_critical.critical_task, tasks_critical.project_actual_end_date,
        tasks_problems.task_log_problem,
	tasks_sum.total_tasks,
	tasks_summy.my_tasks,
	tasks_sum.project_percent_complete,
	user_username
FROM projects
LEFT JOIN companies ON projects.project_company = company_id
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks_critical ON projects.project_id = tasks_critical.task_project
LEFT JOIN tasks_problems ON projects.project_id = tasks_problems.task_project
LEFT JOIN tasks_sum ON projects.project_id = tasks_sum.task_project
LEFT JOIN tasks_summy ON projects.project_id = tasks_summy.task_project"
.(isset($department) ? "\nLEFT JOIN project_departments ON project_departments.project_id = projects.project_id" : '')."
WHERE 1 = 1"
.(count($deny) > 0 ? "\nAND projects.project_id NOT IN (" . implode( ',', $deny ) . ')' : '')
.(!isset($department)&&$company_id ? "\nAND projects.project_company = '$company_id'" : "\nAND projects.project_company IN (" . implode(',', array_keys($companies)) . ")" )
.(isset($department) ? "\nAND project_departments.department_id in ( ".implode(',',$dept_ids)." )" : '')
."
GROUP BY projects.project_id
ORDER BY $orderby
";
global $projects;
$projects = db_loadList( $sql );

// get the list of permitted companies
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

//get list of all departments, filtered by the list of permitted companies.
$sql = "
SELECT company_id, company_name, departments.* 
FROM companies
	LEFT JOIN departments ON companies.company_id = departments.dept_company
WHERE company_id in( ".implode(',',array_keys($companies))." )
ORDER BY company_name,dept_parent,dept_name
";
//echo "<pre>$sql</pre>";
$rows = db_loadList( $sql, NULL );

//display the select list
$buffer = '<select name="department" onChange="document.pickCompany.submit()" class="text">';
$buffer .= '<option value="company_0" style="font-weight:bold;">'.$AppUI->_('All').'</option>'."\n";
$company = '';
foreach ($rows as $row) {
	if ($row["dept_parent"] == 0) {
		if($company!=$row['company_id']){
			$buffer .= '<option value="'.$company_prefix.$row['company_id'].'" style="font-weight:bold;"'.($company_id==$row['company_id']?'selected="selected"':'').'>'.$row['company_name'].'</option>'."\n";
			$company=$row['company_id'];
		}
		if($row["dept_parent"]!=null){
			showchilddept( $row );
			findchilddept( $rows, $row["dept_id"] );
		}
	}
}
$buffer .= '</select>';

// setup the title block
$titleBlock = new CTitleBlock( 'Projects', 'applet3-48.png', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Company') . '/' . $AppUI->_('Division') . ':');
$titleBlock->addCell( $buffer, '', '<form action="?m=projects" method="post" name="pickCompany">', '</form>');
$titleBlock->addCell();
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new project').'">', '',
		'<form action="?m=projects&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

$project_types = dPgetSysVal("ProjectStatus");

$active = 0;
$complete = 0;
$archive = 0;
$proposed = 0;

foreach($project_types as $key=>$value)
{
        $counter[$key] = 0;
	if (is_array($projects)) {
		foreach ($projects as $p)
			if ($p['project_status'] == $key)
				++$counter[$key];
	}
                
        $project_types[$key] .= ' (' . $counter[$key] . ')';
}


if (is_array($projects)) {
        foreach ($projects as $p)
        {
                if ($p['project_active'] > 0 && $p['project_status'] == 3)
                        ++$active;
                else if ($p['project_active'] > 0 && $p['project_status'] == 5)
                        ++$complete;
                else if ($p['project_active'] < 1)
                        ++$archive;
                else
                        ++$proposed;
        }
}

$fixed_project_type_file = array(
        $AppUI->_('In Progress') . ' (' . $active . ')' => "vw_idx_active",
        $AppUI->_('Complete') . ' (' . $complete . ')'    => "vw_idx_complete",
        $AppUI->_('Archived') . ' (' . $archive . ')'    => "vw_idx_archived");
// we need to manually add Archived project type because this status is defined by 
// other field (Active) in the project table, not project_status
$project_types[] = $AppUI->_('Archived') . ' (' . $archive . ')';

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
$tabBox = new CTabBox( "?m=projects&orderby=$orderby", "{$dPconfig['root_dir']}/modules/projects/", $tab );
if ( $tabBox->isTabbed() ) {
	// This will overwrited the initial tab, so we need to add that separately.
	if (isset($project_types[0]))
		$project_types[] = $project_types[0];
	$project_types[0] = $AppUI->_('All Projects') . ' (' . count($projects) . ')';
}

/**
* Now, we will figure out which vw_idx file are available
* for each project type using the $fixed_project_type_file array 
*/
$project_type_file = array();

foreach($project_types as $project_type){
	$project_type = trim($project_type);
	if(isset($fixed_project_type_file[$project_type])){
		$project_file_type[$project_type] = $fixed_project_type_file[$project_type];
	} else { // if there is no fixed vw_idx file, we will use vw_idx_proposed
		$project_file_type[$project_type] = "vw_idx_proposed";
	}
}

// tabbed information boxes
foreach($project_types as $project_type) {
	$tabBox->add($project_file_type[$project_type], $project_type);
}
$min_view = true;
$tabBox->add("viewgantt", "Gantt");
$tabBox->show();

//writes out a single <option> element for display of departments
function showchilddept( &$a, $level=1 ) {
	Global $buffer, $department;
	$s = '<option value="'.$a["dept_id"].'"'.(isset($department)&&$department==$a["dept_id"]?'selected="selected"':'').'>';

	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '';
		} else {
			$s .= '&nbsp;&nbsp;';
		}
	}

	$s .= '&nbsp;&nbsp;'.$a["dept_name"]."</option>\n";
	$buffer .= $s;

//	echo $s;
}

//recursive function to display children departments.
function findchilddept( &$tarr, $parent, $level=1 ){
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["dept_parent"] == $parent && $tarr[$x]["dept_parent"] != $tarr[$x]["dept_id"]){
			showchilddept( $tarr[$x], $level );
			findchilddept( $tarr, $tarr[$x]["dept_id"], $level);
		}
	}
}

function addDeptId($dataset, $parent){
	Global $dept_ids;
	foreach ($dataset as $data){
		if($data['dept_parent']==$parent){
			$dept_ids[] = $data['dept_id'];
			addDeptId($dataset, $data['dept_id']);
		}
	}
}

?>
