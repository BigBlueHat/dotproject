<?php
global $baseDir;

if (! isset($baseDir)) {
	die("You must not call this file directly, it is run automatically on install/upgrade");
}
include_once "$baseDir/includes/config.php";
include_once "$baseDir/includes/main_functions.php";
require_once "$baseDir/includes/db_adodb.php";
include_once "$baseDir/includes/db_connect.php";
include_once "$baseDir/install/install.inc.php";
require_once "$baseDir/classes/permissions.class.php";

/**
 * DEVELOPERS PLEASE NOTE:
 *
 * For the new upgrader/installer to work, this code must be structured
 * correctly.  In general if there is a difference between the from
 * version and the to version, then all updates should be performed.
 * If the $last_udpated is set, then a partial update is required as this
 * is a CVS update.  Make sure you create a new case block for any updates
 * that you require, and set $latest_update to the date of the change.
 *
 * Each case statement should fall through to the next, so that the
 * complete update is run if the last_updated is not set.
 */
function dPupgrade($from_version, $to_version, $last_updated)
{

	global $baseDir;
	$latest_update = '20050314'; // Set to the latest upgrade date.

	if (! $last_updated)
		$last_updated = '00000000';
	
	if ($last_updated < 20050314) {
		// Add the permissions for task_log
		dPmsg("Adding Task Log permissions");
		$perms =& new dPacl;
		$perms->add_object('app', 'Task Logs', 'task_log', 11, 0, 'axo');
		$all_mods = $perms->get_group_id('all', null, 'axo');
		$nonadmin = $perms->get_group_id('non_admin', null, 'axo');
		$perms->add_group_object($all_mods, 'app', 'task_log', 'axo');
		$perms->add_group_object($nonadmin, 'app', 'task_log', 'axo');
	}
	return $latest_update;
}

?>
