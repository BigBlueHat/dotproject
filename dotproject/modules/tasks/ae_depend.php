<?php
global $AppUI, $dPconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $obj;
global $durnTypes, $task_project, $task_id, $tab;

//Time arrays for selects
$start = dPgetConfig('cal_day_start');
$end   = dPgetConfig('cal_day_end');
$inc   = dPgetConfig('cal_day_increment');
if ($start === null ) $start = 8;
if ($end   === null ) $end = 17;
if ($inc   === null)  $inc = 15;
$hours = array();
for ( $current = $start; $current < $end + 1; $current++ ) {
	if ( $current < 10 ) { 
		$current_key = "0" . $current;
	} else {
		$current_key = $current;
	}
	
	if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ){
		//User time format in 12hr
		$hours[$current_key] = ( $current > 12 ? $current-12 : $current );
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

$minutes = array();
$minutes["00"] = "00";
for ( $current = 0 + $inc; $current < 60; $current += $inc ) {
	$minutes[$current] = $current;
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;


// Pull tasks dependencies
if ($loadFromTab && isset($_SESSION['tasks_subform']['hdependencies'])) {
	$deps = trim($_SESSION['tasks_subform']['hdependencies'], " \t\r\n,");
}
if ($deps) {
	$sql = "SELECT task_id, task_name FROM tasks WHERE task_id in ($deps)";
} else {
	$sql = "
		SELECT t.task_id, t.task_name
		FROM tasks t, task_dependencies td
		WHERE td.dependencies_task_id = $task_id
		AND t.task_id = td.dependencies_req_task_id
	";
}
$taskDep = db_loadHashList( $sql );

?>
<form name="dependFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project;?>" method="post">
<input name="dosql" type="hidden" value="do_task_aed" />
<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
<input name="sub_form" type="hidden" value="1" />
<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std">
<tr>
	<td  align="center" width="50%">
		<table cellspacing="0" cellpadding="2" border="0">
			<?php
				if($can_edit_time_information){
			?>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_start_date" id="task_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="start_date" id="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : "" ;?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar(document.dependFrm.start_date)">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
				<td>
					<table><tr>
						
				<?php
					echo "<td>" . arraySelect($hours, "start_hour",'size="1" onchange="setAMPM(this)" class="text"', $start_date ? $start_date->getHour() : $start ) . "</td><td>" . " : " . "</td>";
					echo "<td>" . arraySelect($minutes, "start_minute",'size="1" class="text"', $start_date ? $start_date->getMinute() : "0" ) . "</td>";
					if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
						echo '<td><input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ( $start_date ? $start_date->getAMPM() : ( $start > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" /></td>';
					}
				?>
					</tr></table>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Finish Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_end_date" id="task_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
					<input type="text" name="end_date" id="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar(document.dependFrm.end_date)">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
				<td>
				<table><tr>
				<?php
					echo "<td>" . arraySelect($hours, "end_hour",'size="1" onchange="setAMPM(this)" class="text"', $end_date ? $end_date->getHour() : $end ) . "</td><td>" . " : " . "</td>";
					echo "<td>" .arraySelect($minutes, "end_minute",'size="1" class="text"', $end_date ? $end_date->getMinute() : "00" ) . "</td>";
					if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
						echo '<td><input type="text" name="end_hour_ampm" id="end_hour_ampm" value="' . ( $end_date ? $end_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" /></td>';
					}
				?>
				</tr></table>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Expected Duration' );?>:</td>
				<td nowrap="nowrap">
					<input type="text" class="text" name="task_duration" maxlength="8" size="6" value="<?php echo isset($obj->task_duration) ? $obj->task_duration : 1;?>" />
				<?php
					echo arraySelect( $durnTypes, 'task_duration_type', 'class="text"', $obj->task_duration_type, true );
				?>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Calculate' );?>:</td>
				<td nowrap="nowrap">
					<input type="button" value="<?php echo $AppUI->_('Duration');?>" onclick="calcDuration(document.dependFrm)" class="button" />
					<input type="button" value="<?php echo $AppUI->_('Finish Date');?>" onclick="calcFinish(document.dependFrm)" class="button" />
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Dynamic Task' );?></td>
				<td nowrap="nowrap">
					<input type="radio" name="task_dynamic" value="1" <?php if($obj->task_dynamic=="1") echo "checked"?> />
				</td>
			</tr>
			<tr>
				<td align="center" nowrap="nowrap" colspan="3"><b><?php echo $AppUI->_( 'Dependency Tracking' );?></b></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'On' );?></td>
				<td nowrap="nowrap">
					<input type="radio" name="task_dynamic" value="31" <?php if($obj->task_dynamic > '20') echo "checked"?> />
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Off' );?></td>
				<td nowrap="nowrap">
					<input type="radio" name="task_dynamic" value="0" <?php if($obj->task_dynamic == '0' || $obj->task_dynamic == '11') echo "checked"?> />
				</td>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Do not track this task' );?>
				
					<input type="checkbox" name="task_dynamic_nodelay" value="1" <?php if(($obj->task_dynamic > '10') && ($obj->task_dynamic < 30)) echo "checked"?> />
				</td>
			</tr>
			<?php
				} else {  
			?>
			<tr>
					<td colspan='2'><?php echo $AppUI->_("Only the task owner, project owner, or system administrator is able to edit time related information."); ?></td>
				</tr>
			<?php
				}// end of can_edit_time_information
			?>
		</table>
	</td>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'All Tasks' );?>:</td>
				<td><?php echo $AppUI->_( 'Task Dependencies' );?>:</td>
			</tr>
			<tr>
				<td>
					<select name='all_tasks' class="text" style="width:220px" size="10" class="text" multiple="multiple">
						<?php echo str_replace("selected", "", $task_parent_options); // we need to remove selected added from task_parent options ?>
					</select>
				</td>
				<td>
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:220px" size="10" class="text" multiple="multiple" ', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<input type="checkbox" name="set_task_start_date" /><?php echo $AppUI->_( 'Set task start date based on dependency' );?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addTaskDependency(document.dependFrm)" /></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeTaskDependency(document.dependFrm)" /></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="hdependencies" />
</form>
<script language="javascript">
  subForm.push( new FormDefinition(<?php echo $tab; ?>, document.dependFrm, checkDepend, saveDepend));
</script>
