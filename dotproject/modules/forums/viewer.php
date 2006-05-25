<?php /* $Id$ */
//view posts
$forum_id 		= dPgetParam($_GET, 'forum_id', 0);
$message_id 	= dPgetParam($_GET, 'message_id', 0);
$post_message = dPgetParam($_GET, 'post_message', 0);

$f = dPgetParam( $_POST, 'f', 0 );

// check permissions
$canRead = !getDenyRead( $m, $forum_id );
$canEdit = !getDenyEdit( $m, $forum_id );

if (!$canRead || ($post_message & !$canEdit)) 
	$AppUI->redirect( 'm=public&amp;a=access_denied' );

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$q  = new DBQuery;
$q->addTable('forums');
$q->addTable('projects', 'p');
$q->addTable('users', 'u');

$q->addQuery('forum_id, forum_name');
$q->addQuery('forum_project,	forum_description, forum_owner');
$q->addQuery('forum_create_date, forum_last_date');
$q->addQuery('forum_message_count, forum_moderated');
$q->addQuery('user_username');
$q->addQuery('contact_first_name, contact_last_name');
$q->addQuery('project_name, project_color_identifier');

$q->addJoin('contacts', 'con', 'contact_id = user_contact');
$q->addWhere('user_id = forum_owner');
$q->addWhere('forum_id = '.$forum_id);
$q->addWhere('forum_project = project_id');
$q->exec(ADODB_FETCH_ASSOC);
$forum = $q->fetchRow();
$forum_name = $forum['forum_name'];
echo db_error();
$q->clear();

$start_date = intval( $forum['forum_create_date'] ) ? new CDate( $forum['forum_create_date'] ) : null;

// setup the title block
$titleBlock = new CTitleBlock( 'Forum', 'support.png', $m, "$m.$a" );
$titleBlock->addCell(
 '<form action="?m=forums&amp;a=viewer&amp;forum_id='.$forum_id.'" method="post" name="filterFrm">' .
	arraySelect( $filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f , true) . 
'</form>', '', '', '');
$titleBlock->show();

$tpl->assign('forum', $forum);
$start_date_formatted = $start_date ? $start_date->format( $df ) : '-';
$tpl->assign('formatted_start_date', $start_date_formatted);
$tpl->displayFile('viewer');

if($post_message)
	include($baseDir . '/modules/forums/post_message.php');
else if($message_id == 0)
	include($baseDir . '/modules/forums/view_topics.php');
else
	include($baseDir . '/modules/forums/view_messages.php');

?>
