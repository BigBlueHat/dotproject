<?
// JBF phpinfo(); exit();
if(empty($event_id))$event_id=0;
if(empty($event_project)) $event_project=0;

// JBF strftime("%Y-%m-%d %H:%M:%S", time()) used for new files
$start_date = strtotime($sdate . " " . $stime);
$end_date = strtotime($edate . " " . $etime);
// JBF echo strftime("%Y-%m-%d %H:%M:%S <br />\n", $start_date); exit();

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
	event_remind= '$event_remind'
	where
	event_id = $event_id
	";
	// mysql_query($sql);
	echo "<br>" . $sql;
	$message  ="Forum Updated";
}
//If Insert
else{

$sql = "insert into events
(event_title, event_start_date, event_end_date, event_parent, event_description, event_times_recuring, event_recurs, event_remind)
values

('$event_title', '$start_date', '$end_date', '$event_project', '$event_notes', '$event_times_recuring', '$event_recurs', '$event_remind')";

	 mysql_query($sql);
	// JBF echo "<br>" . $sql;
	$message  ="Event Inserted";
}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
// JBF exit(); // JBF
	header("Location: ./index.php?m=calendar&message=" . $message);
}
?>

