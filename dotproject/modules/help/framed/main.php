<?php
require_once( "./includes/config.php" );
require_once( "./includes/db_connect.php" );
require_once( "./classes/ui.class.php" );

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

$AppUI = new CAppUI;
$AppUI->setConfig( $dHconfig );

$AppUI->setProject( $project_id );

if (isset($_GET['entry_lang'])) {
	$AppUI->user_locale = $_GET['entry_lang'];
}

$m = 'viewer';
@include_once( "./locales/core.php" );

db_connect( $AppUI->project_dbhost, $AppUI->project_dbname, $AppUI->project_dbuser, $AppUI->project_dbpass );

$entry_id = isset($_GET['entry_id']) ? $_GET['entry_id'] : 0;
$entry_type = isset($_GET['entry_type']) ? $_GET['entry_type'] : '';
$entry_link = isset($_GET['entry_link']) ? $_GET['entry_link'] : '';

if ($entry_type == '' || $entry_id == 0 || $entry_link != '') {
	$sql = "
	SELECT entry_id, entry_type
	FROM {$AppUI->project_dbprefix}entries
	WHERE
	";
	$sql .= $entry_link ? "entry_link = '$entry_link'" : "entry_prev = 0";

	if(!($erc = db_exec( $sql ))) {
		echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
	}
	##echo $esql;##
	$row = db_fetch_row( $erc );
	$entry_id = $row[0];
	$entry_type = $row[1];
}

require "./includes/header.php";
require "vw_$entry_type.php";
require "./includes/footer.php";

?>