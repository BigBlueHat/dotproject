<?php
$do_report 		= dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	= dPgetParam( $_POST, "log_end_date", 0 );
$log_all_projects 	= dPgetParam($_POST["log_all_projects"], 0);
$log_all		= dPgetParam($_POST["log_all"], 0);

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );
?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
</script>

<form name="editFrm" action="index.php?m=projects&a=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">


<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all_projects" <?php if ($log_all_projects) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All Projects' );?>
	</td>
	
	<td nowrap='nowrap'>
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){
	
	$sql = "SELECT u.user_username, 
			u.user_first_name, 
			u.user_last_name, 
			sum( t.task_duration ) as hours_allocated ,
			sum( tl.task_log_hours )  as hours_worked
		FROM users AS u, 
		         user_tasks AS ut, 
		         tasks AS t 
			LEFT  JOIN task_log AS tl ON ( tl.task_log_task = t.task_id ) 
		WHERE ut.user_id = u.user_id 
			AND ut.task_id = t.task_id";

	if(!$log_all_projects){
		$sql .= " AND t.task_project='$project_id'\n";
	}
	
	if(!$log_all){
		$sql .= " AND t.task_start_date >= \"".$start_date->format( FMT_DATETIME_MYSQL )."\"
		              AND t.task_start_date <= \"".$end_date->format( FMT_DATETIME_MYSQL )."\"";
			   //AND tl.task_log_date >= \"".$start_date->format( FMT_DATETIME_MYSQL )."\"
		              //AND tl.task_log_date <= \"".$end_date->format( FMT_DATETIME_MYSQL )."\"";
	}
	
	$sql .= " GROUP  BY u.user_id";
	
	//echo "<pre>$sql</pre>";
	$logs = db_loadlist($sql);
	echo db_error();
?>

<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th colspan='2'><?php echo $AppUI->_('User');?></th>
		<th><?php echo $AppUI->_('Hours allocated'); ?></th>
		<th><?php echo $AppUI->_('Hours worked'); ?></th>
		<th>%</th>
	</tr>

<?php
	if($logs){
		$percentage_sum = $hours_allocated_sum = $hours_worked_sum = 0;
		
		foreach($logs as $log){
			$percentage = $log["hours_allocated"]>0 ? ($log["hours_worked"]*100)/$log["hours_allocated"] : 100;
			$percentage_sum += $percentage;
			$hours_allocated_sum += number_format($log["hours_allocated"], 2);
			$hours_worked_sum   += number_format($log["hours_worked"],2);
			?>
			<tr>
				<td><?php echo "(".$log["user_username"].") </td><td> ".$log["user_first_name"]." ".$log["user_last_name"]; ?></td>
				<td align='right'><?php echo number_format($log["hours_allocated"],2); ?> </td>
				<td align='right'><?php echo number_format($log["hours_worked"],2); ?> </td>
				<td align='right'> <?php echo number_format($percentage,0); ?>% </td>
			</tr>
			<?php
		}
		$percentage_average = number_format($percentage_sum/count($logs), 0);
		?>
			<tr>
				<td colspan='2'><?php echo $AppUI->_('Total'); ?></td>
				<td align='right'><?php echo $hours_allocated_sum; ?></td>
				<td align='right'><?php echo $hours_worked_sum; ?></td>
				<td align='right'><?php echo $percentage_average; ?>%</td>
			</tr>
		<?php
	} else {
	}
}
?>
</table>
