<?php /* ADMIN $Id$ */
GLOBAL $AppUI, $canEdit, $where, $orderby;

$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name
FROM users
LEFT JOIN permissions ON user_id = permission_user 
LEFT JOIN companies ON company_id = user_company
WHERE permission_value IS NULL
	AND (user_username LIKE '$where%' or user_first_name LIKE '$where%' OR user_last_name LIKE '$where%')
ORDER by $orderby
";

$users = db_loadList( $sql );

require "{$AppUI->cfg['root_dir']}/modules/admin/vw_usr.php";
?>