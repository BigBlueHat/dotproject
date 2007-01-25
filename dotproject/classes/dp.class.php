<?php /* CLASSES $Id$ */

/**
 *	@package dotproject
 *	@subpackage modules
 *	@version $Revision$
 */

require_once $AppUI->getSystemClass('query');

/**
 *	CDpObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
class CDpObject {
// {{{ variables
/**
 *	@var string Name of the table in the db schema relating to child class
 */
	var $_tbl = '';
/**
 *	@var string Name of the primary key field in the table
 */
	var $_tbl_key = '';
/**
 *  @var string Name of the field containing the name for the item.
 */
 var $_tbl_name = '';
/**
 * @var string The name of the field, referencing the logical parent 
 * of the current item
 */
	var $_tbl_parent = null;
/**
 * @var object The logical parent of the current item
 */
	var $_parent = null;
/**
 *	@var string Error message
 */
	var $_error = '';

/**
 * @var object Query Handler
 */
 var $_query;

/** @var array the fields to search through */
 var $search_fields;
//}}}

/** {{{ constructor
 *	Object constructor to set table and key field
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $table name of the table in the db schema relating to child class
 *	@param string $key name of the primary key field in the table
 */
	function CDpObject( $table, $key )
	{
		global $dPconfig;
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_tbl_name = substr($key, 0, -2) . 'name';
		if (isset($dPconfig['dbprefix']))
			$this->_prefix = $dPconfig['dbprefix'];
		else
			$this->_prefix = '';
		$this->_query =& new DBQuery;
	} // }}} constructor

/**
 *	@return string Returns the error message
 */
	function getError()
	{
		return $this->_error;
	}
/**
 *	Binds a named array/hash to this object
 *
 *	can be overloaded/supplemented by the child class
 *	@param array $hash named array
 *	@return null|string	null is operation was satisfactory, otherwise returns an error
 */
	function bind( $hash )
	{
		if (!is_array( $hash )) {
			$this->_error = get_class( $this )."::bind failed.";
			return false;
		} else {
			bindHashToObject( $hash, $this );
			return true;
		}
	}

/**
 *	Binds an array/hash to this object
 *	@param int $oid optional argument, if not specifed then the value of current key is used
 *	@return any result from the database operation
 */
	function load( $oid=null , $strip = true)
	{
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		$this->_query->addWhere("$this->_tbl_key = $oid");
		$sql = $this->_query->prepare();
		$this->_query->clear();
		$obj = db_loadObject( $sql, $this, false, $strip );
		
		if ($this->_parent)
		{
			$parent_key = $this->_tbl_parent;
			$this->_parent->load($this->$parent_key);
		}
		
		return $obj;
	}

/**
 *	Returns an array, keyed by the key field, of all elements that meet
 *	the where clause provided. Ordered by $order key.
 */
	function loadAll($order = null, $where = null)
	{
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		if ($order)
		  $this->_query->addOrder($order);
		if ($where)
		  $this->_query->addWhere($where);
		$sql = $this->_query->prepare();
		$this->_query->clear();
		return db_loadHashList($sql, $this->_tbl_key);
	}

/**
 *	Return a DBQuery object seeded with the table name.
 *	@param string $alias optional alias for table queries.
 *	@return DBQuery object
 */
	function &getQuery($alias = null)
	{
		$this->_query->clear();
		$this->_query->addTable($this->_tbl, $alias);
		return $this->_query;
	}

/**
 *	Generic check method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null if the object is ok
 */
	function check()
	{
		return NULL;
	}
	
/**
*	Clone the current record
*
*	@author	handco <handco@users.sourceforge.net>
*	@return	object	The new record object or null if error
**/
	function duplicate()
	{
		$_key = $this->_tbl_key;
		
		$newObj = $this;
		// blanking the primary key to ensure that's a new record
		$newObj->$_key = '';
		
		return $newObj;
	}
    
	/**
	 *	Default trimming method for class variables of type string
	 *
	 *	@param object Object to trim class variables for
	 *	Can be overloaded/supplemented by the child class
	 *	@return none
	 */
	function dPTrimAll() {
		$trim_arr = get_object_vars($this);
		foreach ($trim_arr as $trim_key => $trim_val) {
			if (!(strcasecmp(gettype($trim_val), "string"))) {
				$this->{$trim_key} = trim($trim_val);
			}
		}
	}

