<?php /* INCLUDES $Id$ */
/*
 * This page handles permissions
 * 
 * Since permissions are propagated and overwritten from general
 * to specific items, 3 type of permissions are stored in the DB:
 * - read
 * - edit
 * - denied
 *
 * This way, if you grant edit permissions on a project and
 * deny access to an item of this project, you will be able
 * to access any item excluding the one you denied.
 */
// Permission flags used in the DB
define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_READ', '1' );

define( 'PERM_ALL', '-1' );

// TODO: getDeny* should return true/false instead of 1/0

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

/**
 * This function is used to check permissions.
 */
function checkFlag($flag, $perm_type, $old_flag) {
	if($old_flag) {
		// check if permissions are implicity denied
		return ($flag == $PERM_DENY)?0:1;
	} else {
		if($perm_type == PERM_READ) {
			return ($flag != PERM_DENY)?1:0;
		} else {
			return ($flag == $perm_type)?1:0;
		}
	}
}

/**
 * This function checks certain permissions for
 * a given module and optionally an item_id.
 * 
 * $perm_type can be PERM_READ or PERM_EDIT
 */
function isAllowed($perm_type, $mod, $item_id = 0) {
	GLOBAL $perms;   
	
	/*** Special hardcoded permissions ***/
	
	if ($mod == 'public') return 1;
	
	/*** Manually granted permissions ***/
	
	// TODO: I didn't understand this.
	// If $perms['all'] or $perms[$mod] is not empty we have full permissions???
	// If we just set a deny on a item we get read/edit permissions on the full module.
	$allowed = ! empty( $perms['all'] ) | ! empty( $perms[$mod] );
	
	// check permission on all modules
	if ( isset($perms['all']) && $perms['all'][PERM_ALL] ) {
		$allowed = checkFlag($perms['all'][PERM_ALL], $perm_type, $allowed);
	}

	// check permision on this module
	if ( isset($perms[$mod]) && isset($perms[$mod][PERM_ALL]) ) {
		$allowed = checkFlag($perms[$mod][PERM_ALL], $perm_type, $allowed);
	}
	
    // check permision for the item on this module
	if ($item_id > 0) {
		if ( isset($perms[$mod][$item_id]) ) {
			$allowed = checkFlag($perms[$mod][$item_id], $perm_type, $allowed);
		}
	}
		
	/*** Permission propagations ***/
			
	// if we have access on the project => we have access on its tasks
	if ( $mod == 'tasks' && !$allowed ) {
		/*
		if ( $item_id > 0 ) {			
			// get tasks project id
			$sql = "SELECT task_project FROM tasks WHERE task_id = $item_id";
			$project_id = db_loadResult($sql);
			$allowed = isAllowed( $perm_type, "projects", $project_id );
		}
		*/
			
		// HACK: I'm allowing access on the tasks module when having
		// access on the projects module. Why? I granted someone access
		// to a project and want to allow him to see its tasks, but
		// index.php requires access to the tasks module in order
		// to access sub-modules (&a=view)
		$allowed = isAllowed( $perm_type, "projects" );
	}
	
	/*** TODO: Specificaly denied items ***/
//	echo "$perm_type $mod $item_id $allowed<br>";
	
	return $allowed;
}

function getDenyRead( $mod, $item_id = 0 ) {
	return !isAllowed(PERM_READ, $mod, $item_id);
}

function getDenyEdit( $mod, $item_id=0 ) {
	return !isAllowed(PERM_EDIT, $mod, $item_id);
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
