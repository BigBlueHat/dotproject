<?php /* ADMIN $Id$ */

/**
* User Class
*/
class CUser extends CDpObject {
	var $user_id = NULL;
	var $user_username = NULL;
	var $user_password = NULL;
	var $user_parent = NULL;
	var $user_type = NULL;
        var $user_contact = NULL;
/*	var $user_first_name = NULL;
	var $user_last_name = NULL;
	var $user_company = NULL;
	var $user_department = NULL;
	var $user_email = NULL;
	var $user_phone = NULL;
	var $user_home_phone = NULL;
	var $user_mobile = NULL;
	var $user_address1 = NULL;
	var $user_address2 = NULL;
	var $user_city = NULL;
	var $user_state = NULL;
	var $user_zip = NULL;
	var $user_country = NULL;
	var $user_icq = NULL;
	var $user_aol = NULL;
	var $user_birthday = NULL;
	var $user_pic = NULL;
	var $user_owner = NULL;
	var $user_signature = NULL;*/

	function CUser() {
		$this->CDpObject( 'users', 'user_id' );
	}

	function check() {
		if ($this->user_id === NULL) {
			return 'user id is NULL';
		}
		if ($this->user_password !== NULL) {
			$this->user_password = db_escape( trim( $this->user_password ) );
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->user_id ) {
		// save the old password
			$sql = "SELECT user_password FROM users WHERE user_id = $this->user_id";
			db_loadHash( $sql, $hash );
			$pwd = $hash['user_password'];	// this will already be encrypted

			$ret = db_updateObject( 'users', $this, 'user_id', false );

		// update password if there has been a change
			$sql = "UPDATE users SET user_password = MD5('$this->user_password')"
				."\nWHERE user_id = $this->user_id AND user_password != '$pwd'";
			db_exec( $sql );
		} else {
			$ret = db_insertObject( 'users', $this, 'user_id' );
		// encrypt password
			$sql = "UPDATE users SET user_password = MD5('$this->user_password')"
				."\nWHERE user_id = $this->user_id";
			db_exec( $sql );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
}

/**
* Permissions class
*/
class CPermission extends CDpObject {
	var $permission_id = NULL;
	var $permission_user = NULL;
	var $permission_grant_on = NULL;
	var $permission_item = NULL;
	var $permission_value = NULL;

	function CPermission() {
		$this->CDpObject( 'permissions', 'permission_id' );
	}
}

?>