/**
 *	Inserts a new row if id is zero or updates an existing row in the database table
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function store( $updateNulls = false ) {
        
        $this->dPTrimAll();
        
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		if( $this->$k ) {
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			$action = 'update';
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
			$action = 'add';
		}
		$details['name'] = $this->_tbl_name;
		$details['changes'] = $ret;
		addHistory($this->_tbl, $this->$k, $action, $details);
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function access($type) 
	{
		global $AppUI;
		
		$perms =& $AppUI->acl();
		$k = $this->_tbl_key;
		if (!$perms->checkModuleItem($this->_tbl, $type, $this->$k)) 
		{
			$msg = $AppUI->_('noPermission');
			return false;
		}
		
		if ($this->_parent)
			return $this->_parent->access($type);
			
		return true;
		
	}

/**
 *	Generic check for whether dependencies exist for this object in the db schema
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $msg Error message returned
 *	@param int Optional key index
 *	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
 *	@return true|false
 */
	function canDelete( &$msg, $oid=null, $joins=null )
	{
		global $AppUI;

		// First things first.  Are we allowed to delete?
		$acl =& $AppUI->acl();
		if ( ! $acl->checkModuleItem($this->_tbl, 'delete', $oid)) {
		  $msg = $AppUI->_( 'noDeletePermission' );
		  return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = $k;
			$join = '';
			
			$q  = new DBQuery;
			$q->addTable($this->_tbl);
			$q->addWhere("$k = '".$this->$k."'");
			$q->addGroup($k);
			foreach( $joins as $table ) {
				$q->addQuery("COUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}");
				$q->addJoin($table['name'], $table['name'], "{$table['joinfield']} = $k");
			}
			$sql = $q->prepare();
			$q->clear();

			$obj = null;
			if (!db_loadObject( $sql, $obj )) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$msg = $AppUI->_( "noDeleteRecord" ) . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

/**
 *	Default delete method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function delete( $oid=null )
	{
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$msg = null; //to be initialized by method below.
		if (!$this->canDelete( $msg )) {
			return $msg;
		}
                
                addHistory($this->_tbl, $this->$k, 'delete');
		$q  = new DBQuery;
		$q->setDelete($this->_tbl);
		$q->addWhere("$this->_tbl_key = '".$this->$k."'");
		$result = null;
		if (!$q->exec()) {
			$result = db_error();
		}
		$q->clear();
		return $result;
	}

/**
 *	Get specifically denied records from a table/module based on a user
 *	@param int User id number
 *	@return array
 */
	function getDeniedRecords( $uid )
	{
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getDeniedRecords failed, user id = 0" );

		$perms =& $GLOBALS['AppUI']->acl();
		return $perms->getDeniedItems($this->_tbl, $uid);
	}

/**
 *	Returns a list of records exposed to the user
 *	@param int User id number
 *	@param string Optional fields to be returned by the query, default is all
 *	@param string Optional sort order for the query
 *	@param string Optional name of field to index the returned array
 *	@param array Optional array of additional sql parameters (from and where supported)
 *	@return array
 */
// returns a list of records exposed to the user
	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) 
	{
		$perms =& $GLOBALS['AppUI']->acl();
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		$deny =& $perms->getDeniedItems( $this->_tbl, $uid );
		$allow =& $perms->getAllowedItems($this->_tbl, $uid);
		if (! $perms->checkModule($this->_tbl, "view", $uid )) {
		  if (! count($allow))
		    return array();	// No access, and no allow overrides, so nothing to show.
		} else {
		  $allow = array();	// Full access, allow overrides don't mean anything.
		}
		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if (@$extra['from']) {
			$this->_query->addTable($extra['from']);
		}
		
		if (count($allow)) {
		  $this->_query->addWhere("$this->_tbl_key IN (" . implode(',', $allow) . ")");
		}
		if (count($deny)) {
		  $this->_query->addWhere("$this->_tbl_key NOT IN (" . implode(",", $deny) . ")");
		}
		if (isset($extra['where'])) {
		  $this->_query->addWhere($extra['where']);
		}

		if ($orderby)
		  $this->_query->addOrder($orderby);

		return $this->_query->loadHashList( $index );
	}
  function getEdittableRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) 
  {
    $perms =& $GLOBALS['AppUI']->acl();
    $uid = intval( $uid );
    $uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
    $deny =& $perms->getDeniedItems( $this->_tbl, $uid );
    $allow =& $perms->getEdittableItems($this->_tbl, $uid);
    if (! $perms->checkModule($this->_tbl, "view", $uid )) {
      if (! count($allow))
        return array(); // No access, and no allow overrides, so nothing to show.
    } else {
      $allow = array(); // Full access, allow overrides don't mean anything.
    }
    $this->_query->clear();
    $this->_query->addQuery($fields);
    $this->_query->addTable($this->_tbl);

    if (@$extra['from']) {
      $this->_query->addTable($extra['from']);
    }
    
    if (count($allow)) {
      $this->_query->addWhere("$this->_tbl_key IN (" . implode(',', $allow) . ")");
    }
    if (count($deny)) {
      $this->_query->addWhere("$this->_tbl_key NOT IN (" . implode(",", $deny) . ")");
    }
    if (isset($extra['where'])) {
      $this->_query->addWhere($extra['where']);
    }

    if ($orderby)
      $this->_query->addOrder($orderby);

    return $this->_query->loadHashList( $index );
  }
	function getAllowedSQL( $uid, $index = null )
	{
		$perms =& $GLOBALS['AppUI']->acl();
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedSQL failed" );
		$deny =& $perms->getDeniedItems( $this->_tbl, $uid );
		$allow =& $perms->getAllowedItems($this->_tbl, $uid);
		if (! $perms->checkModule($this->_tbl, "view", $uid )) {
		  if (! count($allow))
		    return array("1=0");	// No access, and no allow overrides, so nothing to show.
		} else {
		  $allow = array();	// Full access, allow overrides don't mean anything.
		}

		if (! isset($index))
		   $index = $this->_tbl_key;
		$where = array();
		if (count($allow)) {
		  $where[] = "$index IN (" . implode(',', $allow) . ")";
		}
		if (count($deny)) {
		  $where[] = "$index NOT IN (" . implode(",", $deny) . ")";
		}
		return $where;
	}

	function setAllowedSQL($uid, &$query, $index = null, $key = null)
	{
		$perms =& $GLOBALS['AppUI']->acl();
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedSQL failed" );
		$deny =& $perms->getDeniedItems($this->_tbl, $uid );
		$allow =& $perms->getAllowedItems($this->_tbl, $uid);
		// Make sure that we add the table otherwise dependencies break
    if (! $key) {
      $key = substr($this->_tbl, 0, 2);
    }
		if (isset($index)) {
			$query->leftJoin($this->_tbl, $key, "$key.$this->_tbl_key = $index");
		}
		if (! $perms->checkModule($this->_tbl, "view", $uid )) {
		  if (! count($allow)) {
				// We need to ensure that we don't just break complex SQLs, but
				// instead limit to a nonsensical value.  This assumes that the
				// key is auto-incremented.
		    $query->addWhere("$this->_tbl_key = 0");
		    return;
			}
		} else {
		  $allow = array();	// Full access, allow overrides don't mean anything.
		}

		if (count($allow)) {
		  $query->addWhere("$key.$this->_tbl_key IN (" . implode(',', $allow) . ")");
		}
		if (count($deny)) {
		  $query->addWhere("$key.$this->_tbl_key NOT IN (" . implode(",", $deny) . ")");
		}
	}
	
	function search($keyword)
	{
		global $AppUI;

		$sql = '';
		foreach($this->search_fields as $field) {
			$sql .= " $field LIKE '%$keyword%' OR ";
		}
		$sql = substr($sql, 0, -4);
		
		// getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) 
		$list = $this->getAllowedRecords($AppUI->user_id, $this->_tbl_key . ',' . $this->_tbl_name, $this->_tbl_name, null, array('where' => $sql));

		if (empty($list)) {
			return $list;
		}

		if (!isset($this->_parent) || empty($this->_tbl_parent)) {
			foreach($list as $id => $name) {
				$results[$id]['name'] = $name;
			}
		} else {
			$q = new DBQuery;
			$q->addQuery($this->_tbl_key);
			$q->addQuery($this->_parent->_tbl_key . ' as id');
			$q->addQuery($this->_parent->_tbl_name . ' as parent');
			$q->addTable($this->_parent->_tbl);
			$q->addJoin($this->_tbl, 'children', $this->_tbl_parent . ' = ' . $this->_parent->_tbl_key);
			$q->addWhere($this->_tbl_key . ' in (' . implode(', ', array_keys($list)) . ')');
			$parents = $q->loadHashList($this->_tbl_key);

			foreach($list as $id => $item) {
				$results[$id]['name'] = $item;
				$results[$id]['parent_key'] = $this->_parent->_tbl_key;
				$results[$id]['parent_id'] = $parents[$id]['id'];
				$results[$id]['parent_name'] = $parents[$id]['parent'];
				$results[$id]['parent_type'] = $this->_parent->_tbl;
			}
		}

		//TODO: Sort by parent_name, name
		return $results;
	}
}
?>