<?php  /* PROJECTS $Id$ */
$AppUI->savePlace();

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );


$perms =& $AppUI->acl();

$companies = new CCompany();
$filters_selection = array(
  'project_owner' => $perms->getPermittedUsers('projects'),
  'project_status' => dPgetSysVal('ProjectStatus'),
  'project_type' => dPgetSysVal('ProjectType'),
  'project_company' => $companies->getAllowedRecords($AppUI->user_id, 'company_id, company_name', 'company_name'),
  'project_company_type' => dPgetSysVal('CompanyType')
);

// setup the title block
$titleBlock = new CTitleBlock( 'Projects', 'applet3-48.png', $m, "$m.$a" );
$search_string = $titleBlock->addSearchCell();
$filters = $titleBlock->addFiltersCell($filters_selection);

if ($canAuthor) {
	$titleBlock->addCell(
		'<form action="?m=projects&amp;a=addedit" method="post">
			<input type="submit" class="button" value="'.$AppUI->_('new project').'" />
		</form>', '',	'', '');
}
$titleBlock->show();

// Let's update project status!
if(isset($_GET["update_project_status"]) && isset($_GET["project_status"]) && isset($_GET["project_id"]) ){
	$projects_id = $_GET["project_id"]; // This must be an array

	foreach($projects_id as $project_id){
		$r  = new DBQuery;
		$r->addTable('projects');
		$r->addUpdate('project_status', "{$_GET['project_status']}");
		$r->addWhere('project_id   = '.$project_id);
		$r->exec();
		$r->clear();
	}
}
// End of project status update

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

$company_prefix = 'company_';

if (isset( $_POST['department'] )) {
	$AppUI->setState( 'ProjIdxDepartment', $_POST['department'] );
}
$department = $AppUI->getState( 'ProjIdxDepartment' ) !== NULL ? $AppUI->getState( 'ProjIdxDepartment' ) : $company_prefix.$AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the 
// $department variable, and populate the $company_id variable.
// TODO: Broken by generic filters (company_id not used) - how to fix?
if(!(strpos($department, $company_prefix)===false)){
	$company_id = substr($department,strlen($company_prefix));
	unset($department);
}

if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'ProjIdxOrderDir' ) ? ($AppUI->getState( 'ProjIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';    
    $AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'ProjIdxOrderDir', $orderdir);
}
$orderby  = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';
$orderdir = $AppUI->getState( 'ProjIdxOrderDir' ) ? $AppUI->getState( 'ProjIdxOrderDir' ) : 'asc';
// get any records denied from viewing
$obj = new CProject();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// collect the full projects list data via function in projects.class.php
projects_list_data();

$project_types = dPgetSysVal("ProjectStatus");

$complete = 0;
$archive = 0;
$proposed = 0;

foreach($project_types as $key=>$value)
{
        $counter[$key] = 0;
	if (is_array($projects)) {
		foreach ($projects as $p) {
			if ($p['project_status'] == $key) {
				++$counter[$key];
			}
		}
	}
                
        $project_types[$key] = $AppUI->_($project_types[$key], UI_OUTPUT_RAW) . ' (' . $counter[$key] . ')';
}


if (is_array($projects)) {
  foreach ($projects as $p) {
    if ($p['project_status'] == 3) {
      ++$active;
    } else if ($p['project_status'] == 5) {
      ++$complete;
    } else {
    	++$proposed;
    }
  }
}

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
$tabBox = new CTabBox( "?m=projects", "{$dPconfig['root_dir']}/modules/projects/", $tab );


// tabbed information boxes
$tabBox->add( 'vw_idx_list', $AppUI->_('All', UI_OUTPUT_RAW). ' (' . count($projects) . ')' , true,  1000);
foreach($project_types as $key=>$project_type) {
	$tabBox->add('vw_idx_list', $project_type, true, $key);
}
$min_view = true;
$tabBox->add("viewgantt", "Gantt", true, 'gantt');
$tabBox->show();
?>
