<?php
//dosql.sql

//defaults
if(empty($task_id))$task_id=0;
if(empty($task_status ))$task_status =0;
if(empty($task_order ))$task_order =0;
if(empty($task_client_publish))$task_client_publish =0;
if(empty($notify))$notify =0;
$doassingsql  = 0;
$doassignemail = 0;
$message = "";
$mail_type = "";

if(empty($task_milestone))$task_milestone = 0;
if(empty($task_dynamic))$task_dynamic = 0;
if(empty($hassign))$hassign = "";
if(empty($hdependencies))$dependencies = "";


//Delete if $del set
if ($del) {
	$delsql = "delete from tasks where task_id = $task_id";
	mysql_query($delsql);
	$delsql2 = "delete from user_tasks where task_id = $task_id";
	mysql_query($delsql);
	$message = "Task Deleted";
	$mail_type = "Deleted";
} else if ($task_id ==0 && isset( $task_name )) {
//insert a new task
	$task_duration = ($duration * $dayhour);
	$task_start_date = toDate($task_start_date);
	$task_end_date = toDate($task_end_date);
	
	$tsql = "
	INSERT INTO tasks ( 
		task_name, task_parent, task_milestone, task_project,  task_start_date,
		task_end_date, task_duration, task_status, task_priority, 
		task_precent_complete, task_description, task_target_budget, task_related_url,
		task_order, task_client_publish, task_owner, task_dynamic
	) VALUES (
		'$task_name', '$task_parent', '$task_milestone', '$task_project', '$task_start_date',
		'$task_end_date', '$task_duration', '$task_status ', '$task_priority ',
		'$task_precent_complete', '$task_description', '$task_target_budget ', '$task_related_url',
		'$task_order ', '$task_client_publish', '$task_owner', '$task_dynamic'
	)";
##echo "<pre>$tsql</pre>";
	mysql_query($tsql);
	if (mysql_error()) {
		$sql = $tsql;
	}
##$message.= mysql_error() ."<BR>";
	$id = mysql_insert_id();
	if ($task_parent == 0) {
		$tpsql = "update tasks set task_parent = " . $id . " where task_id = " . $id;
		mysql_query( $tpsql );
		if (mysql_error()) {
			$sql = $tpsql;
		}
		//$message.= mysql_error() ."<BR>";
	}
	$tosql = "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($user_cookie, $id, -1)";
	mysql_query( $tosql );
	if (mysql_error()) {
		$sql = $tosql;
	}
	//$message.= mysql_error() ."<BR>";

	$task_id = $id;
	$doassingsql = 1;	
	$doassignemail = 1;
	$mail_type = "Added";

} else if ($task_id > 0) {
// Update existing task
	//Check if there is at least one top level parent
	$cpsql = "
	SELECT task_id, task_parent
	FROM tasks 
	WHERE 
	task_project = $task_project
	";

	$cprc = mysql_query($cpsql);
	$cprow = mysql_num_rows($cprc);
	
	for ($x=0; $x < $cprow; $x++) {
		$checkarr[$x] = mysql_fetch_array( $cprc );
		
		if ($checkarr[$x]["task_id"] == $task_id) {
			$old_parent = $checkarr[$x]["task_parent"];
			$checkarr[$x]["task_parent"] = $task_parent;
		}
	}

	function goodParent($id, $parent, $already_checked = "") {
		global $checkarr, $cprow;
		$s = 0;
		reset( $checkarr );
		for ($x=0; $x < $cprow; $x++) {
			if ($checkarr[$x]["task_id"] == $parent) {
				if ($checkarr[$x]["task_parent"] == $checkarr[$x]["task_id"]) {
					$s=1;
				} else {
					$fstr = "-" . $checkarr[$x]["task_id"] . "-";
					if (strpos( $already_checked, $fstr ) > 0) {
						$s=0;
					} else {
						$already_checked = $already_checked . "-" . $checkarr[$x]["task_id"];
						$s = $s + goodParent($checkarr[$x]["task_id"], $checkarr[$x]["task_parent"], $already_checked);
					}
				}
			}
		}
		return $s;
	}
	

	if (!goodParent( $task_id, $task_parent )) {
		$message .= "Changing the task parent would orphan the task, falling back to the old parent";
		$task_parent = $old_parent;
	}
	
	$task_duration = ($duration * $dayhour);
	$task_start_date = toDate( $task_start_date );
	$task_end_date = toDate( $task_end_date );
	$tsql = "
	UPDATE tasks SET
	task_name='$task_name',
	task_parent='$task_parent',
	task_milestone='$task_milestone',
	task_dynamic='$task_dynamic',
	task_start_date='$task_start_date',
	task_end_date='$task_end_date',
	task_duration='$task_duration',
	task_status='$task_status',
	task_priority='$task_priority',
	task_duration='$task_duration',
	task_precent_complete='$task_precent_complete',
	task_description='$task_description',
	task_target_budget='$task_target_budget',
	task_related_url='$task_related_url',
	task_creator='$task_creator',
	task_order='$task_order',
	task_client_publish='$task_client_publish', 
	task_owner = '$task_owner'
	WHERE task_id = $task_id
	";
	mysql_query($tsql);
	if (mysql_error()) {
		$sql = $tsql;
	}
	//$message.= mysql_error() ."<BR>";

	$doassingsql = 1;	
	$doassignemail = 1;
	$mail_type = "Edited";
}

