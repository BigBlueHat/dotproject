<?php
/* FILES $Id$ */
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
function shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page)
{

	GLOBAL $AppUI;
	$xpg_break = false;
        $xpg_prev_page = $xpg_next_page = 1;
	
	echo "\t<table width='100%' cellspacing='0' cellpadding='0' border=0><tr>";

	if ($xpg_totalrecs > $xpg_pagesize) {
		$xpg_prev_page = $page - 1;
		$xpg_next_page = $page + 1;
		// left buttoms
		if ($xpg_prev_page > 0) {
			echo "<td align='left' width='15%'>";
			echo '<a href="./index.php?m=files&amp;page=1">';
			echo '<img src="images/navfirst.gif" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
			echo '<a href="./index.php?m=files&amp;page=' . $xpg_prev_page . '">';
			echo "<img src=\"images/navleft.gif\" border=\"0\" Alt=\"Previous page ($xpg_prev_page)\"></a></td>";
		} else {
			echo "<td width='15%'>&nbsp;</td>\n";
		} 
		
		// central text (files, total pages, ...)
		echo "<td align='center' width='70%'>";
		echo "$xpg_totalrecs " . $AppUI->_('File(s)') . " ($xpg_total_pages " . $AppUI->_('Page(s)') . ")";
		echo "</td>";

		// right buttoms
		if ($xpg_next_page <= $xpg_total_pages) {
			echo "<td align='right' width='15%'>";
			echo '<a href="./index.php?m=files&amp;page='.$xpg_next_page.'">';
			echo '<img src="images/navright.gif" border="0" Alt="Next Page ('.$xpg_next_page.')"></a>&nbsp;&nbsp;';
			echo '<a href="./index.php?m=files&amp;page=' . $xpg_total_pages . '">';
			echo '<img src="images/navlast.gif" border="0" Alt="Last Page"></a></td>';
		} else {
			echo "<td width='15%'>&nbsp;</td></tr>\n";
		}
		// Page numbered list, up to 30 pages
		echo "<tr><td colspan=\"3\" align=\"center\">";
		echo " [ ";
	
		for($n = $page > 16 ? $page-16 : 1; $n <= $xpg_total_pages; $n++) {
			if ($n == $page) {
				echo "<b>$n</b></a>";
			} else {
				echo "<a href='./index.php?m=files&amp;page=$n'>";
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
				echo "<a href='./index.php?m=files&amp;page=$xpg_total_pages'>";
				echo $n . "</a>";
			} 
		} 
		echo " ] ";
		echo "</td></tr>";
	} else { // or we dont have any files..
		echo "<td align='center'>";
		if ($xpg_next_page > $xpg_total_pages) {
		echo $xpg_sqlrecs . " " . "Files" . " ";
		}
		echo "</td></tr>";
	} 
	echo "</table>";
}

GLOBAL $AppUI, $deny1, $canRead, $canEdit, $canAdmin;
global $company_id, $project_id, $task_id;

//require_once( dPgetConfig( 'root_dir' )."/modules/files/index_table.lib.php");

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab             - file category
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

$tab = $AppUI->getState( 'FileIdxTab' ) !== NULL ? $AppUI->getState( 'FileIdxTab' ) : 0;
$page = dPgetParam( $_GET, "page", 1);
if (!isset($project_id))
        $project_id = dPgetParam( $_REQUEST, 'project_id', 0);
if (!isset($showProject))
        $showProject = true;

$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

// load the following classes to retrieved denied records
include_once( $AppUI->getModuleClass( 'projects' ) );
include_once( $AppUI->getModuleClass( 'tasks' ) );
require_once $AppUI->getSystemClass('query');

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$file_types = dPgetSysVal("FileType");
if ($tab <= 0)
        $catsql = false;
else
        $catsql = "file_category = " . --$tab ;
// SQL text for count the total recs from the selected option
$q = new DBQuery;
$q->addQuery('count(file_id)');
$q->addTable('files', 'f');
if ($catsql) $q->addWhere($catsql);
if ($company_id) $q->addWhere("project_company = $company_id");
if ($project_id) $q->addWhere("file_project = $project_id");
if ($task_id) $q->addWhere("file_task = $task_id");
$q->addGroup("file_version_id");
$project->setAllowedSQL($AppUI->user_id, $q, 'file_project');
$task->setAllowedSQL($AppUI->user_id, $q, 'file_task');

