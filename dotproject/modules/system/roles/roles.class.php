<?php /* ROLES $Id$ */

class CRole {
	var $role_id = NULL;
	var $role_name = NULL;
	var $role_description = NULL;
	var $role_type = NULL;
	var $role_module = NULL;

	function CRole( $name='', $description='', $type='0', $module='0' ) {
		$this->role_name = $name;
		$this->role_description = $description;
		$this->role_type = $type;
		$this->role_module = $module;
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
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		if( $this->role_id ) {
			$ret = db_updateObject( 'roles', $this, 'role_id', false );
		} else {
			$ret = db_insertObject( 'roles', $this, 'role_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM roles WHERE role_id = '$this->role_id'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>