<?php
##
## CProject Class
##

class CProject {
	var $project_id = NULL;
	var $project_company = NULL;
	var $project_department = NULL;
	var $project_name = NULL;
	var $project_short_name = NULL;
	var $project_owner = NULL;
	var $project_url = NULL;
	var $project_demo_url = NULL;
	var $project_start_date = NULL;
	var $project_end_date = NULL;
	var $project_actual_end_date = NULL;
	var $project_status = NULL;
	var $project_precent_complete = NULL;
	var $project_color_identifier = NULL;
	var $project_description = NULL;
	var $project_target_budget = NULL;
	var $project_actual_budget = NULL;
	var $project_creator = NULL;
	var $project_active = NULL;
	var $project_private = NULL;

	function CProject() {
		// empty constructor
	}

	function check() {
		if (!$this->project_active) {
			$this->project_active = '0';
		}
		if (!$this->project_private) {
			$this->project_private = '0';
		}

		// TODO
		return NULL; // object is ok
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		}
		bindHashToObject( $hash, $this );
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->project_id ) {
			$ret = db_updateObject( 'projects', $this, 'project_id', false );
		} else {
			$ret = db_insertObject( 'projects', $this, 'project_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT task_id FROM tasks WHERE task_project = $this->project_id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "You cannot delete a project that has tasks associated with it.";
		} else{
			$sql = "DELETE FROM projects WHERE project_id = $this->project_id";
			if (!db_exec( $sql )) {
				return db_error();
			} else {
				return NULL;
			}
		}
	}
}
?>