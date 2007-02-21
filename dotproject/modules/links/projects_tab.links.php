<?php /* PROJECTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit;

$showProject = false;
require( DP_BASE_DIR . '/modules/links/index_table.php' );
?>