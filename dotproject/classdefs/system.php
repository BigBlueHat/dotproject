<?php
##
## CPreference Class
##

class CPreferences {
	var $pref_user = NULL;
	var $pref_name = NULL;
	var $pref_value = NULL;

	function CPreferences() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return "CPreferences::bind failed";
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
			return "CPreference::store-check failed<br>$msg";
		}
		if (($msg = $this->delete())) {
			return "CPreference::store-delete failed<br>$msg";
		}
		if (!($ret = db_insertObject( 'user_preferences', $this, 'pref_user' ))) {
			return "CPreference::store failed <br>" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM user_preferences WHERE pref_user = $this->pref_user AND pref_name = '$this->pref_name'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>