<?php
// This page sets permissions

define( 'PERM_READ', '1' );
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_ALL', '-1' );

function getDenyRead( $mod, $item_id=0 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ));
	$deny |= (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] == PERM_DENY);
	if (isset( $perms[$mod] )) {
		if (isset( $perms[$mod][PERM_ALL] )) {
			$deny |= ($perms[$mod][PERM_ALL] == PERM_DENY);
		}
	}
	if ($item_id > 0) {
		if (isset( $perms[$mod][$item_id] )) {
			$deny = $perms[$mod][$item_id] == PERM_DENY ? 1 : 0;
		} else {
			$deny |= (empty( $perms['all'] ) & empty( $perms[$mod][PERM_ALL] ) & empty( $perms[$mod][$item_id] ));
		}
	}
	return $deny;
}

function getDenyEdit( $mod, $item_id=0 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ));
	$deny |= (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] <> PERM_EDIT);
	if (isset( $perms[$mod] )) {
		if (isset( $perms[$mod][PERM_ALL] )) {
			$deny |= ($perms[$mod][PERM_ALL] <> PERM_EDIT);
		}
	}
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

/*
$user_cookie = isset($HTTP_COOKIE_VARS['user_cookie']) ? $HTTP_COOKIE_VARS['user_cookie'] : 0;
$thisuser = isset($HTTP_COOKIE_VARS['thisuser']) ? $HTTP_COOKIE_VARS['thisuser'] : 0;

// Logout procedure
if ($user_cookie < 1 || $thisuser < 1 || isset( $logout )) {
	include "./includes/login.php";
	die;
}

list($thisuser_id, $thisuser_first_name, $thisuser_last_name, $thisuser_company, $thisuser_dept, $hash) = explode( '|', $thisuser );

if ($hash != md5( $thisuser_first_name.$secret.$thisuser_last_name )) {
	include "./includes/login.php";
	die;
}

//*** these bits seem to be left over from the creation of the universe
if (empty( $m )) {
	$m = "ticketsmith";
}
$noworkee =0;
*/

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


// *** this next bit doesn't seem to have any relevance
if($ual < 1){
	setcookie("m", "ticketsmith");
	setcookie("user_cookie", "0");
	include "./includes/login.php";
	die;
}
?>
