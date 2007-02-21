<?php /* FILES $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table

// add to allow for returning to other modules besides Files
$current_uriArray = parse_url($_SERVER['REQUEST_URI']);
$current_uri = $current_uriArray['query'] . $current_uriArray['fragment'];

global $tpl;

function showfnavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page, $folder)
{

	global $AppUI, $tab, $m, $a;
	$xpg_break = false;
	$xpg_prev_page = $xpg_next_page = 1;
	
	echo "\t<table width='100%' cellspacing='0' cellpadding='0' border='0'><tr>";

	if ($xpg_totalrecs > $xpg_pagesize) {
		$xpg_prev_page = $page - 1;
		$xpg_next_page = $page + 1;
		// left buttoms
		if ($xpg_prev_page > 0) {
			echo "<td align='left' width='15%'>";
			echo '<a href="./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;folder='.$folder.'&amp;page=1">';
			echo '<img src="images/navfirst.gif" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
			echo '<a href="./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;folder='.$folder.'&amp;page=' . $xpg_prev_page . '">';
			echo "<img src=\"images/navleft.gif\" border=\"0\" Alt=\"Previous page ($xpg_prev_page)\"></a></td>";
		} else {
			echo "<td width='15%'>&nbsp;</td>\n";
		} 
		
		// central text (files, total pages, ...)
		echo "<td align='center' width='70%'>";
		//echo "$xpg_totalrecs " . $AppUI->_('File(s)') . " ($xpg_total_pages " . $AppUI->_('Page(s)') . ")";
		echo "$xpg_totalrecs " . $AppUI->_('File(s)') . " " . $AppUI->_('Pages') . ":";
		echo " [ ";
		
		// begin page numbers
		for($n = $page > 16 ? $page-16 : 1; $n <= $xpg_total_pages; $n++) {
			if ($n == $page) {
				echo "<b>$n</b></a>";
			} else {
				echo "<a href='./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;folder={$folder}&amp;page=$n'>";
				echo $n . "</a>";
			} 
			if ($n >= 30+$page-15) {
				$xpg_break = true;
				break;
			} else if ($n < $xpg_total_pages) {
				echo " | ";
			} 
		} 
	
		if (!isset($xpg_break)) { // are we supposed to break ?
			if ($n == $page) {
				echo "<" . $n . "</a>";
			} else {
				echo "<a href='./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;page=$xpg_total_pages'>";
				echo $n . "</a>";
			} 
		} 
		echo " ] ";
		// end page numbers
		
		echo "</td>";

		// right buttoms
		if ($xpg_next_page <= $xpg_total_pages) {
			echo "<td align='right' width='15%'>";
			echo '<a href="./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;folder='.$folder.'&amp;page='.$xpg_next_page.'">';
			echo '<img src="images/navright.gif" border="0" Alt="Next Page ('.$xpg_next_page.')"></a>&nbsp;&nbsp;';
			echo '<a href="./index.php?m='.$m.'&amp;a='.$a.'&amp;tab='.$tab.'&amp;folder='.$folder.'&amp;page=' . $xpg_total_pages . '">';
			echo '<img src="images/navlast.gif" border="0" Alt="Last Page"></a></td>';
		} else {
			echo "<td width='15%'>&nbsp;</td></tr>\n";
		}
		// Page numbered list, up to 30 pages
		//echo "<tr><td colspan=\"3\" align=\"center\">";
// was page numbers
		//echo "</td></tr>";
	} else { // or we dont have any files..
		echo "<td align='center'>";
		if ($xpg_next_page > $xpg_total_pages) {
		echo $xpg_sqlrecs . " " . "Files" . " ";
		}
		echo "</td></tr>";
	} 
	echo "</table>";
}

global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary, $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id, $allowed_companies, $showProject;

//require_once( DP_BASE_DIR . '/modules/files/index_table.lib.php');

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $folder          - current folder
// $page            - actual page to show
// $xpg_pagesize    - max rows per page
// $xpg_min         - initial record in the SELECT LIMIT
// $xpg_totalrecs   - total rows selected
// $xpg_sqlrecs     - total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page   - next pagenumber
// $xpg_prev_page   - previous pagenumber
// $xpg_break       - stop showing page numbered list?
// $xpg_sqlcount    - SELECT for the COUNT total
// $xpg_sqlquery    - SELECT for the SELECT LIMIT
// $xpg_result      - pointer to results from SELECT LIMIT

$page = dPgetParam( $_GET, "page", 1);

if (!isset($project_id)) {
        $project_id = dPgetParam( $_REQUEST, 'project_id', 0);
}
if (!$project_id) {
        $showProject = true;
}

// get company to filter files by
//if (isset( $_POST['company_id'] )) {
//	$AppUI->setState( 'FileIdxCompany', intval( $_POST['company_id'] ) );
//}
//$company_id = $AppUI->getState( 'FileIdxCompany' ) !== NULL ? $AppUI->getState( 'FileIdxCompany' ) : $AppUI->user_company;
if (!isset($company_id)) {
        $company_id = dPgetParam( $_REQUEST, 'company_id', 0);
}

$obj = new CCompany();
$allowed_companies_ary = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$allowed_companies = implode( ",", array_keys($allowed_companies_ary) );

if (!isset($task_id)) {
        $task_id = dPgetParam( $_REQUEST, 'task_id', 0);
}

global $xpg_min, $xpg_pagesize;
$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records
include_once( $AppUI->getModuleClass( 'projects' ) );
include_once( $AppUI->getModuleClass( 'tasks' ) );

$project = new CProject();
$deny1 = $project->getDeniedRecords( $AppUI->user_id );

$task = new CTask();
$deny2 = $task->getDeniedRecords( $AppUI->user_id );

$file_types = dPgetSysVal("FileType");

$folder = $folder ? $folder : 0;

// SQL text for count the total recs from the selected option
$q = new DBQuery();
$q->addTable('files');
$q->addQuery('count(files.file_id)');
$q->addJoin('projects','p','p.project_id = file_project');
$q->addJoin('users','u','u.user_id = file_owner');
$q->addJoin('tasks','t','t.task_id = file_task');
$q->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
$q->addWhere('file_folder = '. $folder);
if (count( $deny1 ) > 0)
	$q->addWhere('file_project NOT IN (' . implode( ',', $deny1 ) . ')');
if (count( $deny2 ) > 0)
	$q->addWhere('file_task NOT IN (' . implode( ',', $deny2 ) . ')');
if ($project_id)
	$q->addWhere('file_project = '. $project_id);
if ($task_id)
	$q->addWhere('file_task = '. $task_id);
if ($company_id) {
	$q->innerJoin('companies','co','co.company_id = p.project_company');
	$q->addWhere('company_id = '. $company_id);
	$q->addWhere('company_id IN (' . $allowed_companies  . ')');
}
	
$q->addGroup('file_folder_name');
$q->addGroup('project_name');
$q->addGroup('file_name');

$xpg_sqlcount = $q->prepare();
$q->clear();

// counts total recs from selection
$xpg_totalrecs = count(db_loadList($xpg_sqlcount));

// How many pages are we dealing with here ??
$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 1;

//shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page, $folder);

//Lets add our bulk form
	require (DP_BASE_DIR.'/modules/files/functions.php');
	$folders_avail = getFolderSelectList();
	//used O (uppercase 0)instead of 0 (zero) to keep things in place
	$folders = array('-1' => Array ( 0 => 'O', 1 => '(Move to Folder)', 2 => -1 )) + array('0' => Array ( 0 => 0, 1 => 'Root', 2 => -1 )) + $folders_avail;

	$project = new CProject();
    $sprojects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
    $q  = new DBQuery;
    $q->addTable('projects');
    $q->addQuery('project_id, company_name');
    $q->addJoin("companies",'co','co.company_id = project_company');
    $idx_companies = $q->loadHashList();
    $q->clear();
    foreach ($sprojects as $prj_id => $prj_name) {
          $sprojects[$prj_id] = $idx_companies[$prj_id].': '.$prj_name;
    }
    asort($sprojects);
    $sprojects = array( 'O'=>'('.$AppUI->_('Move to Project', UI_OUTPUT_RAW).')') + array( '0'=>'('.$AppUI->_('All Projects', UI_OUTPUT_RAW).')') + $sprojects ;


/**** Main Program ****/
$canEdit = !getDenyEdit( $m, $folder );
$canRead = !getDenyRead( $m, $folder );
//echo $folder . ":" . $canEdit . ":" . $canRead;
//if (!$canEdit && !$canRead) {
//	$AppUI->redirect( "m=public&a=access_denied" );
//}

