<?php
include "./includes/config.php";
include "./includes/db_connect.php";

//Check Login
$psql = "select
user_id, user_first_name, user_last_name, user_company
from users,  permissions
where user_username = '$username'
and user_password = password('$password') 
and users.user_id = permissions.permission_user
and permission_value <> 0
";

$prc = mysql_query($psql);

//Pull record, write bad login if exists
if (!$row = mysql_fetch_array( $prc, MYSQL_NUM )) {
	$message  = "Login Failed.";
	include "./includes/login.php";
	die;
}

$row[] = md5( $row[1].$secret.$row[2] );

setcookie( "user_cookie", $row[0] );
setcookie( "thisuser", implode( '|', $row ) );
?>
<script language="JavaScript">
window.location = "./index.php";
</script>

