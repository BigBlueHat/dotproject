<?php /* DEPARTMENTS $Id$ */
##
## CDepartment Class
##

class CDepartment {
	var $dept_id = NULL;
	var $dept_parent = NULL;
	var $dept_company = NULL;
	var $dept_name = NULL;
	var $dept_phone = NULL;
	var $dept_fax = NULL;
	var $dept_address1 = NULL;
	var $dept_address2 = NULL;
	var $dept_city = NULL;
	var $dept_state = NULL;
	var $dept_zip = NULL;
	var $dept_url = NULL;
	var $dept_desc = NULL;
	var $dept_owner = NULL;

	function CDepartment() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM departments WHERE dept_id = $oid";
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
		if ($this->dept_id === NULL) {
			return 'department id is NULL';
		}
		// TODO MORE
		if ($this->dept_id && $this->dept_id == $this->dept_parent) {
		 	return "cannot make myself my own parent (" . $this->dept_id . "=" . $this->dept_parent . ")";
		}
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}
		if( $this->dept_id ) {
			$ret = db_updateObject( 'departments', $this, 'dept_id', false );
		} else {
			$ret = db_insertObject( 'departments', $this, 'dept_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT * FROM departments WHERE dept_parent = $this->dept_id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "deptWithSub";
		}
		$sql = "SELECT * FROM projects WHERE project_department = $this->dept_id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "deptWithProject";
		}
		$sql = "DELETE FROM departments WHERE dept_id = $this->dept_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>
