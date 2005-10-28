<?php  /* FORUMS $Id$ */
$AppUI->savePlace();
$sort = dPgetParam($_REQUEST, 'sort', 'asc');
$viewtype = dPgetParam($_REQUEST, 'viewtype', 'normal');
$hideEmail = dPgetConfig('hide_email_addresses', false );

$q  = new DBQuery;
$q->addTable('forums');
$q->addTable('forum_messages');
$q->addQuery('forum_messages.*,	contact_first_name, contact_last_name, contact_email, user_username,
		forum_moderated, visit_user');
$q->addJoin('forum_visits', 'v', "visit_user = {$AppUI->user_id} AND visit_forum = $forum_id AND visit_message = forum_messages.message_id");
$q->addJoin('users', 'u', 'message_author = u.user_id');
$q->addJoin('contacts', 'con', 'contact_id = user_contact');
$q->addWhere("forum_id = message_forum AND (message_id = $message_id OR message_parent = $message_id)");
if (@$dPconfig['forum_descendent_order'] || dPgetParam($_REQUEST,'sort',0)) { $q->addOrder("message_date $sort"); }

$messages = $q->loadList();

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
$crumbs["?m=forums&a=view_pdf&forum_id=$forum_id&message_id=$message_id&sort=$sort&suppressHeaders=1"] = "view PDF file";


$x = false;

$date = new CDate();

if ($viewtype == 'single')
{
	$s = '';
        $first = true;
}

$new_messages = array();

$message_rows = "";
$single_view_message_body = "";

foreach ($messages as $row) {
	$tpl_row = new CTemplate();
        
	$tpl_row->assign('viewtype', $viewtype);

	// Find the parent message - the topic.
        if ($row['message_id'] == $message_id)
                $topic = $row['message_title'];
		
	$q  = new DBQuery;
	$q->addTable('forum_messages');
	$q->addTable('users');
	$q->addQuery('DISTINCT contact_email, contact_first_name, contact_last_name, user_username');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact');
	$q->addWhere('users.user_id = '.$row["message_editor"]);
	$editor = $q->loadList();

	$date = intval( $row["message_date"] ) ? new CDate( $row["message_date"] ) : null;

	$style = $x ? 'background-color:#eeeeee' : '';

	$tpl_row->assign('style', $style);
	$tpl_row->assign('row', $row);
	$tpl_row->assign('editor', $editor[0]);
	$tpl_row->assign('hideEmail', $hideEmail);
	$tpl_row->assign('user_id', $AppUI->user_id);
	$tpl_row->assign('message_count', count($messages));

	if ($row['visit_user'] != $AppUI->user_id) {
		$new_messages[] = $row['message_id'];
	}

	$formatted_date = $date->format( "$df $tf" );
	$tpl_row->assign('formatted_date', $formatted_date);

	//the following users are allowed to edit/delete a forum message: 1. the forum creator  2. a superuser with read-write access to 'all' 3. the message author
	$canEdit = $perms->checkModuleItem('forums', 'edit', $row['message_id']);
	if ( $canEdit && ( $AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $perms->checkModule('admin', 'edit'))) {
		$tpl_row->assign('show_edit_controls', TRUE);
	}
	else
	{
		$tpl_row->assign('show_edit_controls', FALSE);
	}

	$tpl_row->assign('first', $first);
        if ($first)
        {
                $first = false;
        }

	if ($viewtype != 'single')
		$x = !$x;

	if ($viewtype == 'single')
	{
		$single_view_message_body .= ' 
			<div class="message" id="'.$row["message_id"].'" style="display: none">
				'.nl2br($row["message_body"]).'
			</div>
		';
	}	
	$message_rows .= $tpl_row->fetchFile('view_messages.row');
	unset($tpl_row);
}

$tpl->assign('viewtype', $viewtype);
$tpl->assign('canEdit', $canEdit);

$thispage = "?m=$m&a=viewer&forum_id=$forum_id&message_id=$message_id&sort=$sort";
$tpl->assign('thispage', $thispage);
$tpl->assign('breadCrumbs', breadCrumbs( $crumbs ));
$sort = ($sort == 'asc') ? 'desc' : 'asc'; 
$tpl->assign('sort', $sort); 
if ($viewtype == 'single') $tpl->assign('message_bodies', $single_view_message_body); 
$tpl->assign('message_rows', $message_rows);
$tpl->assign('forum_id', $forum_id); 
$tpl->assign('message_id', $message_id);
$tpl->displayFile('view_messages');

/*
if ($viewtype == 'single')
        echo $side . '</td>' . $s;
*/

  // Now we need to update the forum visits with the new messages so they don't show again.
  foreach ($new_messages as $msg_id) {
	$q  = new DBQuery;
	$q->addTable('forum_visits');
	$q->addInsert('visit_user', $AppUI->user_id);
	$q->addInsert('visit_forum', $forum_id);
	$q->addInsert('visit_message', $msg_id);
	$q->exec();
	$q->clear();
  }
?>
