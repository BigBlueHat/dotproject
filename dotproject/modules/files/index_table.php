<?php /* FILES $Id$ */
// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
GLOBAL $AppUI, $deny1;

function shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page)
{

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
		echo "$xpg_totalrecs " . "Files" . " ($xpg_total_pages " . "Pages" . ")";
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
	
		for($n = $page > 16 ? $page-16 : 1; $n < $xpg_total_pages; $n++) {
			if ($n == $page) {
				echo "<b>$n</b></a>";
			} else {
				echo "<a href='./index.php?m=files&amp;page=$n'>";
				echo $n . "</a>";
			} 
			if ($n >= 30+$page-15) {
				$xpg_break = true;
				break;
			} else {
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
		echo $xpg_sqlrecs . " " . "Files" . " ";
		echo "</td></tr>";
	} 
	echo "</table>";

}

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
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
// $xpg_resultcount - pointer to results from SELECT COUNT

if (!isset($page)) {
	$page = 1;
} 
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

// SQL text for count the total recs from the selected option
$xpg_sqlcount = "
SELECT COUNT(files.file_id)
FROM files, permissions
LEFT JOIN projects ON project_id = file_project
LEFT JOIN users ON user_id = file_owner
WHERE
	permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
"
. (count( $deny1 ) > 0 ? "\nAND file_project NOT IN (" . implode( ',', $deny1 ) . ')' : '') 
. (count( $deny2 ) > 0 ? "\nAND file_task NOT IN (" . implode( ',', $deny2 ) . ')' : '') 
. ($project_id ? "\nAND file_project = $project_id" : '');


//echo $xpg_sqlcount."<br><br>";

// SETUP FOR FILE LIST
$sql = "
SELECT files.*,
	project_name, project_color_identifier, project_active, 
	user_first_name, user_last_name
FROM files, permissions
LEFT JOIN projects ON project_id = file_project
LEFT JOIN users ON user_id = file_owner
WHERE
	permission_user = $AppUI->user_id
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
."
GROUP BY file_id
ORDER BY project_name, file_name
LIMIT ".$xpg_min.",".$xpg_pagesize;

//echo $sql;

$file = array();
if ($canRead) {
	$files = db_loadList( $sql );
}

// counts total recs from selection
$xpg_resultcount = db_exec($xpg_sqlcount);
$row = db_fetch_row($xpg_resultcount);
$xpg_totalrecs = $row[0];

// How many pages are we dealing with here ??
if ($xpg_totalrecs > $xpg_pagesize) {
	$xpg_total_pages = ceil($xpg_totalrecs / $xpg_pagesize);
}

shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Owner' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?></a></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Date' );?></th>
</tr>
<?php
$fp=-1;
$file_date = new CDate();

foreach ($files as $row) {
	$file_date = new CDate( $row['file_date'] );

	if ($fp != $row["file_project"]) {
		if (!$row["project_name"]) {
			$row["project_name"] = $AppUI->_('All Projects');
			$row["project_color_identifier"] = 'f4efe3';
		}
		if ($showProject) {
			$s = '<tr>';
			$s .= '<td colspan="8" style="background-color:#'.$row["project_color_identifier"].'" style="border: outset 2px #eeeeee">';
			$s .= '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. $row["project_name"] . '</font>';
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row["file_project"];
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
		<?php echo "<a href=\"./fileviewer.php?file_id={$row['file_id']}\" title=\"{$row['file_description']}\">{$row['file_name']}</a>"; ?>
	</td>
	<td width="20%"><?php echo $row["file_description"];?></td>
	<td width="5%" nowrap="nowrap" align="center"><?php echo $row["file_version"];?></td>
	<td width="15%" nowrap="nowrap"><?php echo $row["user_first_name"].' '.$row["user_last_name"];?></td>
	<td width="10%" nowrap="nowrap" align="right"><?php echo intval($row["file_size"] / 1024);?> kb</td>
	<td width="15%" nowrap="nowrap"><?php echo $row["file_type"];?></td>
	<td width="15%" nowrap="nowrap" align="right"><?php echo $file_date->format( "$df $tf" );?></td>
</tr>
<?php }?>
</table>
<?php
shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);
?>
