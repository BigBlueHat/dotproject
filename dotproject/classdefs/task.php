<?php

class dotTask {
	var $task_id = NULL;
	var $task_name = NULL;
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_owner = NULL;
	var $task_start_date = NULL;
	var $task_duration = NULL;
	var $task_hours_worked = NULL;
	var $task_end_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_precent_complete = NULL;
	var $task_description = NULL;
	var $task_target_budget = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;
	var $task_order = NULL;
	var $task_client_publish = NULL;

	function dotTask() {
	}

	function ListComments() {
		$sql = "SELECT task_name, user_username, comment_title,  comment_body, comment_date"
			."\nFROM tasks, task_comments"
			."\nLEFT JOIN users ON users.user_id = task_comments.comment_user"
			."\nWHERE task_id = $this->task_id AND comment_task = task_id"
			."\nORDER BY comment_date";
		return DB_loadList( $sql );
	}

	function listAssignedUsers() {
		$sql = "SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email"
			."\nFROM users u, user_tasks t"
			."\nWHERE t.task_id=$this->task_id AND t.user_id=u.user_id";
		return DB_loadList( $sql );
	}

	function Load( $oid ) {
		$obj = new dotTask();
		$ret = DB_loadObject( "SELECT * FROM tasks WHERE task_id=$oid", $obj );
		AppLog( "tasks/task/$obj->task_id", 'load', $ret );
		return $obj;
	}

	function Check() {
		// TODO
		return NULL; // object is ok
	}

	function Bind( $hash ) {
		global $user_cookie;
		is_array( $hash ) or die( "dotTask.Bind : hash expected" );
		bindHashToObject( $hash, $this );
		intval( $this->task_milestone )
			or $this->task_milestone = 0;
		if ($hash['hassign'] != "") {
			$this->assigees = explode( ',', $hash['hassign'] );
		}
		if (intval( $hash['duration'] ) && intval( $hash['dayhour'] )) {
			$this->task_duration = $hash['duration'] * $hash['dayhour'];
		}
		$this->_creator = intval($user_cookie);
	}

	function Store() {
		$msg = $this->Check();
		if ($msg) {
			return $msg;
		}
		if ($this->task_id) {
			$ret = DB_updateObject( 'tasks', $this, 'task_id' );
			AppLog( "tasks/task/$this->task_id", 'update', $ret );
			if (!$ret) {
				return DB_Error();
			} else {
				return "Task updated";
			}

		} else {
			$ret = DB_insertObject( 'tasks', $this, 'task_id' );
			AppLog( "tasks/task/$this->task_id", 'insert', $ret );
			// what -1 means for user_type ??
			DB_exec( "insert into user_tasks (user_id, task_id, user_type) values ( $this->_creator, $this->task_id, -1)" );

			if (!$ret)
				return DB_Error();
			if (!$this->task_parent) {
				$this->task_parent = $this->task_id;
				DB_exec( "UPDATE tasks SET task_parent=$this->task_parent WHERE task_id=$this->task_id" );
			}
			return "Task added";
		}

		if (count($this->assigees)) {
			DB_exec("DELETE FROM user_tasks WHERE task_id=$this->task_id AND user_type=0" );
			foreach ($this->assigees as $user_id) {
				if (intval($user_id) > 0)
					$ret = DB_exec( "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($user_id, $this->task_id, 0)" );
			}
		}
	}

	function Delete() {
		$ret = DB_delete( 'tasks', 'task_id', $this->task_id );
		AppLog( "tasks/task/$this->task_id", 'delete', $ret );
		if( ! $ret )
			return DB_Error();
		DB_delete( 'user_tasks', 'task_id', $this->task_id );
		return "Task deleted";
	}
}

?>