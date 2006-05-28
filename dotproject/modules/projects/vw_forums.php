<?php /* PROJECTS $Id$ */
GLOBAL $AppUI, $project_id;
global $baseDir, $forum_id;
// Forums mini-table in project view action
$q  = new DBQuery;

$q->addQuery('forum_id');
$q->addTable('forums');
$q->addWhere('forum_project = ' . $project_id);
$forum_id = $q->loadResult();
if ($forum_id)
	include($baseDir . '/modules/forums/view_topics.php');
else
	echo $AppUI->_('no project forums');
?>
