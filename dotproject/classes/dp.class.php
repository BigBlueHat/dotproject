<?php /* CLASSES $Id$ */

##
## CDpObject Abstract Class
##

class CDpObject {
// table name
	var $_tbl = '';
// table primary key field
	var $_tbl_key = '';

// object constructor to set table and key field
	function CDpObject( $table, $key ) {
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

// default check for delete dependancies (can be overloaded)
	function canDelete( &$msg, $oid=null ) {
		if ($oid) {
			$k = $this->_tbl_key;
			$this->$k = intval( $oid );
		}
		return true;
	}

// default delete method (can be overloaded)
	function delete() {
		if (!$this->canDelete( $msg )) {
			return $msg;
		}

		$k = $this->_tbl_key;
		$sql = "DELETE FROM $this->_tbl WHERE $this->_tbl_key = '".$this->$k."'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

// get specifically denied records from a module based on a user
	function getDeniedRecords( $uid ) {
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getDeniedRecords failed" );

		// get read denied projects
		$deny = array();
		$sql = "
		SELECT $this->_tbl_key
		FROM $this->_tbl, permissions
		WHERE permission_user = $uid
			AND permission_grant_on = '$this->_tbl'
			AND permission_item = $this->_tbl_key
			AND permission_value = 0
		";
		return db_loadColumn( $sql );
	}

// returns a list of records exposed to the user
	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null ) {
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		$deny = $this->getDeniedRecords( $uid );

		$sql = "SELECT $fields"
			. "\nFROM $this->_tbl, permissions"
			. "\nWHERE permission_user = $uid"
			. "\n	AND permission_value <> 0"
			. "\n	AND ("
			. "\n		(permission_grant_on = 'all')"
			. "\n		OR (permission_grant_on = '$this->_tbl' AND permission_item = -1)"
			. "\n		OR (permission_grant_on = '$this->_tbl' AND permission_item = $this->_tbl_key)"
			. "\n	)"
			. (count($deny) > 0 ? "\nAND $this->_tbl_key NOT IN (" . implode( ',', $deny ) . ')' : '')
			. ($orderby ? "\nORDER BY $orderby" : '');

		return db_loadHashList( $sql, $index );	
	}
}
?>