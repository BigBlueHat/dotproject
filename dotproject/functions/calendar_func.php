<?php /* FUNCTIONS $Id$ */
##
## Calendar functions
##

$strMaxLen = 20;

function getTasksForPeriod( $start_date, $end_date, $company_id=0 ) {
	GLOBAL $AppUI;
// convert to default db time stamp
	$db_start = db_unix2dateTime( $start_date->getTimestamp() );
	$db_end = db_unix2dateTime( $end_date->getTimestamp() );

// assemble where clause
	$where = "task_project = project_id"
		."\nAND (task_start_date <= '$db_end' AND task_end_date >= '$db_start')";
/*
		OR
		task_end_date BETWEEN '$db_start' AND '$db_end'
		OR
		(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_end'
		OR
		(DATE_ADD(task_start_date, INTERVAL task_duration DAY)) BETWEEN '$db_start' AND '$db_end'
*/
	$where .= $company_id ? "\nAND project_company = $company_id" : '';

// exclude read denied projects
	$sql = "
	SELECT project_id, project_id
	FROM projects, permissions
	WHERE permission_user = $AppUI->user_id
		AND permission_grant_on = 'projects'
		AND permission_item = project_id
		AND permission_value = 0
	";
	$deny = db_loadHashList( $sql );
	$where .= count($deny) > 0 ? "\nAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

// get any specifically denied tasks
	$sql = "
	SELECT task_id, task_id
	FROM tasks, permissions
	WHERE permission_user = $AppUI->user_id
		AND permission_grant_on = 'tasks'
		AND permission_item = task_id
		AND permission_value = 0
	";
	$deny = db_loadHashList( $sql );
	$where .= count($deny) > 0 ? "\nAND task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

// assemble query
	$sql = "
	SELECT
		task_name, task_id, task_start_date, task_end_date,
		task_duration, task_duration_type,
		project_color_identifier AS color,
		project_name
	FROM tasks,projects
	WHERE $where
	ORDER BY task_start_date
	";
// execute and return
	return db_loadList( $sql );
}

function getEventsForPeriod( $start_date, $end_date ) {
// the event times are stored as unix time stamps, just to be different

// convert to default db time stamp
	$db_start = $start_date->getTimestamp();
	$db_end = $end_date->getTimestamp();

// assemble query
	$sql = "SELECT * FROM events WHERE event_start_date BETWEEN '$db_start' AND '$db_end'";
#echo "<pre>$sql</pre>";
// execute and return
	return db_loadList( $sql );
}

function addTaskLinks( $tasks, $startPeriod, $endPeriod, &$links, $strMaxLen ) {
	GLOBAL $AppUI;
	$link = array();
	// assemble the links for the tasks
	foreach ($tasks as $row) {
	// the link
		$link['href'] = "?m=tasks&a=view&task_id=".$row['task_id'];
		$link['alt'] = $row['project_name'].":\n".$row['task_name'];

	// the link text
		if (strlen( $row['task_name'] ) > $strMaxLen) {
			$row['task_name'] = substr( $row['task_name'], 0, $strMaxLen ).'...';
		}
		$link['text'] = '<span style="color:'.bestColor($row['color']).';background-color:#'.$row['color'].'">'.$row['task_name'].'</span>';

	// determine which day(s) to display the task
		$start = new CDate( db_dateTime2unix( $row['task_start_date'] ) );
		$end = new CDate( db_dateTime2unix( $row['task_end_date'] ) );
		$durn = $row['task_duration'];
		$durnType = $row['task_duration_type'];

		if ($start->isBetween( $startPeriod, $endPeriod )) {
			$temp = $link;
			$temp['alt'] = "START [".($durn < 24 ? $durn.' hours' : floor($durn/24).' days')."]\n".$link['alt'];
			$links[$start->getDay()][] = $temp;
		}
		if ($end->isBetween( $startPeriod, $endPeriod ) && $start->daysTo( $end ) != 0) {
			$temp = $link;
			$temp['alt'] = "FINISH\n".$link['alt'];
			$links[$end->getDay()][] = $temp;
		}
	// convert duration to days
		if ($durnType == 'hours') {
			if ($durn > $AppUI->cfg['daily_working_hours']) {
				$durn /= $AppUI->cfg['daily_working_hours'];
			} else {
				$durn = 0.0;
			}
		}

	// fill in between start and finish based on duration
		if ($durn > 1) {
			
		// notes:
		// start date is not in a future month, must be this or past month
		// start date is counted as one days work
		// business days are not taken into account
			$target = $start;
			$target->addDays( $durn );

			if ($start->compareTo( $startPeriod ) > 0) {
				$temp = $start;
				$temp->addDays(+1);
			} else {
				$temp = $startPeriod;
			}

			while ($endPeriod->compareTo( $temp ) > 0) {
				if ($target->compareTo( $temp ) > 0) {
					if ($temp->daysTo($end) > 1) {
						$links[$temp->getDay()][] = $link;
					}
				}
				$temp->addDays(+1);
			}
		}
	}
}
?>