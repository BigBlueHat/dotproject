<?php
srand((double)microtime()*1000000); 
if($w == "u"){
	$sql = "select task_id, task_order from tasks where task_project = $task_project and task_parent = task_id and task_order <= $order and task_id != $task_id order by task_order desc";
	$dsql = mysql_query($sql);
	if($darr = mysql_fetch_array($dsql)){
		$neworder = $darr["task_order"] - 1;
		
		$sql = "update tasks set task_order = task_order -1 where task_order <= $neworder";
		//echo $sql;
		mysql_query($sql);
		echo mysql_error();
		
		
		$sql = "update tasks set task_order = $neworder where task_id = $task_id";
		//echo $sql;
		mysql_query($sql);
		echo mysql_error();
	}
}
else
{
	$sql = "select task_id, task_order from tasks where task_project = $task_project  and task_parent = task_id and task_order >= $order and task_id != $task_id  order by task_order";
	$dsql = mysql_query($sql);
	if($darr = mysql_fetch_array($dsql)){
		$neworder = $darr["task_order"] + 1;
		
		$sql = "update tasks set task_order = task_order +1 where task_order >= $neworder";
		//echo $sql;
		mysql_query($sql);
		
		
		$sql = "update tasks set task_order = $neworder where task_id = $task_id";
		//echo $sql;
		mysql_query($sql);
	}
}
	$message.= mysql_error();
?>
<script>
window.location="./index.php?m=tasks&message=<?php echo $message;?>";
</script>
