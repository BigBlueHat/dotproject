<?php
// This page sets permissions

define( 'PERM_READ', '1' );
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_ALL', '-1' );

function getDenyRead( $mod, $item_id=-1 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ))
		| (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] == PERM_DENY)
		| (isset( $perms[$mod][PERM_ALL] ) & $perms[$mod][PERM_ALL] == PERM_DENY);
	if ($item_id > -1) {
		$deny |= (isset( $perms[$mod][$item_id] ) & $perms[$mod][$item_id] == PERM_DENY);
	}
	return $deny;
}

function getDenyEdit( $mod, $item_id=-1 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$m] ))
		| (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] <> PERM_EDIT)
		| (isset( $perms[$mod][PERM_ALL] ) & $perms[$mod][PERM_ALL] <> PERM_EDIT);
	if ($item_id > -1) {
		$deny |= (isset( $perms[$mod][$item_id] ) & $perms[$mod][$item_id] <> PERM_EDIT);
	}
	return $deny;
}

// Logout procedure
if (empty( $user_cookie ) || isset( $logout )) {
	include "./includes/login.php";
	die;
}

if (empty( $m )) {
	$m = "ticketsmith";
}
$noworkee =0;
$ual =0;
/* deprecated...
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
*/
// alternative method
$psql = "
select permission_grant_on g, permission_item i, permission_value v
from permissions
where permission_user = $user_cookie
";

$perms = array();
$prc = mysql_query($psql);
$ual = mysql_num_rows($prc);

// build the master permissions array
while ($prow = mysql_fetch_array( $prc, MYSQL_ASSOC )) {
	$perms[$prow['g']][$prow['i']] = $prow['v'];
}

if($ual < 1){
	setcookie("m", "ticketsmith");
	setcookie("user_cookie", "0");
	include "./includes/login.php";
	die;
}
?>
