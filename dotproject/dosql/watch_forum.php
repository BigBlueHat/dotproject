<?php
##
## Change forum watches
##

$watch = isset( $_POST['watch'] ) ? $_POST['watch'] : 0;
$message = '';

if ($watch) {
	// clear existing watches
	$sql = "DELETE FROM forum_watch WHERE watch_user = $thisuser_id AND watch_$watch IS NOT NULL";
	if (!mysql_query($sql)) {
		$message = 'An error occurred cleaning up the watch table<br>'.mysql_error();
	} else {
		$sql = '';
		foreach ($_POST as $k => $v) {
			if (strpos($k, 'forum_') !== FALSE) {
				$sql = "INSERT INTO forum_watch (watch_user,watch_$watch) VALUES ($thisuser_id,".substr( $k, 6 ).")";
				if (!mysql_query($sql)) {
					$message .= mysql_error().'<br>';
				}
			}
		}
	}
} else {
	$message = 'Incorrect watch type passed to sql handler.';
}

?>