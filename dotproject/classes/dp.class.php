<?php /* CLASSES $Id$ */

/**
 *	CDpObject Abstract Class.
 */
class CDpObject {
/**
 *	@var string Name of the table in the db schema relating to child class
 */
	var $_tbl = '';
/**
 *	@var string Name of the primary key field in the table
 */
	var $_tbl_key = '';

/**
 *	object constructor to set table and key field
 *
 *	can be overloaded/supplemented by the child class
 *	@param string $table name of the table in the db schema relating to child class
 *	@param string $key name of the primary key field in the table
 */
	function CDpObject( $table, $key ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
	}

/**
 *	binds a named array/hash to this object
 *
 *	can be overloaded/supplemented by the child class
 *	@param array $hash named array
 *	@return null|string	null is operation was satisfactory, otherwise returns an error
 */
	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

/**
 *	binds an array/hash to this object
 *	@param int $oid optional argument, if not specifed then the value of current key is used
 *	@return any result from the database operation
 */
	function load( $oid=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$oid = $this->$k;
		$sql = "SELECT * FROM $this->_tbl WHERE $this->_tbl_key=$oid";
		return db_loadObject( $sql, $this );
	}

/**
 *	generic check method
 *
 *	can be overloaded/supplemented by the child class
 *	@return null if the object is ok
 */
	function check() {
		return NULL;
	}

/**
 *	inserts a new row if id is zero or updates an existing row in the database table
 *
 *	can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
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

/**
 *	generic check for whether dependancies exist for this object in the db schema
 *
 *	can be overloaded/supplemented by the child class
 *	@param string $msg error message returned
 *	@param int $oid optional key index
 *	@return true|false
 */
	function canDelete( &$msg, $oid=null ) {
		if ($oid) {
			$k = $this->_tbl_key;
			$this->$k = intval( $oid );
		}
		return true;
	}

/**
 *	default delete method
 *
 *	can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
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

/**
 *	get specifically denied records from a table/module based on a user
 *	@param int $uid user id number
 *	@return array
 */
	function getDeniedRecords( $uid ) {
		$uid = intval( $uid );
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

/**
 *	returns a list of records exposed to the user
 *	@param int $uid user id number
 *	@param string $fields optional fields to be returned by the query, default is all
 *	@param string $orderby optional sort order for the query
 *	@param string $index optional name of field to index the returned array
 *	@return array
 */
// returns a list of records exposed to the user
	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null ) {
		$uid = intval( $uid );
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