<?php
	// Function to scan the event queue and execute any functions required.
	$baseDir = dirname(__FILE__);
	require_once "$baseDir/includes/config.php";
	require_once "$baseDir/includes/main_functions.php";
	require_once "$baseDir/includes/db_connect.php";
	require_once "$baseDir/classes/ui.class.php";
	require_once "$baseDir/classes/event_queue.class.php";
	require_once "$baseDir/classes/query.class.php";

	$AppUI = new CAppUI;

	echo "Scanning Queue ...\n";
	$queue = new EventQueue;
	$queue->scan();
	echo "Done, $queue->event_count events processed\n";
?>
