<?php
##
## Task index - standalone wrapper
##

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( 'm=help&a=access_denied' );
}
$AppUI->savePlace();

// act on passed parameters
if (isset( $_GET['f'] )) {
	$AppUI->setState( 'TaskIdxFilter', $_GET['f'] );
}
$f = $AppUI->getState( 'TaskIdxFilter' ) ? $AppUI->getState( 'TaskIdxFilter' ) : 'my';

if (isset( $_GET['project_id'] )) {
	$AppUI->setState( 'TaskIdxProject', $_GET['project_id'] );
}
$project_id = $AppUI->getState( 'TaskIdxProject' ) ? $AppUI->getState( 'TaskIdxProject' ) : 0;
$AppUI->setState( 'ActiveProject', $project_id );
?>

<table width="98%" border="0" cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
	<td nowrap width="100%">
		<h1>*</h1>
	</td>
<form name="task_filter" method=GET action="./index.php">
<input type=hidden name=m value=tasks>
	<td nowrap align=right>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.task_filter.submit();"', $f, true );
?>
	</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_TASK_IDX' );?></td>
</form>
</tr>
</table>

<?php
// include the re-usable sub view
	$min_view = false;
	include("{$AppUI->cfg['root_dir']}/modules/tasks/tasks.php");
?>
