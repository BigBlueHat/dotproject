<?php /* TASKS $Id$ */

require "{$AppUI->cfg['root_dir']}/classes/libmail.php";

##
## CTask Class
##
class CTask {
	var $task_id = NULL;
	var $task_name = NULL;
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_owner = NULL;
	var $task_start_date = NULL;
	var $task_duration = NULL;
	var $task_duration_type = NULL;
	var $task_hours_worked = NULL;
	var $task_end_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_percent_complete = NULL;
	var $task_description = NULL;
	var $task_target_budget = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;
	var $task_order = NULL;
	var $task_client_publish = NULL;
	var $task_dynamic = NULL;

	function CTask() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM tasks WHERE task_id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->task_id === NULL) {
			return 'task id is NULL';
		}
		if (!$this->task_milestone) {
			$this->task_milestone = '0';
		}
		if (!$this->task_dynamic) {
			$this->task_dynamic = '0';
		}
		if (!$this->task_duration) {
			$this->task_duration = '0';
		}
		if (!$this->task_duration_type) {
			$this->task_duration_type = 1;
		}
		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}
		if (!$this->task_hours_worked) {
			$this->task_hours_worked = '0';
		}
		if (!$this->task_percent_complete) {
			$this->task_percent_complete = 0;
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		GLOBAL $AppUI;
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->task_id ) {
			$this->_action = 'updated';
			$ret = db_updateObject( 'tasks', $this, 'task_id', false );
		} else {
			$this->_action = 'added';
			$ret = db_insertObject( 'tasks', $this, 'task_id' );
			if (!$this->task_parent) {
			// new task, parent = task id
				$sql = "UPDATE tasks SET task_parent = $this->task_id WHERE task_id = $this->task_id";
				db_exec( $sql );
			}
		// insert entry in user tasks
			$sql = "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($AppUI->user_id, $this->task_id, -1)";
			db_exec( $sql );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$this->_action = 'deleted';
	// delete linked user tasks
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

	// delete the tasks...what about orphans?
		$sql = "DELETE FROM tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function updateAssigned( $cslist ) {
	// delete all current entries
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id AND user_type = 0";
		db_exec( $sql );

	// process assignees
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
				$sql = "REPLACE user_tasks (user_id, task_id) VALUES ($user_id, $this->task_id)";
				db_exec( $sql );
			}
		}
	}

	function updateDependencies( $cslist ) {
	// delete all current entries
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
		db_exec( $sql );

	// process dependencies
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $task_id) {
			if (intval( $task_id ) > 0) {
				$sql = "REPLACE task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($this->task_id, $task_id)";
				db_exec($sql);
			}
		}
	}

	function notify() {
		GLOBAL $AppUI;

		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;
		$mail->Subject( "$projname::$this->task_name $this->_action" );

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\nc.user_email as creator_email,"
		."\nc.user_first_name as creator_first_name,"
		."\nc.user_last_name as creator_last_name,"
		."\no.user_email as owner_email,"
		."\no.user_first_name as owner_first_name,"
		."\no.user_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\na.user_email as assignee_email,"
		."\na.user_first_name as assignee_first_name,"
		."\na.user_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = "Project: $projname";
			$body .= "\nTask:    $this->task_name";
			$body .= "\nURL:     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			if ($users[0]['creator_email']) {
				$body .= "\n\n" . $AppUI->_('Creator').":"
					. "\n" . $users[0]['creator_first_name'] . " " . $users[0]['creator_last_name' ]
					. ", " . $users[0]['creator_email'];
			}
			$body .= "\n\n" . $AppUI->_('Owner').":"
				. "\n" . $users[0]['owner_first_name'] . " " . $users[0]['owner_last_name' ]
				. ", " . $users[0]['owner_email'];

			$mail->Body( $body );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}

		foreach ($users as $row) {
			if ($row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To( $row['assignee_email'] );
					$mail->Send();
				}
			}
		}
		return '';
	}
}

##
## CTask Class
##

class CTaskLog {
	var $task_log_id = NULL;
	var $task_log_task = NULL;
	var $task_log_name = NULL;
	var $task_log_description = NULL;
	var $task_log_creator = NULL;
	var $task_log_hours = NULL;
	var $task_log_date = NULL;
	var $task_log_costcode = NULL;

	function CTaskLog() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		$this->task_log_hours = (float) $this->task_log_hours;
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		GLOBAL $AppUI;
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->task_log_id ) {
			$this->_action = 'updated';
			$ret = db_updateObject( 'task_log', $this, 'task_log_id', false );
		} else {
			$this->_action = 'added';
			$ret = db_insertObject( 'task_log', $this, 'task_log_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$this->_action = 'deleted';
	// delete linked user tasks
		$sql = "DELETE FROM task_log WHERE task_log_id = $this->task_log_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>