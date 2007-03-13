<?php /* FILES $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $deny1, $canRead, $canEdit, $canAdmin, $tpl;
global $company_id, $project_id, $task_id, $showProject;

//require_once( DP_BASE_DIR . '/modules/files/index_table.lib.php');

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab				- file category
// $page			- actual page to show
// $xpg_pagesize	- max rows per page
// $xpg_min			- initial record in the SELECT LIMIT
// $xpg_totalrecs	- total rows selected
// $xpg_sqlrecs		- total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page	- next pagenumber
// $xpg_prev_page	- previous pagenumber
// $xpg_break		- stop showing page numbered list?
// $xpg_sqlcount	- SELECT for the COUNT total
// $xpg_sqlquery	- SELECT for the SELECT LIMIT
// $xpg_result		- pointer to results from SELECT LIMIT

$page = dPgetParam( $_GET, 'page', 1);
if ($showProject) {
	$tab = $AppUI->getState( 'FileIdxTab' ) !== NULL ? $AppUI->getState( 'FileIdxTab' ) : 0;
} else {
	$tab = 0;
}
if (!isset($project_id)) {
	$project_id = dPgetParam( $_REQUEST, 'project_id', 0);
}

$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records
include_once $AppUI->getModuleClass( 'projects' );
include_once $AppUI->getModuleClass( 'tasks' );
require_once $AppUI->getSystemClass( 'query' );
require_once $AppUI->getModuleClass( 'files' );

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$file_types = dPgetSysVal("FileType");
if ($tab <= 0) {
	$catsql = false;
} else {
	$catsql = 'file_category = ' . --$tab ;
}

// Fetch permissions once for all queries
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'file_task');

// SQL text for count the total recs from the selected option
$q = new DBQuery;
$q->addQuery('count(file_id)');
$q->addTable('files', 'f');
$q->addJoin('projects', 'p', 'p.project_id = file_project');
$q->addJoin('tasks', 't', 't.task_id = file_task');
if (count ($allowedProjects)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count ($allowedTasks)) {
	$q->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q->addWhere($catsql);
}
if ($company_id) {
	$q->addWhere("project_company = $company_id");
}
if ($project_id) {
	$q->addWhere("file_project = $project_id");
}
if ($task_id) {
	$q->addWhere("file_task = $task_id");
}
$q->addGroup('file_version_id');


// SETUP FOR FILE LIST
$q2 = new DBQuery;
$q2->addQuery('f.*'.
	', max(f.file_id) as latest_id'
	.', count(f.file_version) as file_versions, round(max(f.file_version),2) as file_lastversion');
$q2->addQuery('ff.*');
$q2->addTable('files', 'f');
$q2->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
$q2->addJoin('projects', 'p', 'p.project_id = file_project');
$q2->addJoin('tasks', 't', 't.task_id = file_task');
if (count ($allowedProjects)) {
	$q2->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count ($allowedTasks)) {
	$q2->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q2->addWhere($catsql);
}
if ($company_id) {
	$q2->addWhere("project_company = $company_id");
}
if ($project_id) {
	$q2->addWhere("file_project = $project_id");
}
if ($task_id) {
	$q2->addWhere("file_task = $task_id");
}
$q2->setLimit($xpg_pagesize, $xpg_min);
// Adding an Order by that is different to a group by can cause
// performance issues. It is far better to rearrange the group
// by to get the correct ordering.
$q2->addGroup('project_id');
$q2->addGroup('file_version_id DESC');


$q3 = new DBQuery;
$q3->addQuery('file_id, file_version, file_version_id, file_project, file_name, file_task, task_name, file_description, file_checkout, file_co_reason, u.user_username as file_owner, file_size, file_category, file_type, file_date, cu.user_username as co_user, project_name, project_color_identifier, project_owner, con.contact_first_name, con.contact_last_name, co.contact_first_name as co_contact_first_name, co.contact_last_name as co_contact_last_name ');
$q3->addQuery('ff.*');
$q3->addTable('files');
$q3->addJoin('users', 'u', 'u.user_id = file_owner');
$q3->addJoin('contacts', 'con', 'con.contact_id = u.user_contact');
$q3->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
$q3->addJoin('projects', 'p', 'p.project_id = file_project');
$q3->addJoin('tasks', 't', 't.task_id = file_task');
$q3->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
$q3->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');
if (count ($allowedProjects)) {
	$q3->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
}
if (count ($allowedTasks)) {
	$q3->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
}
if ($catsql) {
	$q3->addWhere($catsql);
}
if ($company_id) {
	$q3->addWhere("project_company = $company_id");
}
if ($project_id) {
	$q3->addWhere("file_project = $project_id");
}
if ($task_id) {
	$q3->addWhere("file_task = $task_id");
}

$files = array();
$file_versions = array();
if ($canRead) 
{
	$files = $q2->loadList();
	$file_versions = $q3->loadHashList('file_id');
}
// counts total recs from selection
$xpg_totalrecs = count($q->loadList());

// How many pages are we dealing with here ??
$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 1;

shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);

$fp=-1;
$file_date = new CDate();
$id = 0;
$file_rows_html = "";

foreach ($files as $file_row) {
	$tpl_row = new CTemplate();

		$latest_file = $file_versions[$file_row['latest_id']];
	$file_date = new CDate( $latest_file['file_date'] );

	if ($fp != $latest_file['file_project']) {
		if (!$latest_file['project_name']) {
			$latest_file['project_name'] = $AppUI->_('All Projects');
			$latest_file['project_color_identifier'] = 'f4efe3';
		}
		if ($showProject) {
			$style = "background-color:#$latest_file[project_color_identifier];color:" . bestColor($latest_file["project_color_identifier"]);
			$s = '
<tr>
	<td colspan="12" style="border: outset 2px #eeeeee;' . $style . '">
		<a href="?m=projects&a=view&project_id=' . $latest_file['file_project'] . '">
			<span style="' . $style . '">' . $latest_file['project_name'] . '</span>
		</a>
	</td>
</tr>';

			$tpl_row->assign('showProject_s', $s);	
		}
	}
	$fp = $latest_file['file_project'];
	if ($canEdit && ( empty($latest_file['file_checkout']) || ( $latest_file['file_checkout'] == 'final' && ($canEdit|| $latest_file['project_owner'] == $AppUI->user_id) ))) {
		$file_edit_html = "\n".'<a href="./index.php?m=files&amp;a=addedit&amp;file_id=' . $latest_file['file_id'] . '">';
		$file_edit_html.= dPshowImage( './modules/files/images/kedit.png', '16', '16' );
		$file_edit_html.="\n</a>";

		$tpl_row->assign('edit_link', $file_edit_html);
	}

	if ($canEdit && empty($latest_file['file_checkout']) )
		$checkinout_html = '<a href="?m=files&amp;a=co&amp;file_id='.$latest_file['file_id'].'">'.dPshowImage( './modules/files/images/up.png', '20', '20' ).'</a>';
	else if ($latest_file['file_checkout'] == $AppUI->user_id)
		$checkinout_html = '<a href="?m=files&amp;a=addedit&amp;ci=1&amp;file_id='.$latest_file['file_id'].'">'.dPshowImage( './modules/files/images/down.png', '20', '20' ).'</a>';
	else { 
		if ($latest_file['file_checkout'] == 'final') {
			$checkinout_html = 'final';
		} else {
			$checkinout_html = $latest_file['co_contact_first_name'].' '.$latest_file['co_contact_last_name'] 
				.'<br>('.$latest_file['co_user'].')';
		}
	}

	$tpl_row->assign('checkout_link', $checkinout_html);

	$fnamelen = 32;
	$filename = $latest_file['file_name'];
	if (strlen($latest_file['file_name']) > $fnamelen+9)
	{
		$ext = substr($filename, strrpos($filename, '.')+1);
		$filename = substr($filename, 0, $fnamelen);
		$filename .= '[...].' . $ext;
	}

	$latest_file['filename_display'] = $filename;
	$tpl_row->assign('latest_file', $latest_file);

	$hidden_table = '';
	$file_icon = getIcon($file_row['file_type']);
	$tpl_row->assign('file_icon', $file_icon);
	$tpl_row->assign('file_row', $file_row);	

	if ($file_row['file_versions'] > 1)
	{
		$file_versions_list = ' <a href="#" onclick="expand(\'versions_' . ++$id . '\'); ">(' . $file_row['file_versions'] . ')</a>';
		$tpl_row->assign('file_versions', $file_versions_list);

		$hidden_table .= '<tr><td colspan="12">
		<table style="display: none" id="versions_' . $id++ . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<tr>
			<th nowrap="nowrap">&nbsp;</th>
			<th nowrap="nowrap">' . $AppUI->_( 'File Name' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Description' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Versions' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Task Name' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Owner' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Size' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Type' ) . '</th>
			<th nowrap="nowrap">' . $AppUI->_( 'Date' ) . '</th>
		</tr>
		';

		foreach($file_versions as $file)
		{
			if ($file['file_version_id'] == $latest_file['file_version_id'])
			{
				$hdate = new Date($file['file_date']);
				$hidden_table .= '
					<tr>
						<td nowrap="nowrap" width="20">&nbsp;';

				if ($canEdit && dPgetConfig('files_show_versions_edit'))
					$hidden_table .= '
							<a href="./index.php?m=files&amp;a=addedit&amp;file_id=' . $file["file_id"] . '">' . dPshowImage( './images/icons/stock_edit-16.png', '16', '16' ) . "\n</a>";

					$hidden_table .= '
						</td>
						<td nowrap="8%">
							<a href="./fileviewer.php?file_id=' . $file['file_id'] . '" title="' 
							. $file['file_description'] . '">' . $file['file_name'] . '
							</a>
						</td>
						<td width="20%">' . $file['file_description'] . '</td>
						<td width="5%" nowrap="nowrap" align="center">' . $file['file_version'] . '</td>
						<td width="5%" align="center"><a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $file['file_task'] . '">' . $file['task_name'] . '</a></td>
						<td width="15%" nowrap="nowrap">' . $file["contact_first_name"].' '.$file["contact_last_name"] . '</td>
						<td width="5%" nowrap="nowrap" align="right">' . file_size(intval($file['file_size'])) . '</td>
						<td nowrap="nowrap">' . substr($file['file_type'], strpos($file['file_type'], '/')+1) . '</td>
						<td width="15%" nowrap="nowrap" align="right">' . $hdate->format("$df $tf") . '</td>
					</tr>';
						}
				}
				$hidden_table .= '</table></td></tr>';

	}
		$file_size_int = file_size(intval($latest_file["file_size"]));
		$tpl_row->assign('file_size_int', $file_size_int);

		$file_type_sub = substr($latest_file['file_type'], strpos($latest_file['file_type'], '/')+1);
		$tpl_row->assign('file_type_sub', $file_type_sub);
		
		$file_date_formatted = $file_date->format( "$df $tf" );
		$tpl_row->assign('file_date_formatted', $file_date_formatted);

		$tpl_row->assign('hidden_table', $hidden_table);

		// Run the row template through smarty and collect the output.
		$file_rows_html .= $tpl_row->fetchFile('list.row', 'files');
		unset($tpl_row);
}

	$tpl->assign('file_rows', $file_rows_html);
	$tpl->displayFile('list', 'files');
?>