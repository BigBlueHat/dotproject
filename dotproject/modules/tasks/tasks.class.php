<?php /* TASKS $Id$ */

require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

/*
* CTask Class
*/
class CTask extends CDpObject {
/** @var int */
	var $task_id = NULL;
/** @var string */
	var $task_name = NULL;
/** @var int */
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
		$this->CDpObject( 'tasks', 'task_id' );
	}

// overload check
	function check() {
		if ($this->task_id === NULL) {
			return 'task id is NULL';
		}
	// ensure changes to checkboxes are honoured
		$this->task_milestone = intval( $this->task_milestone );
		$this->task_dynamic = intval( $this->task_dynamic );
		
		$this->task_percent_complete = intval( $this->task_percent_complete );

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
		return NULL;
	}

/**
* @todo Parent store could be partially used
*/
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

/**
* @todo Parent store could be partially used
* @todo Can't delete a task with children
*/
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
		GLOBAL $AppUI, $locale_char_set;
        
		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;
		
		$subj = "$projname::$this->task_name ".$AppUI->_($this->_action));
		
		$mail->Subject( $subj );

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
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
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

			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}

		foreach ($users as $row) {
			if ($row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To( $row['assignee_email'], true );
					$mail->Send();
				}
			}
		}
		return '';
	}
/**
* @param Date Start date of the period
* @param Date End date of the period
* @param integer The target company
*/
	function getTasksForPeriod( $start_date, $end_date, $company_id=0 ) {
		GLOBAL $AppUI;
	// convert to default db time stamp
		$db_start = $start_date->format( DATE_FORMAT_ISO );
		$db_end = $end_date->format( DATE_FORMAT_ISO );

	// assemble where clause
		$where = "task_project = project_id"
			."\n\tAND ("
			. "\n\t\t(task_start_date <= '$db_end' AND task_end_date >= '$db_start')"
			. "\n\t\tOR task_start_date BETWEEN '$db_start' AND '$db_end'"
			. "\n\t)";
	/*
			OR
			task_end_date BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration DAY)) BETWEEN '$db_start' AND '$db_end'
	*/
		$where .= $company_id ? "\n\tAND project_company = $company_id" : '';

	// exclude read denied projects
		$obj = new CProject();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

	// get any specifically denied tasks
		$obj = new CTask();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

	// assemble query
		$sql = "SELECT task_name, task_id, task_start_date, task_end_date,"
			. "\n\ttask_duration, task_duration_type,"
			. "\n\tproject_color_identifier AS color,"
			. "\n\tproject_name"
			. "\nFROM tasks,projects"
			. "\nWHERE $where"
			. "\nORDER BY task_start_date";
//echo "<pre>$sql</pre>";
	// execute and return
		return db_loadList( $sql );
	}
}

/**
* CTask Class
*/
class CTaskLog extends CDpObject {
	var $task_log_id = NULL;
	var $task_log_task = NULL;
	var $task_log_name = NULL;
	var $task_log_description = NULL;
	var $task_log_creator = NULL;
	var $task_log_hours = NULL;
	var $task_log_date = NULL;
	var $task_log_costcode = NULL;

	function CTaskLog() {
		$this->CDpObject( 'task_log', 'task_log_id' );
	}

// overload check method
	function check() {
		$this->task_log_hours = (float) $this->task_log_hours;
		return NULL;
	}
}
?>