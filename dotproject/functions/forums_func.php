<?php

$filters = array( '- Filters -' );

if ($a == 'viewer') {
	array_push( $filters,
		'My Watched',
		'Last 30 days'
	);
} else {
	array_push( $filters,
		'My Forums',
		'My Watched',
		'My Projects',
		'My Company'
	);
}

function sendWatchMail($message_id, $message_parent, $message_forum, $message_title, $message_body) {
	GLOBAL $base_url, $thisuser_id;
	$subj_prefix = "Dotproject forum activity:";
	$body_msg = "There has been activity in a forum you are watching.";
	$from = "Dotproject forum watch";

	$sql = "
	SELECT DISTINCT user_email, user_id, user_first_name, user_last_name
	FROM users, forum_watch
	WHERE user_id = watch_user
		AND (watch_forum = $message_forum OR watch_topic = $message_parent)
	";
##echo "<pre>$sql</pre>";##
	if (!($rc = mysql_query( $sql ))) {
		return;
	}
	if (mysql_num_rows($rc) < 1) {
		return;
	}
	$mail_header = "Content-Type: text/html\r\n"
	. "Content-Transfer-Encoding: 8bit\r\n"
	. "Mime-Version: 1.0\r\n"
	. "X-Mailer: Dotproject"
	;

	$subject = "$subj_prefix $message_title";

	$mail_body = "<head><title>$message_title</title>\n"
	."<style type=text/css>\n"
	."body,td,th { font-family: verdana,helvetica,arial,sans-serif; font-size:12px; }\n"
	."</style>\n"
	."</head>\n"
	. "<body>$body_msg\n"
	. "<p><a href='$base_url/index.php?m=forums&a=viewer&forum_id=$message_forum'>$message_title</a>\n"
	. "<p><pre>$message_body</pre>\n"
	. "</body>\n";

	while ($row = mysql_fetch_array( $rc, MYSQL_ASSOC )) {
		//if ($row['user_id'] != $thisuser_id) {
			$to = '"'.$row['user_first_name'].' '.$row['user_last_name'].'" <'.$row['user_email'].'>';
			//mail( $to, $subject, $mail_body, "From: $from\r\n".$mail_header );
			echo "<pre>";
			echo "TO=$to\n";
			echo "SUBJECT=$subject\n";
			echo "BODY=$mail_body\n";
			echo "FROM=$from\n";
		//}
	}
/*	
*/
}

?>