// SETUP FOR FILE LIST
$q2 = new DBQuery;
$q2->addQuery(array ('f.*',
	'max(f.file_id) as  latest_id',
	'count(f.file_version) as file_versions',
	'round(f.file_version,2) as file_lastversion',
	'project_name',
	'project_color_identifier',
	'project_active',
	'cont.contact_first_name',
	'cont.contact_last_name',
	'task_name',
	'task_id',
	'cu.user_username as co_user'
));
$q2->addTable('files', 'f');
$q2->leftJoin('users', 'cu', 'cu.user_id = f.file_checkout');
$q2->leftJoin('users', 'u', 'u.user_id = f.file_owner');
$q2->leftJoin('contacts', 'cont', 'cont.contact_id = u.user_contact');
$project->setAllowedSQL($AppUI->user_id, $q2, 'file_project');
$task->setAllowedSQL($AppUI->user_id, $q2, 'file_task');
if ($catsql) $q2->addWhere($catsql);
if ($company_id) $q2->addWhere("project_company = $company_id");
if ($project_id) $q2->addWhere("file_project = $project_id");
if ($task_id) $q2->addWhere("file_task = $task_id");
$q2->setLimit($xpg_pagesize, $xpg_min);
// Adding an Order by that is different to a group by can cause
// performance issues. It is far better to rearrange the group
// by to get the correct ordering.
$q2->addGroup('project_id');
$q2->addGroup('file_version_id DESC');

$q3 = new DBQuery;
$q3->addQuery("file_id, file_version, file_version_id, file_project, file_name, file_task, task_name, file_description, file_checkout, file_co_reason, u.user_username as file_owner, file_size, file_category, file_type, file_date, cu.user_username as co_user, project_name, project_color_identifier, project_active, project_owner");
$q3->addTable('files');
$q3->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
$q3->leftJoin('users', 'u', 'u.user_id = file_owner');
//$q3->leftJoin('tasks', 't', 't.task_id = file_task');
//$q3->leftJoin('projects', 'p', 'p.project_id = file_project');
$project->setAllowedSQL($AppUI->user_id, $q3, 'file_project');
$task->setAllowedSQL($AppUI->user_id, $q3, 'file_task');
if ($project_id) $q3->addWhere("file_project = $project_id");
if ($task_id) $q3->addWhere("file_task = $task_id");

$files = array();
$file_versions = array();
if ($canRead) {
	
	$files = $q2->loadList();
	$file_versions = $q3->loadHashList('file_id');
}
// counts total recs from selection
$xpg_totalrecs = count($q->loadList());

// How many pages are we dealing with here ??
$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 1;

shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);

