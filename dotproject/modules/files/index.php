<?php /* FILES $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_REQUEST['project_id'] )) {
	$AppUI->setState( 'FileIdxProject', $_REQUEST['project_id'] );
}
$project_id = $AppUI->getState( 'FileIdxProject' ) !== NULL ? $AppUI->getState( 'FileIdxProject' ) : 0;

require_once( $AppUI->getModuleClass( 'projects' ) );

// get the list of visible companies
$extra = array(
	'from' => 'files',
	'where' => 'AND project_id = file_project'
);

$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('All') ), $projects );

// setup the title block
$titleBlock = new CTitleBlock( 'Files', 'folder5.png', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Filter') . ':' );
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
