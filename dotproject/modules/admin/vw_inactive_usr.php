<?php
GLOBAL $AppUI, $denyEdit, $where, $orderby;

$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email
FROM users
LEFT JOIN permissions ON user_id = permission_user 
WHERE permission_value IS NULL
	AND (user_username LIKE '$where%' or user_first_name LIKE '$where%' OR user_last_name LIKE '$where%')
ORDER by $orderby
";

$users = db_loadList( $sql );

require "$root_dir/modules/admin/vw_usr.php";
?>