<b>Tasks:</b>
<br>
<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
<TR style="border: outset #eeeeee 2px;">
	<TD class="mboxhdr" width="10">&nbsp;</td>
	<TD class="mboxhdr" width="20">work</td>
	<TD class="mboxhdr" width="20">p</td>
	<TD class="mboxhdr" width=200>task</td>
	<TD class="mboxhdr">duration&nbsp;&nbsp;</td>
</tr>
<?php
// Tasks mini-table in project view action

$tarr = array();
//task index
$tsql = "
SELECT
	tasks.task_id, task_parent, task_name,	task_start_date, task_end_date,
	task_priority,
	task_precent_complete,
	task_duration,
	task_order
FROM tasks, user_tasks
WHERE task_project = $project_id
	AND user_tasks.user_id = $user_cookie
	AND user_tasks.task_id = tasks.task_id
ORDER BY task_order
";
//echo "<pre>$tsql</pre>";
$trc = mysql_query($tsql);
$nums = mysql_num_rows($trc);
echo mysql_error();

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$tarr[$x] = mysql_fetch_array( $trc, MYSQL_ASSOC );
}

function showtask( &$a, $level=0 ) { 
	global $done;
	$done[] = $a['task_id']; ?>
<TR bgcolor="#f4efe3">
	<TD><A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
	<TD align="right"><?php echo intval($a["task_precent_complete"]);?>%</td>
	<TD>
	<?php if ($a["task_priority"] < 0 ) {
		echo "<img src='./images/icons/low.gif' width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
	}?>
	</td>
	<TD width=90%>

	<?php if ($level == 0) { ?>
	<img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?php echo $a["task_id"];?>">
	<map name="arrow<?php echo $a["task_id"];?>"><area coords="0,0,10,7" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=u";?>>
	<area coords="0,8,10,14" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=d";?>></map>

	<?php } else {
		for ($y=0; $y < $level; $y++) {
			if ($y+1 == $level) {
				echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
			} else {
				echo "<img src=./images/shim.gif width=16 height=12  border=0>";
			}
		}
	}?>

	<A href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>"><?php echo $a["task_name"];?></a></td>
	<TD>
	<?php if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
		$dt.="s";
	}
	echo $dur . " " . $dt ;
	?>
	</td>
</tr>
<?php }

function findchild( &$a, $parent, $level=0 ){
	$level = $level+1;
	$n = count( $a );
	for ($x=0; $x < $n; $x++) {
		if($a[$x]["task_parent"] == $parent && $a[$x]["task_parent"] != $a[$x]["task_id"]){
			showtask( $a[$x], $level );
			findchild( $a, $a[$x]["task_id"], $level);
		}
	}
}

$done = array();
while (list( , $t ) = each( $tarr )) {
	if ($t["task_parent"] == $t["task_id"]) {
		showtask( $t );
		findchild( $tarr, $t["task_id"] );
	}
}

// catch any 'orphaned' tasks assigned to the user
reset( $tarr );
while (list( , $t ) = each( $tarr )) {
	if ( !in_array( $t["task_id"], $done )) {
		showtask( $t, 1 );
	}
}
?>
</TABLE>
