<?php /* CLASSES $Id$ */

##
## CdpObject Abstract Class
##

class CdpObject {
// table name
	var $_tbl = '';
// table primary key field
	var $_tbl_key = '';

// object constructor to set table and key field
	function CdpObject( $table, $key ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
	}

// method to bind and array/hash to this object
	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

// empty check method (can be overloaded)
	function check() {
		// TODO MORE
		return NULL; // object is ok
	}

// default store method (can be overloaded)
	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		if( $this->$k ) {
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, false );
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

// default delete method (can be overloaded)
	function delete() {
		$k = $this->_tbl_key;
		$sql = "DELETE FROM $this->_tbl WHERE $this->_tbl_key = '".$this->$k."'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>