if ($doassingsql) {
	$cleansql = "DELETE FROM user_tasks WHERE task_id = " .$task_id . " AND user_type = 0";
	mysql_query( $cleansql );
		
	if (mysql_error())
		$sql = $cleansql;
		
	$cleansql = "DELETE FROM task_dependencies WHERE dependencies_task_id = " .$task_id;
	mysql_query( $cleansql );
		
	if (mysql_error())
		$sql = $cleansql;
	
	$assigees = explode( ",", $hassign );
	for ($x=0; $x < count( $assigees ); $x++) {
		if (intval($assigees[$x]) > 0) {
			$asql = "REPLACE user_tasks (user_id, task_id) VALUES ($assigees[$x], $task_id)";
			mysql_query($asql);
			if (mysql_error()) {
				$sql = $asql;
			}
			//$message.= mysql_error() ."<BR>";
		}
	}
	
	$dependencies = explode( ",", $hdependencies );
	for ($x=0; $x < count( $dependencies ); $x++) {
		if (intval($dependencies[$x]) > 0) {
			$sql = "REPLACE task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($task_id, $dependencies[$x])";
			mysql_query($sql);
		}
	}	
}

if ($doassignemail && $notify) {
	$csql = "
	SELECT user_email, user_first_name, user_last_name
	FROM users
	WHERE users.user_id = $user_cookie
	";
	$query = mysql_query( $csql );
	if (mysql_error()) {
		$sql = $csql;
	}
	$editor = mysql_fetch_array( $query );

	$usql = "
	SELECT
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
	FROM tasks
	LEFT JOIN user_tasks ON user_tasks.task_id = tasks.task_id
	LEFT JOIN users creator ON creator.user_id = tasks.task_owner
	LEFT JOIN users owner ON owner.user_id = tasks.task_creator
	LEFT JOIN users assignee ON assignee.user_id = user_tasks.user_id
	WHERE tasks.task_id = $task_id
	";

	$query = mysql_query( $usql );
	if (mysql_error()) {
		$sql = $usql;
	}
	// For each user, email them an update, similar to the update that
	// ticketsmith uses.
	$row_count = mysql_num_rows( $query );
	$mail_header = "Content-Type: text/html\r\n"
	. "Content-Transfer-Encoding: 8bit\r\n"
	. "Mime-Version: 1.0\r\n"
	. "X-Mailer: Dotproject"
	;
	$subject = "Task $task_id $mail_type";
	$mail_body = "<head><title>$subject</title>\n"
	."<style type=text/css>\n"
	."body,td,th { font-family: verdana,helvetica,arial,sans-serif; font-size:12px; }\n"
	."</style>\n"
	."</head>\n"
	. "<body>\n"
	. "<table bgcolor='#ffffff' cellpadding=4 cellspacing=1>\n"
	. "<tr bgcolor='#eeeeee'><th colspan=2>$subject</th></tr>\n"
	. "<tr><td>Task ID</td><td><a href='"
	. $base_url
	. "/index.php?m=tasks&a=view&task_id=$task_id'>$task_id</a></td></tr>\n";
	for ($i=0; $i < $row_count; $i++) {
		$row = mysql_fetch_array( $query );
		if ($row['assignee_id'] != $user_cookie) {
			$mail_text = $mail_body
			. "<tr><td>Title</td><td>"
			. $row['task_name']
			. "&nbsp;</tr>\n<tr><td valign=top>Description</td><td>"
			. str_replace(chr(10), "<BR>", $row['task_description'])
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
			. "</a></tr>\n<tr><td>$mail_type by</td><td><a href='mailto:"
			. $editor['user_email']
			. "'>"
			. $editor['user_first_name']
			. "&nbsp;"
			. $editor['user_last_name']
			. "</a></tr>\n</table></body>\n";

			$from = $row['creator_first_name'] . ' '. $row['creator_last_name'] . ' <' . $row['creator_email'] . '>';
			mail( $row['assignee_email'], $subject, $mail_text, "From: $from\r\n".$mail_header );
		}
	}
}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
} else {
	$AppUI->redirect();
	//header( "Location: ./index.php?m=tasks&project_id=" . $project_id . "&message=" . $message );	
}

?>