if ($folder > 0) {
	$cfObj->load($folder);
	$msg = '';
	$canDelete = $cfObj->canDelete( $msg, $folder );
}

	$canEdit = !getDenyEdit( $m, $folder );
	$canRead = !getDenyRead( $m, $folder );
	
      $df = $AppUI->getPref('SHDATEFORMAT');
      $tf = $AppUI->getPref('TIMEFORMAT');
      
	// SETUP FOR FILE LIST
	$q = new DBQuery();
	$q->addTable('files');
	$q->addQuery('files.*,count(file_version) as file_versions,round(max(file_version), 2) as file_lastversion,file_folder_id, file_folder_name,project_name, project_color_identifier,contact_first_name, contact_last_name,task_name,task_id');
	$q->addJoin('projects','p','p.project_id = file_project');
	$q->addJoin('users','u','u.user_id = file_owner');
	$q->addJoin('contacts','c','c.contact_id = u.user_contact');
	$q->addJoin('tasks','t','t.task_id = file_task');
	$q->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
	$q->addWhere('file_folder = '. $folder);
	if (count( $deny1 ) > 0)
		$q->addWhere('file_project NOT IN (' . implode( ',', $deny1 ) . ')');
	if (count( $deny2 ) > 0)
		$q->addWhere('file_task NOT IN (' . implode( ',', $deny2 ) . ')');
	if ($project_id)
		$q->addWhere('file_project = '. $project_id);
	if ($task_id)
		$q->addWhere('file_task = '. $task_id);
	if ($company_id) {
		$q->innerJoin('companies','co','co.company_id = p.project_company');
		$q->addWhere('company_id = '. $company_id);
		$q->addWhere('company_id IN (' . $allowed_companies  . ')');
	}
				
	$q->addGroup('file_folder');
	$q->addGroup('project_name');
	$q->addGroup('file_name');

	$q->addOrder('file_folder');
	$q->addOrder('project_name');
	$q->addOrder('file_name');
	
	$q->setLimit($xpg_pagesize, $xpg_min);

	$files_sql = $q->prepare();
	$q->clear();

	$q = new DBQuery();
	$q->addTable('files');
	$q->addQuery('files.file_id, file_version, file_project, file_name, file_task, file_description, user_username as file_owner, file_size, file_category, file_type, file_date, file_folder_name');
	$q->addJoin('projects','p','p.project_id = file_project');
	$q->addJoin('users','u','u.user_id = file_owner');
	$q->addJoin('tasks','t','t.task_id = file_task');
	$q->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
	$q->addWhere('file_folder = '. $folder);
	if ($project_id)
		$q->addWhere('file_project = '. $project_id);
	if ($task_id)
		$q->addWhere('file_task = '. $task_id);
	if ($company_id) {
		$q->innerJoin('companies','co','co.company_id = p.project_company');
		$q->addWhere('company_id = '. $company_id);
		$q->addWhere('company_id IN (' . $allowed_companies  . ')');
	}
				
	$file_versions_sql = $q->prepare();
	$q->clear();

	$files = array();
	$file_versions = array();
	if ($canRead) {
		$files = db_loadList( $files_sql );
		$file_versions = db_loadList($file_versions_sql);
	}
	if ($files === array()) {
		return 0;	
	}
	
	   $folder_where = "file_folder_parent='$parent'";
