<?php
// tasks - dosql.php

//defaults
if (empty( $task_id )) {
	$task_id=0;
}
$message = "";

if (empty( $task_milestone )) {
	$task_milestone = 0;
}
if (empty( $hassign )) {
	$hassign = "";
}

//Delete if $del set
if (isset( $del )) {
	$delsql = "DELETE FROM tasks WHERE task_id = $task_id";
	mysql_query( $delsql );
	$delsql2 = "DELETE FROM user_tasks WHERE task_id = $task_id";
	mysql_query( $delsql );
}

//insert a new task
if ($task_id ==0 && isset( $task_name )) {
	$task_duration = ($duration * $dayhour);
	
	$tsql = "
	INSERT INTO tasks (task_name, task_parent, task_milestone, task_project   task_start_date  task_end_date, task_duration, task_status, task_priority, task_precent_complete, task_description, task_target_budget, task_related_url, task_order, task_client_publish, task_owner
	) VALUES (
	'$task_name','$task_parent','$task_milestone','$task_project','$task_start_date','$task_end_date','$task_duration','$task_status','$task_priority ','$task_precent_complete','$task_description','$task_target_budget ','$task_related_url','$task_order ','$task_client_publish', '$task_owner'
	)";
##echo $tsql;##
	mysql_query( $tsql );
	$message .= mysql_error() ."<BR>";
	$id = mysql_insert_id();
	if ($task_parent == 0) {
		$tpsql = "UPDATE tasks SET task_parent = " . $id . " WHERE task_id = " . $id;
		mysql_query( $tpsql );
		$message .= mysql_error() ."<BR>";
	}
	$tosql  ="INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($user_cookie, $id, -1)";
	mysql_query( $tosql );
	$message .= mysql_error() ."<BR>";

	$doassingsql = 1;	
	$doassignemail = 1;

} else if ($task_id > 0) {
// Update existing task
//Check if there is at least one top level parent
	$cpsql = "
	SELECT task_id, task_parent
	FROM tasks 
	WHERE task_project = $task_project
	";

	$cprc = mysql_query( $cpsql );
	$cprow = mysql_num_rows( $cprc );
	
	for ($x=0; $x<$cprow; $x++) {
		$checkarr[$x] = mysql_fetch_array( $cprc );
		if ($checkarr[$x]["task_id"] == $task_id) {
			$old_parent = $checkarr[$x]["task_parent"];
			$checkarr[$x]["task_parent"] = $task_parent;
		}
	}

	function goodParent( $id, $parent, $already_checked="" ) {
		global $checkarr, $cprow;
		$s = 0;
		reset( $checkarr );
		for( $x=0; $x < $cprow; $x++) {
			if ($checkarr[$x]["task_id"] == $parent) {
				if ($checkarr[$x]["task_parent"] == $checkarr[$x]["task_id"]) {
					$s=1;
				} else {
					$fstr = "-" . $checkarr[$x]["task_id"] . "-";
					if (strpos($already_checked, $fstr) > 0) {
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

	if (!goodParent($task_id, $task_parent)) {
		$message.= "Changing the task parent would orphan the task, falling back to the old parent";
		$task_parent = $old_parent;
	}
	
	$task_duration = ($duration * $dayhour);
	$tsql = "
	UPDATE tasks SET
	task_name='$task_name',
	task_parent='$task_parent',
	task_milestone='$task_milestone',
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
	mysql_query( $tsql );
	$message .= mysql_error() ."<BR>";

	$doassingsql = 1;	
	$doassignemail = 1;
}

if ($doassingsql) {
	$cleansql = "DELETE FROM user_tasks WHERE task_id = " .$task_id . " AND user_type = 0";
	mysql_query( $cleansql );
	$message .= mysql_error() ."<BR>";
	$assigees = explode( ",", $hassign );
	for ($x = 0; $x < count( $assigees ); $x++) {
		if (intval( $assigees[$x] ) > 0) {
			$asql = "INSERT INTO user_tasks (user_id, task_id) VALUES ($assigees[$x], $task_id)";
			mysql_query( $asql );
				$message .= mysql_error() ."<BR>";
		}
	}
}
?>
<script>
window.location="./index.php?m=tasks&message=<?php echo $message;?>";
</script>
