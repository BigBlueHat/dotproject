<?php
// This page sets permissions

//Logout procedure
if(empty($user_cookie) || isset($logout)){
 include "./includes/login.php";
 die;
}

if(empty($m))$m = "ticketsmith";
$noworkee =0;
$ual =0;
$perms ="xxx";
$psql = "
Select user_id, 
lower(permission_grant_on) 
from users, permissions 
where user_id = $user_cookie 
and user_id = permission_user";

$prc = mysql_query($psql);
$ual = mysql_num_rows($prc);




while($prow = mysql_fetch_array($prc))
{
	$ual = $prow[0];
	$perms.= ";" . $prow[1] . ";";
}


if($ual < 1){	
	setcookie("m", "ticketsmith");
	setcookie("user_cookie", "0");
	include "./includes/login.php";
	die;
}


?>
