<?php

/*
 * Dynamic Tasks Organizer - by J. Christopher Pereira
 *
 * Consider:
 *	- order by priorities
 *	- other related persons time availability
 *
 * Constraints:
 *	- other tasks
 *	- task dependencies
 * 
 */
		
 	$errors = false;
	$tasks = array();
	if(!$do) $do="conf";
	
	function task_link($task) {
		return "<a href='index.php?m=tasks&a=view&task_id=" . $task["task_id"] . "'>" . $task["task_name"] . "</a>";
	}
	
	function search_task($task_id) {
		global $tasks;
		for($i = 0; $i < count($tasks) ; $i++) {
			if($tasks[$i]["task_id"] == $task_id) return $i;
		}
		return -1;
	}
		
	function log_info($msg) {
		global $option_debug;
		if($option_debug) {
			echo "$msg<br>";
		}
	}	
	
	function log_action($msg) {
		echo "&nbsp;&nbsp;<font color=red size=2>$msg</font><br>";
	}
	
	function log_error($msg, $fields = "") {
		echo "<font color=red size=1>ERROR: $msg</font><br>$fields<hr>";
	}
	
	function log_warning($msg, $fields = "") {
		global $show_warnings;
		echo "WARNING: $msg<br>$fields<hr>";
	}
	
	function fixate_task($task_index, $time, $dep_on_task) {
	
		// task_index != task_id !!!
				
		global $tasks, $do;
		
		// don't fixate tasks before now
		
		if($time < time()) $time = time();
		
		$str_start_date = date("Y-m-d", strtotime(date("Y-m-d", $time) . " + 1 day"));
		$str_end_date = get_end_date($str_start_date, $tasks[$task_index]["task_duration"]);
		
		// Complex SQL explanation:
		//
		// Objective: Check tasks overlapping only when
		// a user is vital for both tasks
		//
		// Definition of "vital for one task": when a task is assigned to user and total_users <= 2
		// (for example: if task is assigned to tree o more users, he is not vital).
		//  
		// Thus, a user is vital for both tasks <=>
		//	- total_users <= 2 for both tasks
		//	- and he apears in both tasks
		//
		// Thus, in both tasks (say 4 and 10), a there will be a vital user <=>
		//	- "number of tasks with total_users <= 2"
		//	  = rows("select count(*) as num_users from user_tasks
		//	  where task_id=4 or task_id=10
		//	  group by task_id having num_users <= 2") == 2;
		//
		//	- and "number of users which appears in both tasks"
		//	  = rows("select count(*) as frec 
		//	  from user_tasks where task_id=4 or task_id=10
		//	  group by user_id having frec = 2") > 0
				
		$t1_start = strtotime($str_start_date);	
		$t1_end = strtotime($str_end_date);
		
		foreach($tasks as $task2) {
			$t2_start = strtotime($task2["task_start_date"]);
			$t2_end = strtotime($task2["task_end_date"]);

			if($task2["fixed"] && (
				($t1_start >= $t2_start && $t1_start <= $t2_end)
				|| ($t1_end >= $t2_start && $t1_end <= $t2_end))
			) {
				// tasks are overlapping
				
				$t1 = $tasks[$task_index]["task_id"];
				$t2 = $task2["task_id"];
				
				$sql1 = "select count(*) as num_users from user_tasks where task_id=$t1 or task_id=$t2 group by task_id having num_users <= 2";
				$sql2 = "select count(*) as frec from user_tasks where task_id=$t1 or task_id=$t2 group by user_id having frec = 2";
		
				$vital = mysql_num_rows(mysql_query($sql1)) == 2 && mysql_num_rows(mysql_query($sql2)) > 0;
				if($vital) {
				
					log_info("Task can't be set to [$str_start_date - $str_end_date] due to conflicts with task " . task_link($task2) . ".");
					fixate_task($task_index, $t2_end, $dep_on_task);
					return;
				} else {
					log_info("Task conflicts with task " . task_link($task2) . " but there are no vital users.");
				}
			}
		}
		
		$tasks[$task_index]["fixed"] = true;
		
		// be quite if nothing will be changed
		
		if($tasks[$task_index]["task_start_date"] == $str_start_date && $tasks[$task_index]["task_end_date"] = $str_end_date) return;
		
		$tasks[$task_index]["task_start_date"] = $str_start_date;
		$tasks[$task_index]["task_end_date"] = $str_end_date;
			
		if($do == "ask") {
			if($dep_on_task) {
				log_action("I will fixate task " . task_link($tasks[$task_index]) . " to " . fromDate($str_start_date) . " (depends on " .  task_link($dep_on_task) . ")");
			} else {
				log_action("I will fixate task " . task_link($tasks[$task_index]) . " to " . fromDate($str_start_date) . " (no dependencies)");
			}
			
			// echo "<input type=hidden name=fixate_task[" . $tasks[$task_index]["task_id"] . "] value=y>";
		} else if($do == "fixate") {
			log_action("Task " . task_link($tasks[$task_index]) . " fixated to " . fromDate($str_start_date));
			$sql = "update tasks set task_start_date = '" . $str_start_date . "', task_end_date = '" . $str_end_date . "' where task_id = " . $tasks[$task_index]["task_id"];
			mysql_query($sql);
		}
	}
	
	function get_last_childrens($task) {
		// returns the last childrens (leafs) from $task
		$arr = array();
		
		// query childrens from task
		$sql = "select * from tasks where task_parent=" . $task["task_id"];
		$query = mysql_query($sql);		
		if(mysql_num_rows($query)) {
			// has childrens
			while($row = mysql_fetch_array($query)) {
				if($row["task_id"] != $task["task_id"]) {
					// add recursively childrens of childrens to $arr
					$sub = get_last_childrens($row);
					array_splice($arr, count($arr), 0, $sub);
				}
			}
		} else {
			// it's a leaf
			array_push($arr, $task);						
		}
		return $arr;		
	}
	
	function process_dependencies($i) {
		global $tasks;
		
		if($tasks[$i]["fixed"]) return;
		
		log_info("<div style='padding-left: 1em'>Dependecies for '" . $tasks[$i]["task_name"] . "':<br>");
		
		// query dependencies for this task
		
		$query = mysql_query("select tasks.* from tasks,task_dependencies where task_id=dependencies_req_task_id and dependencies_task_id=" . $tasks[$i]["task_id"]);
		
		if(mysql_num_rows($query) != 0) {
		
			$all_fixed = true;
			$latest_end_date = "";
			
			// store dependencies in an array (for adding more entries on the fly)
			$dependencies = array();			
			while($row = mysql_fetch_array($query)) {
				array_push($dependencies, $row);
			}
			
			$d = 0;
			
			while($d < count($dependencies)) {
			
				$row = $dependencies[$d];				
				$index = search_task($row["task_id"]);
								
				if($index == -1) {
					// task is not listed => it's a task group
					// => $i depends on all its subtasks
					// => add all subtasks to the dependencies array
					
					log_info("- task '" . $row["task_name"] . "' is a task group (processing subtask's dependencies)");
					
					$childrens = get_last_childrens($row);															
					// replace this taskgroup with all its subtasks
					array_splice($dependencies, $d, 1, $childrens);
													
					continue;
				}
				
				log_info(" - '" . $tasks[$index]["task_name"] . ($tasks[$index]["fixed"]?" (FIXED)":"") . "'");
				
				// TODO: Detect dependencies loops (A->B, B->C, C->A)
				
				process_dependencies($index);
				
				if(!$tasks[$index]["fixed"]) {
					$all_fixed = false;
				} else {
					// store latest end_date
					$str_end_date = substr($tasks[$index]["task_end_date"],0, 10);
					$end_date = strtotime($str_end_date);
					
					if(!$latest_end_date || $end_date > $latest_end_date) {
						$latest_end_date = $end_date;
						$dep_on_task = $row;
					}
					$d++;
				}
			}
			
			if($all_fixed) {
				// this task depends only on fixated tasks
				log_info("all dependencies are fixed");
				fixate_task($i, $latest_end_date, $dep_on_task);
			} else {
				log_error("task has not fixed dependencies");
			}
			
		} else {
			// task has no dependencies
			log_info("no dependencies => ");
			fixate_task($i, time(), "");
		}
		echo "</div><br>\n";
	}
