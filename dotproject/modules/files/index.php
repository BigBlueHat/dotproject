<?php /* FILES $Id$ */
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
$deny1 = db_loadColumn( $sql );

$sql = "
SELECT project_id, project_name
FROM projects, permissions, files
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)"
.(count( $deny1 ) > 0 ? "\nAND project_id NOT IN (" . implode( ',', $deny1 ) . ')' : '')
."
	AND project_id = file_project
ORDER BY project_name
";

$projects = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Files', 'folder.gif', $m, 'ID_HELP_FILE_IDX' );
$titleBlock->addCell(
	arraySelect( $projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id ), '',
	'<form name="pickProject" action="?m=files" method="post">', '</form>'
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new file').'">', '',
		'<form action="?m=files&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td>
<?php
	$showProject = true;
	require( "{$AppUI->cfg['root_dir']}/modules/files/index_table.php" );
?>
	</td>
</tr>
</table>