<?php /* ADMIN $Id$ */
GLOBAL $dPconfig, $canEdit, $stub, $where, $orderby;

$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name,
	permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
WHERE permission_value IS NOT NULL
";

if ($stub) {
	$sql .= "\n	AND (UPPER(user_username) LIKE '$stub%' or UPPER(user_first_name) LIKE '$stub%' OR UPPER(user_last_name) LIKE '$stub%')";
} else if ($where) {
	$sql .= "\n	AND (UPPER(user_username) LIKE '%$where%' or UPPER(user_first_name) LIKE '%$where%' OR UPPER(user_last_name) LIKE '%$where%')";
}

$sql .= "\nORDER by $orderby";

$users = db_loadList( $sql );

require "{$dPconfig['root_dir']}/modules/admin/vw_usr.php";
?>