?>

<table name="table" cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Taks Organizer</span></td>
	<td nowrap><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
</tr>
</table>

<?php

/*** Process updates ***/

// update tasks duration
if($set_duration) {
	foreach($set_duration as $key=>$val) {
		if($val) {
			$sql = "update tasks set task_duration=" . ($val * $dayhour[$key]) . " where task_id=" . $key;
			mysql_query($sql);
		}
	}
	$do = "ask"; // ask again
}

if($set_dynamic) {
	foreach($set_dynamic as $key=>$val) {
		if($val) {
			$sql = "update tasks set task_dynamic=1 where task_id=$key";
			mysql_query($sql);
		}
	}
	$do = "ask";
}

?>

<form name="form" method="post">

<?php
	// default options
	if(!isset($option_check_delayed_tasks)) $option_check_delayed_tasks=1;
	if(!isset($option_no_end_date_warning)) $option_no_end_date_warning=0;
	if(!isset($option_debug)) $option_debug=0;
?>
<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td>
		<input type=checkbox name=option_check_delayed_tasks value=1 <?php echo $option_check_delayed_tasks?"checked":"" ?>>Check delayed fixed tasks<br>
		<input type=checkbox name=option_no_end_date_warning value=1 <?php echo $option_no_end_date_warning?"checked":"" ?>>Warn of fixed tasks withoud end dates<br>
		<?php /*
		<input type=checkbox name=option_project value=1 <?php echo $option_project?"checked":"" ?>>Organize tasks belonging only to <select name=option_project_id>
			<?php
				$sql = "select project_id, project_name from projects";
				$query = mysql_query($sql);
				while($project = mysql_fetch_array($query)) {
					echo "<option value=" . $project["project_id"] . ">" . $project["project_name"] . "</option>";
				}
			?>
		</select><br>
		*/ ?>
		<input type=checkbox name=option_debug value=1 <?php echo $option_debug?"checked":"" ?>>Show debug info<br>
	</td>
