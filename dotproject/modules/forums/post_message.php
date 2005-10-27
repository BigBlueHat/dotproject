<?php /* FORUMS $Id$ */
// Add / Edit forum
$message_id = isset( $_GET['message_id'] ) ? $_GET['message_id'] : 0;
$message_parent = isset( $_GET['message_parent'] ) ? $_GET['message_parent'] : -1;
$forum_id = dPgetParam($_REQUEST, 'forum_id', 0);

//Pull forum information
$q  = new DBQuery;
$q->addTable('forums');
$q->addTable('projects');
$q->addQuery('forum_name, forum_owner, forum_moderated, project_name, project_id');
$q->addWhere("forums.forum_id = $forum_id");
$q->addWhere('forums.forum_project = projects.project_id');
$res = $q->exec();
$forum_info = $q->fetchRow();
$q->clear();
echo db_error();

//pull message information
$q  = new DBQuery;
$q->addTable('forum_messages');
$q->addQuery('forum_messages.*, user_username');
$q->addJoin('users', 'u', 'message_author = u.user_id');
$q->addWhere('message_id = '. ($message_id ? $message_id : $message_parent));
$res = $q->exec();
echo db_error();
$message_info = $q->fetchRow();
$q->clear();

//pull message information from last response 
if ($message_parent != -1)
{
	$q->addTable('forum_messages');
	$q->addWhere('message_parent = '. ($message_id ? $message_id : $message_parent));
	$q->addOrder('message_id DESC'); // fetch last message first
	$q->setLimit(1);
	$res = $q->exec();
    echo db_error();
    $last_message_info = $q->fetchRow();
    if (!$last_message_info) { // if it's first response, use original message
        $last_message_info =& $message_info;
        $last_message_info["message_body"] = wordwrap(@$last_message_info["message_body"], 50, "\n> ");
    }
    else {
        $last_message_info["message_body"] = str_replace("\n", "\n> ", @$last_message_info["message_body"]);
    }
		$q->clear();
}

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
if ($message_parent > -1) {
	$crumbs["?m=forums&a=viewer&forum_id=$forum_id&message_id=$message_parent"] = "this topic";
}

$tpl->assign('canEdit', $canEdit);
$tpl->assign('breadCrumbs', breadCrumbs( $crumbs ));

$tpl->assign('forum_id', $forum_id);
$tpl->assign('forum_info', $forum_info);

$message_author = (isset($message_info["message_author"]) && ($message_id || $message_parent < 0)) ? $message_info["message_author"] : $AppUI->user_id;
$message_editor = (isset($message_info["message_author"]) && ($message_id || $message_parent < 0)) ? $AppUI->user_id : '0';
$message_username = dPgetUsername($message_info['user_username']);
$message_body_text = (($message_id == 0) and ($message_parent != -1)) ? "\n>"  .  $last_message_info["message_body"] . "\n" : $message_info["message_body"];

$tpl->assign('message_id', $message_id);
$tpl->assign('message_parent', $message_parent);
$tpl->assign('message_info', $message_info);
$tpl->assign('message_author', $message_author);
$tpl->assign('message_editor', $message_editor); 
$tpl->assign('message_username', $message_username);
$tpl->assign('message_body', $message_body_text);

$date = intval( $message_info["message_date"] ) ? new CDate( $message_info["message_date"] ) : new CDate();
$formatted_date = $date->format( "$df $tf" );
$tpl->assign('formatted_date', $formatted_date);

if ($AppUI->user_id == $message_info['message_author'] || $AppUI->user_id == $forum_info["forum_owner"] || $message_id ==0 || (!empty($perms['all']) && !getDenyEdit('all')) )
{
	$tpl->assign('show_submit_button', TRUE);
}
else
{
	$tpl->assign('show_submit_button', FALSE);
} 

$tpl->displayFile('post_message');
?>
