<?php
##
## Files modules: index page
##

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

if (isset( $_REQUEST['project_id'] )) {
	$AppUI->setState( 'FileIdxProject', $_REQUEST['project_id'] );
}
$project_id = $AppUI->getState( 'FileIdxProject' ) !== NULL ? $AppUI->getState( 'FileIdxProject' ) : 0;

// SETUP FOR PROJECT LIST BOX
// projects that are denied access
$sql = "
SELECT project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'projects'
	AND permission_item = project_id
	AND permission_value = 0
";
$deny = db_loadList( $sql );

$df = $AppUI->getPref('SHDATEFORMAT');

$sql = "
SELECT project_id, project_name
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
" . (count($deny) > 0 ? 'and project_id not in (' . implode( ',', $deny ) . ')' : '') . "
ORDER BY project_name
";

$projects = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );

// SETUP FOR FILE LIST
$sql = "
SELECT file_id, file_project, file_name, file_owner, file_type, file_size,
	DATE_FORMAT(file_date, '$df %H:%i') as file_date,
	project_name, project_color_identifier, project_active, 
	user_first_name, user_last_name
FROM files, permissions
LEFT JOIN projects ON file_project = project_id
LEFT JOIN users ON file_owner = user_id
WHERE
	permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
" . (count($deny) > 0 ? 'AND project_id NOT IN (' . implode( ',', $deny ) . ')' : '') . "
".($project_id ? "AND file_project = $project_id" : '')."
GROUP BY file_id
ORDER BY project_id, file_name
";

$files = db_loadList( $sql );
?>
<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td rowspan="2"><img src="./images/icons/folder.gif" alt="" border="0" width="42" height="42"></td>
	<td rowspan="2" nowrap><span class="title"><?php echo $AppUI->_( 'File Management' );?></span></td>
<form name="searcher" action="?m=files&a=search" method="post">
<input type="hidden" name="dosql" value="searchfiles">
	<td width="100%" align="right">
		<input class="button" type="text" name="s" maxlength="30" size="20" value="Not implemented" disabled="disabled">
	</td>
	<td>&nbsp;<input class="button" type="submit" value="search" disabled="disabled"></td>
</form>
	<?php if (!$denyEdit) { ?>
	<td align="right">
		&nbsp;<input type="button" class=button value="<?php echo $AppUI->_( 'add new file' );?>" onClick="javascript:window.location='./index.php?m=files&a=addedit';">
	</td>
	<?php } ?>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help', 'ID_HELP_FILE_IDX' ).'">' );?></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickProject">
	<td align="right" width="100%" nowrap="nowrap">
		<?php echo $AppUI->_( 'Project' );?>: 
<?php
	echo arraySelect( $projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id );
?>
	</td>
<tr>
</form>

</table>

<table width="98%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap>&nbsp;</th>
	<th nowrap><?php echo $AppUI->_( 'File Name' );?></th>
	<th nowrap><?php echo $AppUI->_( 'Owner' );?></th>
	<th nowrap><?php echo $AppUI->_( 'Date' );?></th>
	<th nowrap><?php echo $AppUI->_( 'Type' );?></a></th>
	<th nowrap><?php echo $AppUI->_( 'Size' );?></th>
</tr>
<?php
$fp=-1;
foreach ($files as $row) {
	if ($fp != $row["file_project"]) {
		if (!$row["project_name"]) {
			$row["project_name"] = 'All Projects';
			$row["project_color_identifier"] = 'f4efe3';
		}
?>
<tr>
	<td colspan="6" style="background-color:#<?php echo $row["project_color_identifier"];?>" style="border: outset 2px #eeeeee">
<?php
	echo '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
		. $row["project_name"] . '</font>';
	?></td>
</tr>
<?php
	}
	$fp = $row["file_project"];
?>
<tr>
	<td nowrap="nowrap">
	<?php if (!$denyEdit) { ?>
		<A href="./index.php?m=files&a=addedit&file_id=<?php echo $row["file_id"];?>"><img src="./images/icons/pencil.gif" alt="edit file" border="0" width=12 height=12></a>
	<?php } ?>
	</td>
	<td nowrap="nowrap">
		<?php echo "<a href=\"./fileviewer.php?file_id={$row['file_id']}\">{$row['file_name']}</a>"; ?>
	</td>
	<td nowrap="nowrap"><?php echo $row["user_first_name"].' '.$row["user_last_name"];?></td>
	<td nowrap="nowrap"><?php echo $row["file_date"];?></td>
	<td nowrap="nowrap"><?php echo $row["file_type"];?></td>
	<td nowrap="nowrap" align="right"><?php echo intval($row["file_size"] / 1024);?>kb</td>
</tr>
<?php }?>
</table>
