<?php /* $Id$ */
##
## CSysKey Class
##

class CSysKey {
	var $syskey_id = NULL;
	var $syskey_name = NULL;
	var $syskey_label = NULL;
	var $syskey_type = NULL;
	var $syskey_sep1 = NULL;
	var $syskey_sep2 = NULL;

	function CSysKey( $name, $label, $type='0', $sep1="\n", $sep2 = '|' ) {
		$this->syskey_name = $name;
		$this->syskey_label = $label;
		$this->syskey_type = $type;
		$this->syskey_sep1 = $sep1;
		$this->syskey_sep2 = $sep2;
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
		if( $this->syskey_id ) {
			$ret = db_updateObject( 'syskeys', $this, 'syskey_id', false );
		} else {
			$ret = db_insertObject( 'syskeys', $this, 'syskey_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM syskeys WHERE syskey_id = '$this->syskey_id'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}

##
## CSysKey Class
##

class CSysVal {
	var $sysval_id = NULL;
	var $sysval_key_id = NULL;
	var $sysval_title = NULL;
	var $sysval_value = NULL;

	function CSysVal( $key, $title, $value ) {
		$this->sysval_key_id = $key;
		$this->sysval_title = $title;
		$this->sysval_value = $value;
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
		if( $this->sysval_id ) {
			$ret = db_updateObject( 'sysvals', $this, 'sysval_id', false );
		} else {
			$ret = db_insertObject( 'sysvals', $this, 'sysval_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM sysvals WHERE sysval_id = '$this->sysval_id'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}

?>