<?
//dosql.sql

//defaults
if(empty($task_id))$task_id=0;
if(empty($task_status ))$task_status =0;
if(empty($task_order ))$task_order =0;
if(empty($task_client_publish))$task_client_publish =0;
$doassingsql  = 0;
$message = "";


if(empty($task_milestone))$task_milestone = 0;
if(empty($hassign))$hassign = "";


//Delete if $del set
if($del){
	$delsql = "delete from tasks where task_id = $task_id";
	mysql_query($delsql);
	$delsql2 = "delete from user_tasks where task_id = $task_id";
	mysql_query($delsql);
	$message = "Task Deleted";
}
else if($task_id ==0 && isset($task_name)){
//insert a new task
	$task_duration = ($duration * $dayhour);
	
	
	
	$tsql = "
	insert into tasks ( task_name , task_parent , task_milestone , task_project ,  task_start_date , task_end_date , task_duration , task_status  , task_priority  , task_precent_complete , task_description , task_target_budget  , task_related_url  ,  task_order  , task_client_publish, task_owner)
	values
	('$task_name','$task_parent','$task_milestone','$task_project','$task_start_date','$task_end_date','$task_duration','$task_status ','$task_priority ','$task_precent_complete','$task_description','$task_target_budget ','$task_related_url','$task_order ','$task_client_publish', '$task_owner')";
	//echo $tsql;
	mysql_query($tsql);
	//$message.= mysql_error() ."<BR>";
	$id = mysql_insert_id();
	if($task_parent == 0){
		$tpsql = "update tasks set task_parent = " . $id . " where task_id = " . $id;
		mysql_query($tpsql);
		//$message.= mysql_error() ."<BR>";
	}
	$tosql  ="insert into user_tasks (user_id, task_id, user_type) values ($user_cookie, $id, -1)";
	mysql_query($tosql);
	//$message.= mysql_error() ."<BR>";

	$doassingsql = 1;	
	$doassignemail = 1;

} // Update existing task
else if($task_id > 0){
 
	//Check if there is at least one top level parent
	$cpsql = "
	select task_id, task_parent
	from tasks 
	where 
	task_project = $task_project";

	$cprc = mysql_query($cpsql);
	$cprow = mysql_num_rows($cprc);
	
	for($x=0;$x<$cprow;$x++){
		$checkarr[$x]=mysql_fetch_array($cprc);
		
		if($checkarr[$x]["task_id"] == $task_id) {
		$old_parent = $checkarr[$x]["task_parent"];
		$checkarr[$x]["task_parent"] = $task_parent;
		}
	}

	function goodParent($id, $parent, $already_checked=""){
		global $checkarr, $cprow;
		$s = 0;
		reset($checkarr);
		for($x = 0;$x< $cprow;$x++){
			if($checkarr[$x]["task_id"] == $parent){
				if($checkarr[$x]["task_parent"] == $checkarr[$x]["task_id"]){
					$s=1;
				}
				else{
					$fstr = "-" . $checkarr[$x]["task_id"] . "-";
					if(strpos($already_checked, $fstr) >0){
						$s=0;
					}
					else{
						$already_checked = $already_checked . "-" . $checkarr[$x]["task_id"];
						$s = $s + goodParent($checkarr[$x]["task_id"], $checkarr[$x]["task_parent"], $already_checked);
					}
				}
			}
		}
		return $s;
	}
	

	if(!goodParent($task_id, $task_parent)){
		$message.= "Changing the task parent would orphan the task, falling back to the old parent";
		$task_parent = $old_parent;
	}
	
	$task_duration = ($duration * $dayhour);
	$tsql = "
	update tasks set
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
	where task_id = $task_id";
	mysql_query($tsql);
	//$message.= mysql_error() ."<BR>";

	$doassingsql = 1;	
	$doassignemail = 1;


}

if($doassingsql){
	$cleansql = "delete from user_tasks where task_id = " .$task_id . " and user_type = 0";
	mysql_query($cleansql);
	//$message.= mysql_error() ."<BR>";
	$assigees = explode(",", $hassign);
	for($x = 0; $x < count($assigees); $x++){
		if(intval($assigees[$x]) > 0){
			$asql = "insert into user_tasks (user_id, task_id) values ($assigees[$x], $task_id)";
			mysql_query($asql);
				//$message.= mysql_error() ."<BR>";
		}
	}
}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	header("Location: ./index.php?m=tasks&message=" . $message);
}

?>
