<?php

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
        echo '<script language="javascript">
        window.location="./index.php?m=help&a=access_denied";
        </script>
';
}

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

?>
<form name=form method=post>
<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
	<td nowrap width="100%">
		<span class="title">My ToDo List</span>
	</td>
</tr>
</table>

<?php

if($task_priority && $selected) {
	if($task_priority < -1 || $task_priority > 1) {
		exit();
	}
	foreach($selected as $key => $val) {
		mysql_query("update tasks set task_priority=$task_priority where task_id=$key");
	}
}

// query my sub-tasks

$sql = "select project_name, project_id, a.* from projects,tasks as a, user_tasks left join tasks as b on a.task_id=b.task_parent where user_tasks.task_id=a.task_id and b.task_id is null and user_tasks.user_id=$thisuser_id and a.task_precent_complete != 100 and project_id = a.task_project order by a.task_start_date, task_priority desc";
$query = mysql_query($sql);

?>

<table width="98%" border="0" cellpadding="2" cellspacing="1" class="tbl">
	<tr>
		<th width="0">&nbsp;</th>
		<th width="10">id</th>
		<th width="20">work</th>
		<th width="15" align="center">p</th>
		<th width="190">task name</th>
		<th nowrap>start date</th>
		<th nowrap>duration&nbsp;&nbsp;</th>
		<th nowrap>finish date</th>
		<th nowrap>due in</th>
	</tr>

<?php

/*** Tasks listing ***/

while($a = mysql_fetch_array($query)) {

        if($a["task_end_date"] == "0000-00-00 00:00:00") {
	        $a["task_end_date"] = "";
        }
	?>        
	
	<tr>
		<td>
			<input type=checkbox name="selected[<?php echo $a["task_id"] ?>]">
		</td>
		<td><A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
		<td align="right"><?php echo intval($a["task_precent_complete"]);?>%</td>
		<td>
	<?php if ($a["task_priority"] < 0 ) {
		echo "<img src='./images/icons/low.gif' width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
	}?>
		</td>
			
	<td width="90%">
	<img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?php echo $a["task_id"];?>">
	<map name="arrow<?php echo $a["task_id"];?>"><area coords="0,0,10,7" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=u";?>>
	<area coords="0,8,10,14" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=d";?>></map>


	<a href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>"><?php echo $a["task_name"];?></a>
	 - <a href="./index.php?m=projects&a=view&project_id=<?php echo $a["project_id"];?>"><?php echo $a["project_name"];?></a>
	</td>
	<td nowrap><?php echo fromDate(substr($a["task_start_date"], 0, 10));?></td>
	<td>
	<?php if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
	       	// FIXME: this won't work for every language!		
		$dt.="s";
	}
        echo ($dur!=0)?$dur . " " . $dt:"n/a";
	?>
	</td>
	
	<td nowrap>
        <?php 
        	if($a["task_end_date"]) {
        		echo fromDate(substr($a["task_end_date"], 0, 10));
        	} else {
        		echo "n/a";
        	}
        ?>
	</td>
	
	<td nowrap>
	<?php
		$start_date = time2YMD(dbDate2time($a["task_start_date"]));
		$end_date = strtotime(get_end_date($start_date, $a["task_duration"]));
		
		$days = floor(($end_date - time())/ 86400);
		if($days == 0) {
			echo "<font color=brown>today</font>";
		} else {
			if($days<0) echo "<font color=red>";
			echo "$days days";
			if($days<0) echo "</font>";
		}
	?>
	</td>
	
	</tr>
<?php } ?>

</table>
<br>
Set selected tasks priority to
<select name=task_priority>
	<option value=1>high
	<option value=0>normal
	<option value=-1 selected>low
</select><br><br>
<input type=submit class=button value=update>
</form>
<table height="100%">
<tr><td>&nbsp;</td></TR>
</table>
