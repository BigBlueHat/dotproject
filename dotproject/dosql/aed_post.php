<?
if(empty($message_id))$message_id=0;
if(empty($message_moderated))$message_moderated=0;
//IF delete
if($HTTP_POST_VARS["del"]){
	$sql = "delete from forum_messages where message_id = $message_id";
	mysql_query($sql);
	$message  ="Message Deleted";

}
//If update
elseif($HTTP_POST_VARS["message_id"] > 0){

	$sql = "update forum_messages set
	message_title = '$message_title',
	message_body = '$message_body'
	where
	message_id = $message_id
	";
	mysql_query($sql);
	$message  ="Message Updated";
	


}
//If Insert
else{
//Insert into forums
$sql = "insert into forum_messages
(message_forum, message_parent, message_author, message_title, message_date, message_body, message_published)
values 
('$message_forum','$message_parent', '$message_author', '$message_title', now(), '$message_body', '$message_published' )";
mysql_query($sql);

//pull message count and descriptor
$sql = "select count(message_id) as messages,
max(message_date) as latest_reply
from forum_messages where message_forum = $message_forum";
$rc = mysql_query($sql);
$messages = mysql_result($rc, 0, 0);
$latest = mysql_result($rc, 0, 1);

//update forum descriptor
$sql = "update forums set
forum_last_date = '$latest', 
forum_message_count = $messages
where forum_id = $message_forum";
$rc = mysql_query($sql);



$message  ="Message Posted";


}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	//header("Location: ./index.php?m=forums&a=viewposts&forum_id=$message_forum" . $message);
}
?>

