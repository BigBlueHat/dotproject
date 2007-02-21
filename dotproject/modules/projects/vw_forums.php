<?php /* PROJECTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $AppUI, $project_id, $forum_id;
// Forums mini-table in project view action
$q = new DBQuery;

$q->addQuery('forum_id');
$q->addTable('forums');
$q->addWhere('forum_project = ' . $project_id);
$forum_id = $q->loadResult();
if ($forum_id)
	include(DP_BASE_DIR . '/modules/forums/view_topics.php');
else
	echo $AppUI->_('no project forums');
?>