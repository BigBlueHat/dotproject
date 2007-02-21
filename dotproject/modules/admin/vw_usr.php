<?php /* ADMIN  $Id$ */ 
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $canDelete, $canEdit, $stub, $where, $orderby, $tpl;

$q  = new DBQuery;
$q->addQuery('DISTINCT(user_id)');
$q->addQuery('user_username');
$q->addQuery('permission_user');
$q->addQuery('contact_last_name, contact_first_name');
$q->addQuery('contact_email');
$q->addQuery('contact_company');
$q->addQuery('company_name');
$q->addTable('users', 'u');
$q->addJoin('contacts', 'con', 'user_contact = contact_id');
$q->addJoin('companies', 'com', 'contact_company = company_id');
$q->addJoin('permissions', 'per', 'user_id = permission_user');

if ($stub) {
	$q->addWhere("(UPPER(user_username) LIKE '$stub%' or UPPER(contact_first_name) LIKE '$stub%' OR UPPER(contact_last_name) LIKE '$stub%')");
} else if ($where) {
	$where = $q->quote("%$where%");
	$q->addWhere("(UPPER(user_username) LIKE $where or UPPER(contact_first_name) LIKE $where OR UPPER(contact_last_name) LIKE $where)");
}

$q->addOrder($orderby);
$users = $q->loadList();
$tab = dPgetParam($_REQUEST, "tab", 0);
$canLogin = ($tab == 0); // Active = 0, Inactive = 1;
			
$perms =& $AppUI->acl();
foreach ($users as $k => $row)
{
	if ($perms->isUserPermitted($row['user_id']) != $canLogin)
		unset($users[$k]);
	else 
	{
		$rows[$k]['display'] = trim(addslashes($row['contact_first_name'] . ' ' . $row['contact_last_name']));
		if (empty($rows[$k]['display']))
		$rows[$k]['display'] = $row['user_username'];

		$q  = new DBQuery;
		$q->addTable('user_access_log', 'ual');
		$q->addQuery("user_access_log_id, ( unix_timestamp( now( ) ) - unix_timestamp( date_time_in ) ) / 3600 as 		hours, ( unix_timestamp( now( ) ) - unix_timestamp( date_time_last_action ) ) / 3600 as 		idle, if(isnull(date_time_out) or date_time_out ='0000-00-00 00:00:00','1','0') as online");
		$q->addWhere('user_id =' . $row['user_id']);
		$q->addOrder('user_access_log_id DESC');
		$q->setLimit(1);
		list ($user_log) = $q->loadList();
		
		if (!$user_log)
			$users[$k]['online'] = -1;
		else
		{
			$users[$k]['online'] 	= $user_log['online'];
			$users[$k]['hours'] 	= $user_log['hours'];
			$users[$k]['idle'] 		= $user_log['idle'];
		}
	}
}

$tpl->assign('tab', 			$tab);
$tpl->assign('canDelete', $canDelete);
$tpl->assign('canEdit', 	$canEdit);

$tpl->displayList('admin', $users);
?>