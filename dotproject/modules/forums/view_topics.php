<?php /* FORUMS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'ForumVwOrderDir' ) ? ($AppUI->getState( 'ForumVwOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
	$AppUI->setState( 'ForumVwOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'ForumVwOrderDir', $orderdir);
}
$orderby         = $AppUI->getState( 'ForumVwOrderBy' ) ? $AppUI->getState( 'ForumVwOrderBy' ) : 'latest_reply';
$orderdir        = $AppUI->getState( 'ForumVwOrderDir' ) ? $AppUI->getState( 'ForumVwOrderDir' ) : 'desc';

//Pull All Messages
$q  = new DBQuery;
$q->addTable('forum_messages', 'fm1');
$q->addQuery('fm1.*');
$q->addQuery('COUNT(distinct fm2.message_id) AS replies');
$q->addQuery('MAX(fm2.message_date) AS latest_reply');
$q->addQuery('user_username, contact_first_name, watch_user');
$q->addQuery('count(distinct v1.visit_message) as reply_visits');
$q->addQuery('v2.visit_user');
$q->addJoin('users', 'u', 'fm1.message_author = u.user_id');
$q->addJoin('contacts', 'con', 'contact_id = user_contact');
$q->addJoin('forum_messages', 'fm2', 'fm1.message_id = fm2.message_parent');
$q->addJoin('forum_watch', 'fw', "watch_user = $AppUI->user_id AND watch_topic = fm1.message_id");
$q->addJoin('forum_visits', 'v1', "v1.visit_user = $AppUI->user_id AND v1.visit_message = fm2.message_id");
$q->addJoin('forum_visits', 'v2', "v2.visit_user = $AppUI->user_id AND v2.visit_message = fm1.message_id");

$q->addWhere("fm1.message_forum = $forum_id");
switch ($f) {
	case 1:
		$q->addWhere("watch_user IS NOT NULL");
		break;
	case 2:
		$q->addWhere("(NOW() < DATE_ADD(fm2.message_date, INTERVAL 30 DAY) OR NOW() < DATE_ADD(fm1.message_date, INTERVAL 30 DAY))");
		break;
}
$q->addGroup('fm1.message_id,
	fm1.message_parent,
	fm1.message_author,
	fm1.message_title,
	fm1.message_date,
	fm1.message_body,
	fm1.message_published');
$q->addOrder("$orderby $orderdir");
$topics = $q->loadList();

$crumbs = array();
$crumbs['?m=forums'] = 'forums list';

global $tpl;
$tpl->assign('breadCrumbs', breadCrumbs( $crumbs ));
$tpl->assign('canEdit', $canEdit);
$tpl->assign('forum_id', $forum_id);
$tpl->assign('f', $f);

$topic_rows = '';

$now = new CDate();

foreach ($topics as $row) {
	$tpl_row = new CTemplate();

	$tpl_row->assign('forum_id', $forum_id);
	$last = intval( $row['latest_reply'] ) ? new CDate( $row['latest_reply'] ) : null;
	
	//JBF limit displayed messages to first-in-thread
	$tpl_row->assign('row', $row);
	$tpl_row->assign('user_id', $AppUI->user_id);

	$formatted_date = ($last != null) ? $last->format( "$df $tf" ) : null;
	$tpl_row->assign('formatted_date', $formatted_date);

	$span = new Date_Span();
	$span->setFromDateDiff( $now, $last );

	$date_diff = sprintf( '%.1f', $span->format( '%d' ) );
	$tpl_row->assign('date_diff_now_last', $date_diff);

	$topic_rows .= $tpl_row->fetchFile('view_topics.row', 'forums');
}

$tpl->assign('topic_rows', $topic_rows);

$tpl->displayFile('view_topics', 'forums');
?>