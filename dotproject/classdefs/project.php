<?php

class dotProject {
	var $project_id = NULL;
	var $project_company = NULL;
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

	function dotProject() {
	}

	function getHashList() {
		// TODO access rights
		$sql = "SELECT project_id,project_name FROM projects ORDER BY project_name";
		return DB_loadHashList( $sql );
	}

	function Load( $oid ) {
		$obj = new dotProject();
		$ret = DB_loadObject( "SELECT * FROM projects WHERE project_id=$oid", $obj );
		AppLog( "projects/project/$obj->project_id", 'load', $ret );
		return $obj;
	}

	function Check() {
		// TODO
		return NULL; // object is ok
	}

	function Bind( $hash ) {
		global $user_cookie;
		is_array( $hash ) or die( "dotProject.Bind : hash expected" );
		bindHashToObject( $hash, $this );
		intval( $this->project_milestone )
			or $this->project_milestone = 0;
		if ($hash['hassign'] != "") {
			$this->assigees = explode( ',', $hash['hassign'] );
		}
		if (intval( $hash['duration'] ) && intval( $hash['dayhour'] )) {
			$this->project_duration = $hash['duration'] * $hash['dayhour'];
		}
		$this->_creator = intval($user_cookie);
	}

	function Store() {
		$msg = $this->Check();
		if( $msg ) {
			return $msg;
		}
		if ($this->project_id) {
			$ret = DB_updateObject( 'projects', $this, 'project_id' );
			AppLog( "projects/project/$this->project_id", 'update', $ret );
			if( ! $ret ) {
				return DB_Error();
			} else {
				return "Project updated";
			}

		} else {
			$ret = DB_insertObject( 'projects', $this, 'project_id' );
			AppLog( "projects/project/$this->project_id", 'insert', $ret );
			// what this -1 means for user_type ??
			DB_exec( "insert into user_projects (user_id, project_id, user_type) values ( $this->_creator, $this->project_id, -1)" );

			if (!$ret) {
				return DB_Error();
			}
			if (!$this->project_parent) {
				$this->project_parent = $this->project_id;
				DB_exec( "UPDATE projects SET project_parent=$this->project_parent WHERE project_id=$this->project_id" );
			}
			return "Project added";
		}
	}

	function Delete() {
		$ret = DB_delete( 'projects', 'project_id', $this->project_id );
		AppLog( "projects/project/$this->project_id", 'delete', $ret );
		if( ! $ret ) {
			return DB_Error();
		}
//		DB_delete( 'user_projects', 'project_id', $this->project_id );
		return "Project deleted";
	}
}

?>