<?php /* ADMIN $Id$ */
GLOBAL $dPconfig, $canEdit, $stub, $where, $orderby;

$sql = "
SELECT DISTINCT(user_id), user_username, contact_last_name, contact_first_name, permission_user, contact_email, company_name, contact_company
FROM users
LEFT JOIN contacts ON contact_id = user_contact
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = contact_company
";

if ($stub) {
	$sql .= "\n	WHERE (UPPER(user_username) LIKE '$stub%' or UPPER(contact_first_name) LIKE '$stub%' OR UPPER(contact_last_name) LIKE '$stub%')";
} else if ($where) {
	$sql .= "\n	WHERE (UPPER(user_username) LIKE '%$where%' or UPPER(contact_first_name) LIKE '%$where%' OR UPPER(contact_last_name) LIKE '%$where%')";
}

$sql .= "\nORDER by $orderby";

$users = db_loadList( $sql );
$canLogin = false;

require "{$dPconfig['root_dir']}/modules/admin/vw_usr.php";
?>