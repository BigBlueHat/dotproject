<?php

/*
 *        task index
 */

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;
$project_id = isset( $HTTP_COOKIE_VARS['cookie_project'] ) ? $HTTP_COOKIE_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_GET_VARS['cookie_project'] ) ? $HTTP_GET_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_POST_VARS['cookie_project'] ) ? $HTTP_POST_VARS['cookie_project'] : $project_id;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( 'm=help&a=access_denied' );
}

$AppUI->savePlace();

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;
?>

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
	<td nowrap width="100%">
		<span class="title">Project Tasks</span>
	</td>
<form name="task_filter" method=GET action="./index.php">
<input type=hidden name=m value=tasks>
	<td nowrap align=right>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.task_filter.submit();"', $f );
?>
	</td>
</form>
</tr>
</table>

<?php
	$min_view = '';
	include("modules/tasks/tasks.php");
?>
