<?php /* INCLUDES $Id$ */
// This page sets permissions

define( 'PERM_READ', '1' );
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_ALL', '-1' );

function getReadableModule() {
	$sql = "SELECT mod_directory FROM modules WHERE mod_active > 0 ORDER BY mod_ui_order";
	$modules = db_loadColumn( $sql );
	foreach ($modules as $mod) {
		if (!getDenyRead($mod)) {
			return $mod;
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

function winnow( $mod, $key ) {
	GLOBAL $perms;

	$in = array();
	$out = array();
	$all_in = false;
	$all_out = false;

	// do some winnowing
	if (@$perms[$mod]) {
		foreach ($perms[$mod] as $k=>$v) {
			if ($k == PERM_ALL) {
				if ($v == PERM_DENY) {
					$all_out = true;
				} else {
					$all_in = true;
				}
			} else {
				if ($v == PERM_DENY) {
					$out[] = $k;
				} else {
					$in[] = $k;
				}
			}
		}
	} else if (@$perms['all']) {
		if ($perms['all'] == PERM_DENY) {
			$all_out = true;
		} else {
			$all_in = true;
		}
	}
	// now compile as query
	$where = array();
	if (count( $out ) > 0) {
		$where[] = "$key NOT IN (" . implode( ',', $out ).")";
	}
	if (count( $in ) > 0) {
		$where[] = "$key IN (" . implode( ',', $in ).")";
	}
	if ($all_in) {
		$where[] = "$key > 0";
	}
	if ($all_out) {
		$where[] = "$key = 0";
	}
	return "(" . implode( ' AND ', $where ). ")";
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
