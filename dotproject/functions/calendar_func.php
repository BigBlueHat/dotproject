<?php /* $Id$ */
##
## Calendar functions
##

function getTasksForPeriod( $start_date, $end_date, $company_id=0 ) {
	GLOBAL $AppUI;
// convert to default db time stamp
	$db_start = db_unix2dateTime( $start_date->getTimestamp() );
	$db_end = db_unix2dateTime( $end_date->getTimestamp() );

// assemble where clause
	$where = "task_project = project_id";
	$where .= $company_id ? " AND project_company = $company_id" : '';
	$where .= "\nAND (
		task_start_date BETWEEN '$db_start' AND '$db_end'
		OR
		task_end_date BETWEEN '$db_start' AND '$db_end'
		OR
		(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_end'
	)
	";

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
	$where .= count($deny) > 0 ? "\nAND project_id NOT IN (" . implode( ',', $deny ) . ')' : '';

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
		task_duration AS task_duration,
		project_color_identifier AS color,
		project_name
	FROM tasks, projects
	WHERE $where
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
?>