?>
<script type="text/JavaScript">
function expand(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "block" : "none";
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
        <th nowrap="nowrap"><?= $AppUI->_('co') ?></th>
        <th nowrap="nowrap"><?= $AppUI->_('Checkout Reason') ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Versions' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Task Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Owner' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?></a></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Date' );?></th>
</tr>
<?php
$fp=-1;
$file_date = new CDate();

function file_size($size)
{
        if ($size > 1024*1024*1024)
                return round($size / 1024 / 1024 / 1024, 2) . ' Gb';
        if ($size > 1024*1024)
                return round($size / 1024 / 1024, 2) . ' Mb';
        if ($size > 1024)
                return round($size / 1024, 2) . ' Kb';
        return $size . ' B';
}

function last_file($file_versions, $file_name, $file_project)
{
        $latest = NULL;
        //global $file_versions;
        if (isset($file_versions))
        foreach ($file_versions as $file_version)
                if ($file_version['file_name'] == $file_name && $file_version['file_project'] == $file_project)
                        if ($latest == NULL || $latest['file_version'] < $file_version['file_version'])
                                $latest = $file_version;

        return $latest;
}

$id = 0;
foreach ($files as $file_row) {
        $row = $file_versions[$file_row['latest_id']];
	$file_date = new CDate( $row['file_date'] );

	if ($fp != $row["file_project"]) {
		if (!$row["project_name"]) {
			$row["project_name"] = $AppUI->_('All Projects');
			$row["project_color_identifier"] = 'f4efe3';
		}
		if ($showProject) {
			$style = "background-color:#$row[project_color_identifier];color:" . bestColor($row["project_color_identifier"]);
			$s = '<tr>';
			$s .= '<td colspan="12" style="border: outset 2px #eeeeee;' . $style . '">';
			$s .= '<a href="?m=projects&a=view&project_id=' . $row['file_project'] . '">';
			$s .= '<span style="' . $style . '">' . $row["project_name"] . '</span></a>';
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row["file_project"];
//        if ($row['file_versions'] > 1)
//                $file = last_file($file_versions, $row['file_name'], $row['file_project']);
//        else 
                $file = $row;
?>
<tr>
	<td nowrap="nowrap" width="20">
	<?php if ($canEdit && ( empty($file['file_checkout']) || ( $file['file_checkout'] == 'final' && ($canAdmin || $file['project_owner'] == $AppUI->user_id) ))) {
		echo "\n".'<a href="./index.php?m=files&a=addedit&file_id=' . $row["file_id"] . '">';
		echo dPshowImage( './images/icons/stock_edit-16.png', '16', '16' );
		echo "\n</a>";
	}
	?>
	</td>
        <td nowrap="nowrap">
        <?php if ($canEdit && empty($file['file_checkout']) ) {
        ?>
                <a href="?m=files&a=co&file_id=<?= $file['file_id'] ?>">CO</a>
        <?php }
        else if ($file['file_checkout'] == $AppUI->user_id) { ?>
                <a href="?m=files&a=addedit&ci=1&file_id=<?= $file['file_id'] ?>">CI</a>
        <?php }
        else { 
                if ($file['file_checkout'] == 'final')
                        echo 'final';
                else
                        echo $file['co_user']; 
        }
        ?>
                
        </td>
        <td width="10%"><?= $file['file_co_reason'] ?></td>
	<td nowrap="8%">
		<?php echo "<a href=\"./fileviewer.php?file_id={$file['file_id']}\" title=\"{$row['file_description']}\">{$row['file_name']}</a>"; ?>
	</td>
	<td width="20%"><?php echo $row['file_description'];?></td>
	<td width="5%" nowrap="nowrap" align="center">
        <?php
                $hidden_table = '';
                echo $file_row['file_lastversion'];
                if ($file_row['file_versions'] > 1)
                {
                 echo ' <a href="#" onClick="expand(\'versions_' . ++$id . '\'); ">(' . $file_row['file_versions'] . ')</a>';
                 $hidden_table = '<tr><td colspan="12">
<table style="display: none" id="versions_' . $id++ . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
        <th nowrap="nowrap">&nbsp;</th>
        <th nowrap="nowrap">' . $AppUI->_( 'File Name' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Description' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Versions' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Task Name' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Owner' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Size' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Type' ) . '</a></th>
        <th nowrap="nowrap">' . $AppUI->_( 'Date' ) . '</th>
</tr>
';
                foreach($file_versions as $file)
                        if ($file['file_version_id'] == $row['file_version_id'])
                        {
                                $hidden_table .= '
        <tr>
                <td nowrap="nowrap" width="20">&nbsp;';
                                if ($canEdit)
                                {
                                        $hidden_table .= '
                <a href="./index.php?m=files&a=addedit&file_id=' . $row["file_id"] . '">' . dPshowImage( './images/icons/stock_edit-16.png', '16', '16' ) . "\n</a>";
                                }
                                $hidden_table .= '
                </td>
                <td nowrap="8%"><a href="./fileviewer.php?file_id=' . $file['file_id'] . '" 
                        title="' . $file['file_description'] . '">' . 
                        $file['file_name'] . '
                </a></td>
                <td width="20%">' . $file['file_description'] . '</td>
                <td width="5%" nowrap="nowrap" align="center">' . $file['file_version'] . '</td>
                <td width="5%" align="center"><a href="./index.php?m=tasks&a=view&task_id=' . $row['file_task'] . '">' . $file['task_name'] . '</a></td>
                <td width="15%" nowrap="nowrap">' . $file_row["contact_first_name"].' '.$file_row["contact_last_name"] . '</td>
                <td width="5%" nowrap="nowrap" align="right">' . intval($file['file_size']/1024) . 'kb </td>
                <td width="15%" nowrap="nowrap">' . $file['file_type'] . '</td>
                <td width="15%" nowrap="nowrap" align="right">' . $file['file_date'] . '</td>
        </tr>';
                        }
                $hidden_table .= '</table>';
                //$hidden_table .= '</span>';
                }
        ?>
        </td>
	<td width="5%" align="center"><a href="./index.php?m=tasks&a=view&task_id=<?php echo $row["file_task"];?>"><?php echo $row["task_name"];?></a></td>
	<td width="15%" nowrap="nowrap"><?php echo $file_row["contact_first_name"].' '.$file_row["contact_last_name"];?></td>
	<td width="5%" nowrap="nowrap" align="right"><?php echo file_size(intval($row["file_size"]));?></td>
	<td width="15%" nowrap="nowrap"><?php echo $row["file_type"];?></td>
	<td width="15%" nowrap="nowrap" align="right"><?php echo $file_date->format( "$df $tf" );?></td>
</tr>
<?= $hidden_table ?>
<?php 
        $hidden_table = ''; 
}?>
</table>
<?php
shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);
?>
