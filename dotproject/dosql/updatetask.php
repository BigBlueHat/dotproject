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







?>