<?php /* $Id$ */
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

$sql = "
SELECT project_id, project_name
FROM projects, permissions, files
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
" . (count($deny) > 0 ? 'and project_id not in (' . implode( ',', $deny ) . ')' : '') . "
	AND project_id = file_project
ORDER BY project_name
";

$projects = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );
?>
<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td rowspan="2"><img src="./images/icons/folder.gif" alt="" border="0" width="42" height="42"></td>
	<td rowspan="2" nowrap><span class="title"><?php echo $AppUI->_( 'File Management' );?></span></td>
<form name="searcher" action="?m=files&a=search" method="post">
<input type="hidden" name="dosql" value="searchfiles">
	<td width="100%" align="right">
		<input class="button" type="text" name="s" maxlength="30" size="20" value="<?php echo $AppUI->_('Not implemented');?>" disabled="disabled">
	</td>
	<td>&nbsp;<input class="button" type="submit" value="<?php echo $AppUI->_('search');?>" disabled="disabled"></td>
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
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" name="pickProject">
	<td align="right" width="100%" nowrap="nowrap">
		<?php echo $AppUI->_( 'Project' );?>: 
<?php
	echo arraySelect( $projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id );
?>
	</td>
<tr>
</form>

</table>

<table cellspacing="0" cellpadding="0" border="0" width="98%">
<tr><td>
<?php
	$showProject = true;
	require( "$root_dir/modules/files/index_table.php" );
?>
</td></tr>
</table>
