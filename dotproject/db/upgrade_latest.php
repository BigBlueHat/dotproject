<?php

/**
 *  This segment will extract all the project/department and project/contact relational info and populate the project_departments and project_contacts tables.
 **/
 
$sql = "SELECT project_id, project_departments, project_contacts FROM projects";
$projects = db_loadList( $sql );

//split out related departments and store them seperatly.
$sql = 'DELETE FROM project_departments';
db_exec( $sql );
//split out related contacts and store them seperatly.
$sql = 'DELETE FROM project_contacts';
db_exec( $sql );

foreach ($projects as $project){
	$departments = explode(',',$project['project_departments']);
	foreach($departments as $department){
		$sql = 'INSERT INTO project_departments (project_id, department_id) values ('.$project['project_id'].', '.$department.')';
		db_exec( $sql );
	}

	$contacts = explode(',',$project['project_contacts']);
	foreach($contacts as $contact){
		$sql = 'INSERT INTO project_contacts (project_id, contact_id) values ('.$project['project_id'].', '.$contact.')';
		db_exec( $sql );
	}
}

/**
 *  This segment will extract all the task/department and task/contact relational info and populate the task_departments and task_contacts tables.
 **/

$sql = "SELECT task_id, task_departments, task_contacts FROM tasks";
$tasks = db_loadList( $sql );

//split out related departments and store them seperatly.
$sql = 'DELETE FROM task_departments';
db_exec( $sql );
//split out related contacts and store them seperatly.
$sql = 'DELETE FROM task_contacts';
db_exec( $sql );

foreach ($tasks as $task){
	$departments = explode(',',$task['task_departments']);
	foreach($departments as $department){
		$sql = 'INSERT INTO task_departments (task_id, department_id) values ('.$task['task_id'].', '.$department.')';
		db_exec( $sql );
	}

	$contacts = explode(',',$task['task_contacts']);
	foreach($contacts as $contact){
		$sql = 'INSERT INTO task_contacts (task_id, contact_id) values ('.$task['task_id'].', '.$contact.')';
		db_exec( $sql );
	}
}

include ('upgrade_contacts.php');
?>
