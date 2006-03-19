<?php /* FORUMS $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'ForumIdxOrderDir' ) ? ($AppUI->getState( 'ForumIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
	$AppUI->setState( 'ForumIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'ForumIdxOrderDir', $orderdir);
}
$orderby         = $AppUI->getState( 'ForumIdxOrderBy' ) ? $AppUI->getState( 'ForumIdxOrderBy' ) : 'forum_name';
$orderdir        = $AppUI->getState( 'ForumIdxOrderDir' ) ? $AppUI->getState( 'ForumIdxOrderDir' ) : 'asc';

$perms =& $AppUI->acl();

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$f = dPgetParam( $_POST, 'f', 0 );


$forum =& new CForum;
require_once $AppUI->getModuleClass('projects');
$project =& new CProject;

$max_msg_length = 30;

/* Query modified by Fergus McDonald 2005/08/12 to address slow join issue */

$q  = new DBQuery;
$q->addTable('forums');
$q->addTable('projects', 'p');
$q->addTable('users', 'u');
$q->addQuery("forum_id, forum_project, forum_description, forum_owner, forum_name");
$q->addQuery("forum_moderated, forum_create_date, forum_last_date");
$q->addQuery("sum(if(c.message_parent=-1,1,0)) as forum_topics, sum(if(c.message_parent>0,1,0)) as forum_replies");
$q->addQuery("user_username, project_name, project_color_identifier");
$q->addQuery("SUBSTRING(l.message_body,1,$max_msg_length) message_body");
$q->addQuery("LENGTH(l.message_body) message_length, watch_user, l.message_parent, l.message_id");
$q->addQuery("count(distinct v.visit_message) as visit_count, count(distinct c.message_id) as message_count");
$q->addJoin('forum_messages', 'l', 'l.message_id = forum_last_id');
$q->addJoin('forum_messages', 'c', 'c.message_forum = forum_id');
$q->addJoin('forum_watch', 'w', "watch_user = $AppUI->user_id AND watch_forum = forum_id");
$q->addJoin('forum_visits', 'v', "visit_user = $AppUI->user_id AND visit_forum = forum_id and visit_message = c.message_id");

$project->setAllowedSQL($AppUI->user_id, $q);
$forum->setAllowedSQL($AppUI->user_id, $q);


$q->addWhere("user_id = forum_owner AND project_id = forum_project");

switch ($f) {
	case 1:
		$q->addWhere("project_status != 7 AND forum_owner = $AppUI->user_id");
		break;
	case 2:
		$q->addWhere("project_status != 7 AND watch_user IS NOT NULL");
		break;
	case 3:
		$q->addWhere("project_status != 7 AND project_owner = $AppUI->user_id");
		break;
	case 4:
		$q->addWhere("project_status != 7 AND project_company = $AppUI->user_company");
		break;
	case 5:
		$q->addWhere("project_status = 7");
		break;
	default:
		$q->addWhere("project_status != 7");
		break;
}

$q->addGroup('forum_id');
$q->addOrder("$orderby $orderdir");
$forums = $q->loadList();

// setup the title block
$titleBlock = new CTitleBlock( 'Forums', 'support.png', $m, "$m.$a" );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f , true ), '',
	'<form name="forum_filter" action="?m=forums" method="post">', '</form>'
);

$canAdd = $perms->checkModule( $m, 'add');
if ($canAdd) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new forum').'">', '',
		'<form action="?m=forums&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

$tpl->assign('f', $f);

$p ="";
$forum_rows_html = "";
$now = new CDate();
foreach ($forums as $row) {
	$tpl_row = new CTemplate();
	$tpl_row->assign('row', $row);
	
	$message_date = intval( $row['forum_last_date'] ) ? new CDate( $row['forum_last_date'] ) : null;

	if($p != $row["forum_project"]) {
		$tpl_row->assign('show_project_header', TRUE);
		$create_date = intval( $row['forum_create_date'] ) ? new CDate( $row['forum_create_date'] ) : null;
		$p = $row["forum_project"];
	}
	if ( $row["forum_owner"] == $AppUI->user_id || $perms->checkModule('forums', 'add') ) { 
		$tpl_row->assign('show_edit_controls', TRUE); 
	} 

	$formatted_create_date = $create_date->format( $df );
	$tpl_row->assign('create_date', $formatted_create_date);

	if ($message_date !== null) {
		$tpl_row->assign('show_last_post', TRUE);	
		$formatted_last_message_date = $message_date->format( "$df $tf" );
		$tpl_row->assign('message_date', $formatted_last_message_date);

		$last = new Date_Span();
		$last->setFromDateDiff( $now, $message_date );

		$last_post_days = sprintf( "%.1f", $last->format( "%d" ) );
		$tpl_row->assign('last_post_days', $last_post_days);

		$id = $row['message_parent'] < 0 ? $row['message_id'] : $row['message_parent'];
		$tpl_row->assign('last_message_id', $id); 
		$tpl_row->assign('max_msg_length', $max_msg_length);
	} else {
		$tpl_row->assign('show_last_post', FALSE);
	}

	$forum_rows_html .= $tpl_row->fetchFile('index.row');
	unset($tpl_row);	
}

$tpl->assign('forum_rows', $forum_rows_html);
$tpl->displayFile('index');
?>