//   $folder_where .= (count($denied_folders_ary) > 0) ? "\nAND file_folder_id NOT IN (" . implode(",", $denied_folders_ary) . ")" : "";

	$q = new DBQuery();
	$q->addTable('file_folders');
	$q->addQuery('*');
	$q->addWhere($folder_where);
	$q->addOrder('file_folder_name');			
	$folders = $q->loadList();
	
	$folders_avail = getFolderSelectList();
	$folders_menu = array('-1' => Array ( 0 => 'O', 1 => '(Move to Folder)', 2 => -1 )) + array('0' => Array ( 0 => 0, 1 => 'Root', 2 => -1 )) + $folders_avail;
	
	foreach($folders as $k => $row) {
		$folders[$k]['file_count'] = countFiles($row['file_folder_id']);
	}

	$tpl->assign('sprojects', $sprojects);
	$tpl->assign('folders', $folders);
	$tpl->assign('folders_menu', $folders_menu);
	$tpl->assign('files', $files);
	
	foreach($files as $file)
	{
		$tpl->assign('file', $file);
		$tpl->assign('row', $file);
		$html .= $tpl->fetchFile('list_folder.row');
	}
	$tpl->assign('html', $html);
	$tpl->assign('file_versions', $file_versions);
	
	$tpl->assign('canEdit', $canEdit);
	$tpl->assign('df', $df);
	$tpl->assign('tf', $tf);
	$tpl->assign('file_types', $file_types);
	
	$tpl->assign('current_uri', $current_uri);
	$tpl->assign('tab', $tab);
	
	$tpl->assign('folder', $folder);
	$tpl->assign('folders', $folders);
	$tpl->assign('obj', $cfObj);
	
	$tpl->assign('allowed_folders_ary', $allowed_folders_ary);
	
	$tpl->displayFile('list_folder', 'files');
	exit;
