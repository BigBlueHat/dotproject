<?php /* SYSTEM $Id$ */

/**
* Preferences class
*/
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
			return "CPreference::store-check failed<br />$msg";
		}
		if (($msg = $this->delete())) {
			return "CPreference::store-delete failed<br />$msg";
		}
		if (!($ret = db_insertObject( 'user_preferences', $this, 'pref_user' ))) {
			return "CPreference::store failed <br />" . db_error();
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

/**
* Module class
*/
class CModule extends CDpObject {
	var $mod_id=null;
	var $mod_name=null;
	var $mod_directory=null;
	var $mod_version=null;
	var $mod_setup_class=null;
	var $mod_type=null;
	var $mod_active=null;
	var $mod_ui_name=null;
	var $mod_ui_icon=null;
	var $mod_ui_order=null;
	var $mod_ui_active=null;
	var $mod_description=null;
	var $permissions_item_label=null;
	var $permissions_item_field=null;
	var $permissions_item_table=null;

	function CModule() {
		$this->CDpObject( 'modules', 'mod_id' );
	}

	function install() {
		$sql = "SELECT mod_directory FROM modules WHERE mod_directory = '$this->mod_directory'";
		if (db_loadHash( $sql, $temp )) {
			// the module is already installed
			// TODO: check for older version - upgrade
			return false;
		}
		$this->store();
		return true;
	}

	function remove() {
		$sql = "DELETE FROM modules WHERE mod_id = $this->mod_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function move( $dirn ) {
		$temp = $this->mod_ui_order;
		if ($dirn == 'moveup') {
			$temp--;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order+1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		} else if ($dirn == 'movedn') {
			$temp++;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order-1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		}
		$sql = "UPDATE modules SET mod_ui_order = $temp WHERE mod_id = $this->mod_id";
		db_exec( $sql );

		$this->mod_id = $temp;
	}
// overridable functions
	function moduleInstall() {
		return null;
	}
	function moduleRemove() {
		return null;
	}
	function moduleUpgrade() {
		return null;
	}
}
?>
