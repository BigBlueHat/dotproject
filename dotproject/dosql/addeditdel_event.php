<?php
if(empty($event_id))$event_id=0;
if(empty($event_project)) $event_project=0;
$event_private = empty($HTTP_POST_VARS['event_private']) ? 0 : 1;

$start_date = strtotime(toDate($sdate) . " " . $stime);
$end_date = strtotime(toDate($edate) . " " . $etime);

//IF delete
if($HTTP_POST_VARS["del"]){
	$sql = "delete from events where event_id = $event_id";
	mysql_query($sql);
	$message  ="Event Deleted";
	$message.="<BR>Messages Deleted";

}
//If update
elseif($HTTP_POST_VARS["event_id"] > 0){

	$sql = "update events set
	event_title = '$event_title',
	event_start_date = '$start_date',
	event_end_date = '$end_date',
	event_parent= '$event_project',
	event_description= '$event_notes',
	event_times_recuring= '$event_times_recuring',
	event_recurs= '$event_recurs',
	event_remind= '$event_remind',
	event_project='$event_project',
	event_private='$event_private'
	where
	event_id = $event_id
	";
	mysql_query($sql);
	$message  ="Event Updated";
}
//If Insert
else{

$sql = "insert into events
(event_title, event_start_date, event_end_date, event_parent, event_description, event_times_recuring, event_recurs, event_remind, event_project, event_owner, event_private)
values

('$event_title', '$start_date', '$end_date', '$event_project', '$event_notes', '$event_times_recuring', '$event_recurs', '$event_remind', 'event_project', $thisuser_id, '$event_private')";

	 mysql_query($sql);
	$message  ="Event Inserted";
}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	header("Location: ./index.php?m=calendar&message=" . $message);
}
?>

