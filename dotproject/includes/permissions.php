<?php
// This page sets permissions

define( 'PERM_READ', '1' );
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_ALL', '-1' );

function getDenyRead( $mod, $item_id=0 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ))
		| (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] == PERM_DENY)
		| (isset( $perms[$mod][PERM_ALL] ) & $perms[$mod][PERM_ALL] == PERM_DENY);
	if ($item_id > 0) {
		if (isset( $perms[$mod][$item_id] )) {
			$deny = $perms[$mod][$item_id] == PERM_DENY ? 1 : 0;
		} else {
			$deny |= (empty( $perms['all'] ) & empty( $perms[$mod][PERM_ALL] ) & empty( $perms[$mod][$item_id] ));
		}
	}
/*
// DEBUG
echo ' Read:';
echo (empty( $perms['all'] ) & empty($perms['companies'] ));
echo (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] == PERM_DENY);
echo (isset( $perms['companies'][PERM_ALL] ) & $perms['companies'][PERM_ALL] == PERM_DENY);
echo (isset( $perms['companies'][$company_id] ) & $perms['companies'][$company_id] == PERM_DENY);
echo (empty( $perms[$mod][PERM_ALL] ) & empty( $perms[$mod][$item_id] ));
echo "=$deny";
return false;
*/
	return $deny;
}

function getDenyEdit( $mod, $item_id=0 ) {
	GLOBAL $perms;

	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ))
		| (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] <> PERM_EDIT)
		| (isset( $perms[$mod][PERM_ALL] ) & $perms[$mod][PERM_ALL] <> PERM_EDIT);
	if ($item_id > 0) {
		if (isset( $perms[$mod][$item_id] )) {
			$deny = ($perms[$mod][$item_id] <> PERM_EDIT) ? 1 : 0;
		} else {
			$deny |= (empty( $perms['all'] ) & empty( $perms[$mod][PERM_ALL] ) & empty( $perms[$mod][$item_id] ));
		}
	}
/*
// DEBUG
echo " Edit:";
echo (empty( $perms['all'] ) & empty( $perms[$mod] ));
echo (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] <> PERM_EDIT);
echo (isset( $perms[$mod][PERM_ALL] ) & $perms[$mod][PERM_ALL] <> PERM_EDIT);
echo (isset( $perms[$mod][$item_id] ) & $perms[$mod][$item_id] <> PERM_EDIT);
echo (empty( $perms[$mod][PERM_ALL] ) & empty( $perms[$mod][$item_id] ));
echo "=$deny";
return false;
*/
	return $deny;
}

// Logout procedure
if (empty( $user_cookie ) || isset( $logout )) {
	include "./includes/login.php";
	die;
}

list($thisuser_id, $thisuser_first_name, $thisuser_last_name, $thisuser_company) = explode( '|', $thisuser );

if (empty( $m )) {
	$m = "ticketsmith";
}
$noworkee =0;
$ual =0;

// pull permissions into master array
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
