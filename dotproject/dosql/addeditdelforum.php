<?
if(empty($forum_id))$forum_id=0;
if(empty($forum_moderated))$forum_moderated=0;
//IF delete
if($HTTP_POST_VARS["del"]){
	$sql = "delete from forums where forum_id = $forum_id";
	mysql_query($sql);
	$message  ="Forum Deleted";
	$sql = "delete from forum_messages where message_forum = $forum_id";
	mysql_query($sql);
	$message.="<BR>Messages Deleted";

}
//If update
elseif($HTTP_POST_VARS["forum_id"] > 0){

	$sql = "update forums set
	forum_name = '$forum_name',
	forum_description = '$forum_description',
	forum_project = '$forum_project',
	forum_moderated = '$forum_moderated',
	forum_owner = '$forum_owner'
	where
	forum_id = $forum_id
	";
	mysql_query($sql);
	$message  ="Forum Updated";
	


}
//If Insert
else{

$sql = "insert into forums
( forum_project  , forum_owner  , forum_name  , forum_create_date  , forum_description  , forum_moderated)
values 
('$forum_project' , '$forum_owner' , '$forum_name' , now() , '$forum_description' , '$forum_moderated')";


mysql_query($sql);
$message  ="Forum Inserted";


}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	header("Location: ./index.php?m=forums&message=" . $message);
}
?>

