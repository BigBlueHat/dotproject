<?php
/* FILES $Id$ */
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
function shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page)
{

	GLOBAL $AppUI;
	$xpg_break = false;
	
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

GLOBAL $AppUI, $deny1, $canRead, $canEdit;

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

$project = new CProject();
$deny1 = $project->getDeniedRecords( $AppUI->user_id );

$task = new CTask();
$deny2 = $task->getDeniedRecords( $AppUI->user_id );

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$file_types = dPgetSysVal("FileType");
if ($tab <= 0)
        $catsql = "";
else
        $catsql = " AND file_category = " . --$tab ;
// SQL text for count the total recs from the selected option
$xpg_sqlcount = "
SELECT count(files.file_id)
FROM files, permissions
LEFT JOIN projects ON project_id = file_project
LEFT JOIN users ON user_id = file_owner
WHERE
	permission_user = $AppUI->user_id
        $catsql
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
"
. (count( $deny1 ) > 0 ? "\nAND file_project NOT IN (" . implode( ',', $deny1 ) . ')' : '')
. (count( $deny2 ) > 0 ? "\nAND file_task NOT IN (" . implode( ',', $deny2 ) . ')' : '')
. ($project_id ? "\nAND file_project = $project_id" : '')
. ' GROUP BY project_name, file_name';

// SETUP FOR FILE LIST
$sql = "
SELECT files.*,
        count(file_version) as file_versions,
        round(max(file_version), 2) as file_lastversion,
	project_name, project_color_identifier, project_active,
	user_first_name, user_last_name,task_name,task_id
FROM files, permissions
LEFT JOIN projects ON project_id = file_project
LEFT JOIN users ON user_id = file_owner
LEFT JOIN tasks on file_task = task_id
WHERE
	permission_user = $AppUI->user_id
        $catsql
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
"
. (count( $deny1 ) > 0 ? "\nAND file_project NOT IN (" . implode( ',', $deny1 ) . ')' : '') 
. (count( $deny2 ) > 0 ? "\nAND file_task NOT IN (" . implode( ',', $deny2 ) . ')' : '') 
. ($project_id ? "\nAND file_project = $project_id" : '')
. '
GROUP BY project_name, file_name
ORDER BY project_name, file_name
LIMIT ' . $xpg_min . ', ' . $xpg_pagesize ;

$sql2 = "SELECT file_id, file_version, file_project, file_name, file_description, user_username as file_owner, file_size, file_category, file_type, file_date
        FROM files
        LEFT JOIN users ON user_id = file_owner
        LEFT JOIN tasks on file_task = task_id
        LEFT JOIN projects ON project_id = file_project
" . 
($project_id ? " AND file_project = $project_id" : '');

$files = array();
$file_versions = array();
if ($canRead) {
	$files = db_loadList( $sql );
        $file_versions = db_loadList($sql2);
}

// counts total recs from selection
$xpg_totalrecs = count(db_loadList($xpg_sqlcount));

// How many pages are we dealing with here ??
if ($xpg_totalrecs > $xpg_pagesize) {
	$xpg_total_pages = ceil($xpg_totalrecs / $xpg_pagesize);
}

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
	<th nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Versions' );?></th>
        <th nowrap="nowrap"><?php echo $AppUI->_( 'Category' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Task Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Owner' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?></a></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Date' );?></th>
</tr>
<?php
$fp=-1;
$file_date = new CDate();

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
foreach ($files as $row) {
	$file_date = new CDate( $row['file_date'] );

	if ($fp != $row["file_project"]) {
		if (!$row["project_name"]) {
			$row["project_name"] = $AppUI->_('All Projects');
			$row["project_color_identifier"] = 'f4efe3';
		}
		if ($showProject) {
			$s = '<tr>';
			$s .= '<td colspan="10" style="background-color:#'.$row["project_color_identifier"].'" style="border: outset 2px #eeeeee">';
			$s .= '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. $row["project_name"] . '</font>';
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row["file_project"];
        if ($row['file_versions'] > 1)
                $file = last_file($file_versions, $row['file_name'], $row['file_project']);
?>
<tr>
	<td nowrap="nowrap" width="20">
	<?php if ($canEdit) {
		echo "\n".'<a href="./index.php?m=files&a=addedit&file_id=' . $row["file_id"] . '">';
		echo dPshowImage( './images/icons/stock_edit-16.png', '16', '16' );
		echo "\n</a>";
	}
	?>
	</td>
	<td nowrap="8%">
		<?php echo "<a href=\"./fileviewer.php?file_id={$file['file_id']}\" title=\"{$row['file_description']}\">{$row['file_name']}</a>"; ?>
	</td>
	<td width="20%"><?php echo $row['file_description'];?></td>
	<td width="5%" nowrap="nowrap" align="center">
        <?php
                echo $row['file_lastversion'];
                if ($row['file_versions'] > 1)
                {
                 echo ' <a href="#" onClick="expand(\'versions_' . ++$id . '\'); ">(' . $row['file_versions'] . ')</a>';
                 $hidden_table = '<tr><td colspan="10">
<table style="display: none" id="versions_' . $id++ . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
        <th nowrap="nowrap">&nbsp;</th>
        <th nowrap="nowrap">' . $AppUI->_( 'File Name' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Description' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Versions' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Category' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Task Name' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Owner' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Size' ) . '</th>
        <th nowrap="nowrap">' . $AppUI->_( 'Type' ) . '</a></th>
        <th nowrap="nowrap">' . $AppUI->_( 'Date' ) . '</th>
</tr>
';
                foreach($file_versions as $file)
                        if ($file['file_name'] == $row['file_name'] && $file['file_project'] == $row['file_project'])
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
                <td width="10%" nowrap="nowrap" align="center">' . $file_types[$file['file_category']] . '</td>
                <td width="5%" align="center">' . $file['file_task'] . '</td>
                <td width="15%" nowrap="nowrap">' . $row["user_first_name"].' '.$row["user_last_name"] . '</td>
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
        <td width="10%" nowrap="nowrap" align="center"><?php echo $file_types[$row["file_category"]];?></td> 
	<td width="5%" align="center"><a href="./index.php?m=tasks&a=view&task_id=<?php echo $row["task_id"];?>"><?php echo $row["task_name"];?></a></td>
	<td width="15%" nowrap="nowrap"><?php echo $row["user_first_name"].' '.$row["user_last_name"];?></td>
	<td width="5%" nowrap="nowrap" align="right"><?php echo intval($row["file_size"] / 1024);?> kb</td>
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