// $parent is the parent of the children we want to see
// $level is increased when we go deeper into the tree,
//        used to display a nice indented tree
function getFolders($parent, $level=0) {
   global $AppUI, $allowed_folders_ary, $denied_folders_ary, $tab, $m, $a, $company_id, $allowed_companies, $project_id, $task_id, $current_uri;
   // retrieve all children of $parent


   // display each child
   
		
	// call this function again to display this
	// child's children
	if (!getFolders($row['file_folder_id'], $level+1)) {
		echo "</li>";
	} else {
       		echo "</li></ul>";
	}
   return true;
}

function countFiles($folder) {
	global $AppUI, $company_id, $allowed_companies, $tab;
	global $deny1, $deny2, $project_id, $task_id, $showProject, $file_types;

	$q = new DBQuery();
	$q->addTable('files');
	$q->addQuery('count(files.file_id)','file_in_folder');
	$q->addJoin('projects','p','p.project_id = file_project');
	$q->addJoin('users','u','u.user_id = file_owner');
	$q->addJoin('tasks','t','t.task_id = file_task');
	$q->addJoin('file_folders','ff','ff.file_folder_id = file_folder');
	$q->addWhere('file_folder = '. $folder);
	if (count( $deny1 ) > 0)
		$q->addWhere('file_project NOT IN (' . implode( ',', $deny1 ) . ')');
	if (count( $deny2 ) > 0)
		$q->addWhere('file_task NOT IN (' . implode( ',', $deny2 ) . ')');
	if ($project_id)
		$q->addWhere('file_project = '. $project_id);
	if ($task_id)
		$q->addWhere('file_task = '. $task_id);
	if ($company_id) {
		$q->innerJoin('companies','co','co.company_id = p.project_company');
		$q->addWhere('company_id = '. $company_id);
		$q->addWhere('company_id IN (' . $allowed_companies  . ')');
	}
	$files_in_folder = $q->loadResult();

	return $files_in_folder;
}

function displayFiles($folder) {
	global $m, $a, $tab, $AppUI, $xpg_min, $xpg_pagesize;
	global $deny1, $deny2, $project_id, $task_id, $showProject, $file_types, $cfObj;
	global $xpg_totalrecs, $xpg_total_pages, $page;
	global $company_id, $allowed_companies, $current_uri;
	
	$fp=-1;
	$file_date = new CDate();
	
	$id = 0;
	
	if ($xpg_totalrecs > $xpg_pagesize) {
		showfnavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page, $folder);
	}
	echo "<br />";
}

		getFolders($folder);
?>