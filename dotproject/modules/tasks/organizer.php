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

	$show_no_end_date_warnings = true;
	$show_delayed_tasks_warnings = true;
		
 	$errors = false;
	$tasks = array();
	if(!$do) $do="ask";
	
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
		global $do;
		if($do == "ask") {
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
		
		$str_start_date = date("Y-m-d", strtotime(date("Y-m-d", $time) . " + 1 day"));
		$str_end_date = get_end_date($str_start_date, $tasks[$task_index]["task_duration"]);
		
		// check tasks overlapping
				
		$task = $tasks[$tasks_index];
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
				// log_info("$t1_start - $t1_end<br>$t2_start - $t2_end<br>");
				log_info("Task can't be set to [$str_start_date - $str_end_date] due to conflicts with task " . task_link($task2) . ".");
				fixate_task($task_index, $t2_end, $dep_on_task);
				return;
			}
		}
		
		$tasks[$task_index]["fixed"] = true;
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

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td>
		<?php
		
			/**** Add tasks to an array and check conflicts ****/
		
			// Select tasks without childrens (sub tasks)
			$sql = "select a.*,!a.task_dynamic as fixed from tasks as a left join tasks as b on a.task_id = b.task_parent and a.task_id != b.task_id where b.task_id IS NULL or b.task_id = b.task_parent order by a.task_priority desc";
			$dtrc = mysql_query( $sql );

			while ($row = mysql_fetch_array( $dtrc, MYSQL_ASSOC )) {
			
			        // calculate or set blank task_end_date if unset
			        
			        if(!$row["task_dynamic"] && $row["task_end_date"] == "0000-00-00 00:00:00") {
			        	if($row["task_duration"] != 0) {
				        	$row["task_end_date"] = get_end_date($row["task_start_date"], $row["task_duration"]);
				        	if($do=="ask" && $show_no_end_date_warnings) {
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
				        		if($show_delayed_tasks_warnings) {
					        		log_warning("Task " .task_link($row) . " started on " . formatTime(strtotime($row["task_start_date"])) . " and ended on " . formatTime($end_time) . "." ,
					        		"<input type=checkbox name=set_dynamic[" . $row["task_id"] . "] value=1 checked> Set as dynamic task<br>" .
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
				echo "<input type=hidden name=do value=" . ($do=="ask"?"fixate":"ask") . ">";
			}
		?>	

	</td>
</tr>
</table>
<br>
<?php
if(!$errors) {
	if($do=="ask") {
		echo "<font size=2><b>Do you want to accept this changes?</b></font><br>";
		echo "<input type=button value=accept class=button onClick='javascript:document.form.submit()'>";
	} else if ($do=="fixate") {
		echo "<font size=2><b>Tasks has been reorganized</b></font><br>";
	}
} else {
	echo "<font size=2><b>Please correct the above errors</b></font><br>";
	echo "<input type=button value=submit class=button onClick='javascript:document.form.submit()'>";
}
?>

</form>

</body>
</html>

