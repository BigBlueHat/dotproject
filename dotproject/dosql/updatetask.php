<?
//update task
if(empty($task_id))$task_id = 0;
if(empty($already_worked))$already_worked = 0;
if(empty($worked))$worked = 0;
if(empty($complete))$complete = 0;
if(empty($wascomplete))$wascomplete = 0;
$worked = intval($worked);
$realwork = $already_worked + $worked;

$sql1 = "update tasks set 
task_hours_worked = $realwork, 
task_precent_complete = $complete";
if($complete ==100 and $wascomplete != 100){
	$sql1.= ", task_end_date = now() ";
}


$sql1.= " where task_id = $task_id";
mysql_query($sql1);
echo mysql_error();

$sql2 = "insert into task_comments 
(comment_task ,comment_title, comment_body, comment_user, comment_date, comment_unique_id )
values
('$task_id' ,'Status Update', '$comments', $user_cookie, '" . strftime("%Y-%m-%d %H:%M:%S", time()) . "', '$uniqueid')";
mysql_query($sql2);
//echo mysql_error();

$csql = "select user_email, user_first_name, user_last_name
from users where users.user_id = $user_cookie";
$query = mysql_query($csql);
if (mysql_error())
	$sql = $csql;
$editor = mysql_fetch_array($query);

$usql = "select
tasks.task_id,
task_name,
task_description,
creator.user_email as creator_email,
creator.user_first_name as creator_first_name,
creator.user_last_name as creator_last_name,
owner.user_email as owner_email,
owner.user_first_name as owner_first_name,
owner.user_last_name as owner_last_name,
assignee.user_id as assignee_id,
assignee.user_email as assignee_email,
assignee.user_first_name as assignee_first_name,
assignee.user_last_name as assignee_last_name
	from tasks
	left join user_tasks on user_tasks.task_id = tasks.task_id
	left join users creator on creator.user_id = tasks.task_owner
	left join users owner on owner.user_id = tasks.task_creator
	left join users assignee on assignee.user_id = user_tasks.user_id
where tasks.task_id = $task_id";

$query = mysql_query($usql);
if (mysql_error())
	$sql = $usql;
// For each user, email them an update, similar to the update that
// ticketsmith uses.
$row_count = mysql_num_rows($query);
$mail_header = "From: " . $admin_email . "\r\n"
. "Content-Type: text/html\r\n"
. "Content-Transfer-Encoding: 8bit\r\n"
. "Mime-Version: 1.0\r\n"
. "X-Mailer: Dotproject";
$subject = "Task $task_id Updated";
$mail_body = "<head><title>$subject</title></head>\n"
. "<body>\n"
. "<table bgcolor='#ffffff' cellpadding=4 cellspacing=1>\n"
. "<tr bgcolor='#eeeeee'><th colspan=2>$subject</th></tr>\n"
. "<tr><td>Task ID</td><td><a href='"
. $base_url
. "/index.php?m=tasks&a=view&task_id=$task_id'>$task_id</a></td></tr>\n";
for ($i = 0; $i < $row_count; $i++) {
	$row = mysql_fetch_array($query);
	if ($row['assignee_id'] != $user_cookie) {
		$mail_text = $mail_body
		. "<tr><td>Title</td><td>"
		. $row['task_name']
		. "&nbsp;</tr>\n<tr><td>Description</td><td>"
		. str_replace(chr(10), "<br>", $row['task_description'])
		. "&nbsp;</td></tr>\n<tr><td>Created by</td><td><a href='mailto:"
		. $row['creator_email']
		. "'>"
		. $row['creator_first_name']
		. "&nbsp;"
		. $row['creator_last_name' ]
		. "</a></tr>\n<tr><td>Owned by</td><td><a href='mailto:"
		. $row['owner_email']
		. "'>"
		. $row['owner_first_name']
		. "&nbsp;"
		. $row['owner_last_name']
		. "</a></tr>\n<tr><td>Updated by</td><td><a href='mailto:"
		. $editor['user_email']
		. "'>"
		. $editor['user_first_name']
		. "&nbsp;"
		. $editor['user_last_name']
		. "</a></tr>\n<tr><td>Comments Added</td><td>"
		. str_replace(chr(10), "<BR>", $comments)
		. "&nbsp;</td></tr>\n</table></body>\n";
		mail($row['assignee_email'], $subject, $mail_text, $mail_header);
	}
}

?>
