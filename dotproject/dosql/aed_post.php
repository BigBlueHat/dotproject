<?php
if (empty( $message_id )) {
	$message_id = 0;
}
if (empty( $message_moderated )) {
	$message_moderated=0;
}
if ($HTTP_POST_VARS["del"]) {
// delete case
	$sql = "delete from forum_messages where message_id = $message_id";
	mysql_query($sql);
	$message  ="Message Deleted";
} else if ($HTTP_POST_VARS["message_id"] > 0) {
// update case
	$sql = "update forum_messages set
	message_title = '$message_title',
	message_body = '$message_body'
	where
	message_id = $message_id
	";
	mysql_query($sql);
	$message = "Message Updated";
} else {
// Insert into forums
	$message_body = htmlspecialchars( $message_body );
	$sql = "insert into forum_messages
	(message_forum, message_parent, message_author, message_title, message_date, message_body, message_published)
	values 
	('$message_forum','$message_parent', '$message_author', '$message_title', now(), '$message_body', '$message_published' )";
	mysql_query($sql);
	$new_id = mysql_insert_id();

	//pull message count and descriptor
	$sql = "select count(message_id) as messages,
	max(message_date) as latest_reply
	from forum_messages where message_forum = $message_forum";
	$rc = mysql_query($sql);
	$messages = mysql_result($rc, 0, 0);
	$latest = mysql_result($rc, 0, 1);

	//update forum descriptor
	$sql = "
	UPDATE forums 
	SET
		forum_last_date = '$latest', 
		forum_message_count = $messages,
		forum_last_id = $new_id
	WHERE forum_id = $message_forum";
	$rc = mysql_query($sql);

	$message  ="Message Posted";

	sendWatchMail($new_id, $message_parent, $message_forum, $message_title, $message_body);
}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	header("Location: ./index.php?m=forums&a=viewer&forum_id=$message_forum&message=$message");
}
?>

