<?php
GLOBAL $vm, $denyEdit;

//set defaults
$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'user_username';

$usql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email
FROM users
LEFT JOIN permissions ON user_id = permission_user
WHERE permission_value IS NOT NULL
ORDER by $orderby
";
$urow = mysql_query( $usql );

require "vw_usr.php";
?>

