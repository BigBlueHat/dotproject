<?php /* FORUMS $Id$ */
// Add / Edit forum

$forum_id = intval( dPgetParam( $_GET, 'forum_id', 0 ) );
$perms =& $AppUI->acl();

// check permissions for this record
$canAdd = $perms->checkModule( $m, 'add');
$canEdit = $perms->checkModuleItem( $m, 'edit', $forum_id );
if (!$canEdit || !$canAdd) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the companies class to retrieved denied projects
require_once( $AppUI->getModuleClass( 'projects' ) );

$forum_id = intval( dPgetParam( $_GET, 'forum_id', 0 ) );

//Pull forum information
$q =& new DBQuery;
$q->addTable('forums');
$q->addWhere("forums.forum_id = $forum_id");
$res = $q->exec();
echo db_error();
$forum_info = db_fetch_assoc( $res );

$status = isset( $forum_info["forum_status"] ) ? $forum_info["forum_status"] : -1;


// get any project records denied from viewing
$projObj = new CProject();

//Pull project Information
$q =& new DBQuery;
$q->addTable('projects');
$q->addQuery('project_id, project_name');
$q->addWhere('project_status != 7');
$q->addOrder('project_name');
$projObj->setAllowedSQL($AppUI->user_id, $q);
if (isset($company_id))
	$q->addWhere("project_company = $company_id");
$projects = array( '0' => '' ) + $q->loadHashList();
echo db_error();

$perms =& $AppUI->acl();
$permittedUsers =& $perms->getPermittedUsers();
$users = array( '0' => '' ) + $permittedUsers;
// setup the title block
$ttl = $forum_id > 0 ? "Edit Forum" : "Add Forum";
$titleBlock = new CTitleBlock( $ttl, 'support.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=forums", "forums list" );
if ($canDelete) {
	$titleBlock->addCrumbRight(
		'<a href="javascript:delIt()">' . $AppUI->_('delete forum')
			. '&nbsp;<img align="absmiddle" src="' . dPfindImage( 'stock_delete-16.png', $m ) . '" width="16" height="16" alt="" border="0" /></a>'
	);
}
$titleBlock->show();

$tpl->assign('forum_id', $forum_id);
$tpl->assign('forum_unique_update', uniqid(""));

if ($AppUI->user_id == $forum_info["forum_owner"] || $forum_id == 0)
{
	$tpl->assign('show_submit_button', TRUE);
}
else
{
	$tpl->assign('show_submit_button', FALSE);
}

$tpl->assign('projects', $projects);
$tpl->assign('users', $users);

$forum_owner_id = $forum_info['forum_owner'] ? $forum_info['forum_owner'] : $AppUI->user_id;
$tpl->assign('forum_owner_id', $forum_owner_id);

$tpl->displayAddEdit($forum_info);

?>