</tr>
</table>
<br>

<?php if($do != "conf") { ?>
<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td>
		<?php
		
			/**** Add tasks to an array and check conflicts ****/
		
			// Select tasks without childrens (sub tasks)
			$sql = "select a.*,!a.task_dynamic as fixed from tasks as a left join tasks as b on a.task_id = b.task_parent and a.task_id != b.task_id where b.task_id IS NULL or b.task_id = b.task_parent order by a.task_priority desc, a.task_order desc";
			$dtrc = mysql_query( $sql );

			while ($row = mysql_fetch_array( $dtrc, MYSQL_ASSOC )) {
			
			        // calculate or set blank task_end_date if unset
			        
			        if(!$row["task_dynamic"] && $row["task_end_date"] == "0000-00-00 00:00:00") {
			        	if($row["task_duration"] != 0) {
				        	$row["task_end_date"] = get_end_date($row["task_start_date"], $row["task_duration"]);
				        	if($do=="ask" && $option_no_end_date_warning) {
					        	log_warning("Task " . task_link($row) . " has no end date. Using tasks duration instead.",
					        	"<input type=checkbox name='set_end_date[" . $row["task_id"] . "]' value=1> Set end date to " . formatTime(strtotime($row["task_end_date"])));
					        }
			        	} else {
				        	$row["task_end_date"] = "";
				        	log_error("Task " .task_link($row) . " has no duration.",
				        		"Please enter the expected duration: <input class=input type=text name='dur[" . $row["task_id"] . "]' size=3>"
				        	. "<select name='dayhour[" . $row["task_id"] . "]'>
				        		<option value='1'>hour(s)
                                			<option value='24'>day(s)
			                        </select>"
			                        );
				        	$errors = true;
				        }
			        }
			        
			        // check delayed tasks
			        if($do == "ask") {
				        if(!$row["task_dynamic"] && $row["task_precent_complete"] == 0) {
				        	// nothing has be done yet
				        	$end_time = strtotime($row["task_end_date"]);
				        	if($end_time < time()) {
				        		if($option_check_delayed_tasks) {
					        		log_warning("Task " .task_link($row) . " started on " . formatTime(strtotime($row["task_start_date"])) . " and ended on " . formatTime($end_time) . "." ,
					        		"<input type=checkbox name=set_dynamic[" . $row["task_id"] . "] value=1 checked> Set as dynamic task and reorganize<br>" .
					        		"<input type=checkbox name=set_priority[" . $row["task_id"] . "] value=1 checked> Set priority to high<br>"				        		
								);
							}
						}
					}
				}

				array_push($tasks, $row);
			}
			
			if(!$errors) {
				for($i = 0; $i < count($tasks) ; $i++) {
					process_dependencies($i);
				}
			}
		?>	

	</td>
</tr>
</table>
<br>
<?php } ?>

<?php
if(!$errors) {
	echo "<input type=hidden name=do value=" . ($do=="ask"?"fixate":"ask") . ">";
	if($do == "ask") {
		echo "<font size=2><b>Do you want to accept this changes?</b></font><br>";
		echo "<input type=button value=accept class=button onClick='javascript:document.form.submit()'>";
	} else if ($do == "fixate") {
		echo "<font size=2><b>Tasks has been reorganized</b></font><br>";
	} else if ($do == "conf") {
		echo "<input type=button value=start class=button onClick='javascript:document.form.submit()'>";
	}
} else {
	echo "<font size=2><b>Please correct the above errors</b></font><br>";
	echo "<input type=button value=submit class=button onClick='javascript:document.form.submit()'>";
}
?>

</form>

</body>
</html>

