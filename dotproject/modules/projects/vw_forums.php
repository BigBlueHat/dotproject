<?php /* PROJECTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $AppUI, $forum_id;
$project_id = intval(dPgetParam($_GET, 'project_id', 0));
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