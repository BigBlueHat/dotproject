<?php /* INCLUDES $Id$ */
// This page sets permissions

define( 'PERM_READ', '1' );
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_ALL', '-1' );

function getReadableModule() {
	$sql = "SELECT mod_name FROM modules WHERE mod_active > 0 ORDER BY mod_ui_order";
	$modules = db_loadColumn( $sql );
	foreach ($modules as $mod) {
		if (!getDenyRead($mod)) {
			return strtolower($mod);
		}
	}
	return null;
}

function getDenyRead( $mod, $item_id=0 ) {
	GLOBAL $perms;
	$deny = (empty( $perms['all'] ) & empty( $perms[$mod] ));
	if (isset( $perms['all'] )) {
		$deny |= (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] == PERM_DENY);
	}
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
	if (isset( $perms['all'] )) {
		$deny |= (isset( $perms['all'][PERM_ALL] ) & $perms['all'][PERM_ALL] <> PERM_EDIT);
	}
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
	return $deny;
}

// pull permissions into master array
$sql = "
SELECT permission_grant_on g, permission_item i, permission_value v
FROM permissions
WHERE permission_user = $AppUI->user_id
";

$perms = array();
$res = db_exec( $sql );

// build the master permissions array
while ($row = db_fetch_assoc( $res )) {
	$perms[$row['g']][$row['i']] = $row['v'];
}
?>
