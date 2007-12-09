<?php
/*{{{ Copyright 2003-2007 <developers@saki.com.au>

    This file is part of the collected works of Adam Donnison.

    This is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
}}}*/

require_once 'DP/Config.php';
require_once 'DP/AppUI.php';
require_once 'Zend/Db.php';

define('QUERY_STYLE_BOTH', Zend_Db::FETCH_BOTH);
define('QUERY_STYLE_ASSOC', Zend_Db::FETCH_ASSOC);
define('QUERY_STYLE_NUM', Zend_Db::FETCH_NUM);

// {{{ DP_Query class
/**
 * Database query class
 *
 * Container for creating prefix-safe queries.  Allows build up of
 * a select statement by adding components one at a time.
 *
 * @version	$Id: query.class.php,v 1.49 2007/10/27 20:46:14 cyberhorse Exp $
 * @author	Adam Donnison <adam@saki.com.au>
 * @license	GPL version 2 or later.
 * @copyright	(c) 2003 Adam Donnison
 */
class DP_Query {
	public $query; /**< Contains the query after it has been built. */
	public $table_list; /**< Array of tables to be queried */
	public $where; /**< WHERE component of the query */
	public $order_by; /**< ORDER BY component of the query */
	public $group_by; /**< GROUP BY component of the query */
	public $limit; /**< LIMIT component of the query */
	public $offset; /**< offset of the LIMIT component */
	public $join; /**< JOIN component of the query */
	public $type; /**< Query type eg. 'select', 'update' */
	public $update_list; /**< Array of fields->values to update */
	public $value_list; /**< Array of values used in INSERT or REPLACE statements */
	public $create_table; /**< Name of the table to create */
	public $create_definition; /**< Array containing information about the table definition */
	public $_table_prefix; /**< Internal string, table prefix, prepended to all queries */
	protected $_query_id = null; /**< Handle to the query result */
	private $_old_style = null; /**< Use the old style of fetch mode with ADODB */
	private $_fetch_style = null;
	private $_error = null;
	private $_bind_list = null;
	/**
	 * @var object Handle to Zend_DB database object
	 */
	private $_db = null;

	/** DP_Query constructor
	 */
	public function __construct() 
	{
		$this->_db = DP_Config::getDB();
		$this->_query_id = null;
		$this->clear();
	}

	/** Clear the current query and all set options
	 */
	function clear()
	{
		$this->type = 'select';
		$this->query = null;
		$this->table_list = null;
		$this->where = null;
		$this->order_by = null;
		$this->group_by = null;
		$this->limit = null;
		$this->offset = -1;
		$this->join = null;
		$this->value_list = null;
		$this->update_list = null;
		$this->create_table = null;
		$this->create_definition = null;
		$this->clearQuery();
		$this->_bind_list = array();
	}

	function clearQuery()
	{
		if ($this->_query_id) {
			$this->_db->closeConnection();
		}
		$this->_query_id = null;
	}
  
	/** Get database specific SQL used to concatenate strings.
	 * @return String containing SQL to concatenate supplied strings
	 */
	function concat()
	{
		$arr = func_get_args();
		$conc_str = call_user_func_array(array(&$this->_db, 'Concat'), $arr);
		return $conc_str;	
	}

	/** Get database specific SQL used to check for null values.
	*
	* Calls the ADODB IfNull method
	* @return String containing SQL to check for null field value
	*/
	function ifNull($field, $nullReplacementValue)
	{
		return $this->_db->IfNull($field, $nullReplacementValue);
	}	

  /** Add item to an internal associative array
   * 
   * Used internally with DBQuery
   *
   * @param	$varname	Name of variable to add/create
   * @param	$name	Data to add
   * @param	$id	Index to use in array.
   */
  function addMap($varname, $name, $id)
  {
    if (!isset($this->$varname))
      $this->$varname = array();
    if (isset($id))
      $this->{$varname}[$id] = $name;
    else
      $this->{$varname}[] = $name;
  }

  /** Add a table to the query
   *
   * A table is normally addressed by an
   * alias.  If you don't supply the alias chances are your code will
   * break.  You can add as many tables as are needed for the query.
   * E.g. addTable('something', 'a') will result in an SQL statement
   * of {PREFIX}table as a.
   * Where {PREFIX} is the system defined table prefix.
   *
   * @param	$name	Name of table, without prefix.
   * @param	$id	Alias for use in query/where/group clauses.
   */
  function addTable($name, $id = null)
  {
    $this->addMap('table_list', $name, $id);
  }

  /** Add a clause to an internal array
   *
   * Checks to see variable exists first.
   * then pushes the new data onto the end of the array.
   * @param $clause the type of clause to add
   * @param $value the clause value
   * @param $check_array defaults to true, iterates through each element in $value and adds them seperately to the clause
   */
  function addClause($clause, $value, $check_array = true)
  {
    dprint(__FILE__, __LINE__, 8, "Adding '$value' to $clause clause");
    if (!isset($this->$clause))
      $this->$clause = array();
    if ($check_array && is_array($value)) {
      foreach ($value as $v) {
	array_push($this->$clause, $v);
      }
    } else {
      array_push($this->$clause, $value);
    }
  }

  /** Add the select part (fields, functions) to the query
   *
   * E.g. '*', or 'a.*'
   * or 'a.field, b.field', etc.  You can call this multiple times
   * and it will correctly format a combined query.
   *
   * @param	$query	Query string to use.
   */
  function addQuery($query)
  {
    $this->addClause('query', $query);
  }

  /** Insert a value into the database
   * @param $field The field to insert the value into
   * @param $value The specified value
   * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
   * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
   */
  function addInsert($field, $value = null, $set = false, $func = false)
  {
		if (is_array($field) && $value == null) {
			foreach ($field as $f => $v)
				$this->addMap('value_list', $f, $v);
		} elseif ($set) {
			if (is_array($field))
				$fields = $field;
			else
				$fields = explode(',', $field);

			if (is_array($value))
				$values = $value;
			else
				$values = explode(',', $value);

			for($i = 0; $i < count($fields); $i++)
				$this->addMap('value_list', $this->quote($values[$i]), $fields[$i]);
		}
		else if (!$func)
    	$this->addMap('value_list', $this->quote($value), $field);
		else
    	$this->addMap('value_list', $value, $field);
    $this->type = 'insert';
  }
  
  // implemented addReplace() on top of addInsert()
  /** Insert a value into the database, to replace an existing row.
   * @param $field The field to insert the value into
   * @param $value The specified value
   * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
   * @param $func Defaults to false. If true will not use quotation marks around the value - to be used when the value being inserted includes a function
   */
  function addReplace($field, $value, $set = false, $func = false)
  {
  	 $this->addInsert($field, $value, $set, $func);
	 $this->type = 'replace';
  }

  /** Update a database value
   * @param $field The field to update
   * @param $value The value to set $field to
   * @param $set Defaults to false. If true will check to see if the fields or values supplied are comma delimited strings instead of arrays
   */
  function addUpdate($field, $value = null, $set = false)
  {
		if (is_array($field) && $value == null) {
			foreach ($field as $f => $v)
				$this->addMap('update_list', $f, $v);
		}	elseif ($set)	{
			if (is_array($field))
				$fields = $field;
			else
				$fields = explode(',', $field);

			if (is_array($value))
				$values = $value;
			else
				$values = explode(',', $value);

			for($i = 0; $i < count($fields); $i++)
				$this->addMap('update_list', $values[$i], $fields[$i]);
		}	else {
    	$this->addMap('update_list', $value, $field);
		}
    $this->type = 'update';
  }

  /** Create a database table
   * @param $table the name of the table to create
   */
  function createTable($table)
  {
    $this->type = 'createPermanent';
    $this->create_table = $table;
  }
  
  function createDatabase($database)
  {
  	$dict = NewDataDictionary($this->_db, DP_Config::getConfig('dbtype'));
  	$dict->CreateDatabase($database);
  }
  
  function DDcreateTable($table, $def, $opts)
  {
  	$dict = NewDataDictionary($this->_db, DP_Config::getConfig('dbtype'));
  	$query_array = $dict->ChangeTableSQL(DP_Config::getConfig('dbprefix') . $table, $def, $opts);
  	//returns 0 - failed, 1 - executed with errors, 2 - success
  	return $dict->ExecuteSQLArray($query_array);
  }
  
  function DDcreateIndex($name, $table, $cols, $opts)
  {
  	$dict = NewDataDictionary($this->_db, DP_Config::getConfig('dbtype'));
  	$query_array = $dict->CreateIndexSQL($name, $table, $cols, $opts);
  	//returns 0 - failed, 1 - executed with errors, 2 - success
  	return $dict->ExecuteSQLArray($query_array);
  }
  
  /** Create a temporary database table
   * @param $table the name of the temporary table to create.
   */
  function createTemp($table)
  {
    $this->type = 'create';
    $this->create_table = $table;
  }
  
  /** Drop a table from the database
   *
   * Use dropTemp() to drop temporary tables
   * @param $table the name of the table to drop.
   */
  function dropTable($table)
  {
    $this->type = 'drop';
    $this->create_table = $table;
  }

  /** Drop a temporary table from the database
   * @param $table the name of the temporary table to drop
   */
  function dropTemp($table)
  {
    $this->type = 'drop';
    $this->create_table = $table;
  }

	/** Alter a database table 
	 * @param $table the name of the table to alter
	 */
	function alterTable($table)
	{
		$this->create_table = $table;
		$this->type = 'alter';
	}

	/** Add a field definition for usage with table creation/alteration
	 * @param $name The name of the field
	 * @param $type The type of field to create
	 */
	function addField($name, $type)
	{
		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'ADD',
			'type' => '',
			'spec' => $name . ' ' . $type);
	}

	/**
	 * Alter a field definition for usage with table alteration
	 * @param $name The name of the field
	 * @param $type The type of the field
	 */
  function alterField($name, $type)
  {
    if (! is_array($this->create_definition))
      $this->create_definition = array();
    $this->create_definition[] = array('action' => 'CHANGE',
      'type' => '',
      'spec' => $name . ' ' . $name . ' ' . $type);
  }

	/** Drop a field from table definition or from an existing table
	 * @param $name The name of the field to drop
	 */
	function dropField($name)
	{
		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
			'type' => '',
			'spec' => $name);
	}

	/** Add an index
	*/
	function addIndex($name, $type)
	{
		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'ADD',
			'type' => 'INDEX',
			'spec' => $name . ' ' . $type);
	}

    /** Drop an index
    */
	function dropIndex($name)
	{
		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
			'type' => 'INDEX',
			'spec' => $name);
	}

	/** Remove a primary key attribute from a field
	*/
	function dropPrimary()
	{
		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
			'type' => 'PRIMARY KEY',
			'spec' => '');
	}

  /** Set a table creation definition from supplied array
   * @param $def Array containing table definition
   */
  function createDefinition($def)
  {
    $this->create_definition = $def;
  }

	function setDelete($table)
	{
		$this->type = 'delete';
		$this->addMap('table_list', $table, null);
	}

  /** Add a WHERE sub clause
   * 
   * The where clause can be built up one
   * part at a time and the resultant query will put in the 'and'
   * between each component.
   *
   * Make sure you use table aliases.
   *
   * @param	$query	Where subclause to use, not including WHERE keyword
   */
  function addWhere($query)
  {
    if (isset($query))
      $this->addClause('where', $query);
  }

  /** Add a JOIN condition
   *
   * Add a join condition to the query.  This only implements
   * left join, however most other joins are either synonymns or
   * can be emulated with where clauses.
   *
   * @param	$table	Name of table (without prefix)
   * @param	$alias	Alias to use instead of table name (required).
   * @param	$join	Join condition (e.g. 'a.id = b.other_id')
   *				or array of join fieldnames, e.g. array('id', 'name);
   *				Both are correctly converted into a join clause.
   */
  function addJoin($table, $alias, $join, $type = 'left')
  {
    $var = array ( 'table' => $table,
	  'alias' => $alias,
      'condition' => $join,
	  'type' => $type );

    $this->addClause('join', $var, false);
  }

  /** Add a left join condition
   *
   * Helper method to add a left join
   * @see addJoin()
   * @param $table Name of table (without prefix)
   * @param $alias Alias to use instead of table name
   * @param $join Join condition
   */
  function leftJoin($table, $alias, $join)
  {
    $this->addJoin($table, $alias, $join, 'left');
  }
 
  /** Add a right join condition
   *
   * Helper method to add a right join
   * @see addJoin()
   * @param $table Name of table (without prefix)
   * @param $alias Alias to use instead of table name
   * @param $join Join condition
   */
  function rightJoin($table, $alias, $join)
  {
    $this->addJoin($table, $alias, $join, 'right');
  }

  /** Add an inner join condition
   *
   * Helper method to add an inner join
   * @see addJoin()
   * @param $table Name of table (without prefix)
   * @param $alias Alias to use instead of table name
   * @param $join Join condition
   */
  function innerJoin($table, $alias, $join)
  {
    $this->addJoin($table, $alias, $join, 'inner');
  }

  /** Add an ORDER BY clause
   *
   * Again, only the fieldname is required, and
   * it should include an alias if a table has been added.
   * May be called multiple times.
   *
   * @param	$order	Order by field.
   */
  function addOrder($order)
  {
    if (isset($order))
      $this->addClause('order_by', $order);
  }

  /** Add a GROUP BY clause
   *
   * Only the fieldname is required.
   * May be called multiple times.  Use table aliases as required.
   *
   * @param	$group	Field name to group by.
   */
  function addGroup($group)
  {
    $this->addClause('group_by', $group);
  }

  /** Set a row limit on the query
   *
   * Set a limit on the query.  This is done in a database-independent
   * fashion.
   *
   * @param	$limit	Number of rows to limit.
   * @param	$start	First row to start extraction(row offset).
   */
  function setLimit($limit, $start = 0)
  {
    $this->limit = $limit;
    $this->offset = $start;
  }
	
	/** Set a limit on the query based on pagination.
	 *
	 * @param $page     the current page
	 * @param $pagesize the size of pages
	 */
	function setPageLimit($page, $pagesize = 0)
	{
		if ($page == 0) {
			$page = 1;
		}
		if ($pagesize == 0)
			$pagesize = DP_Config::getConfig('page_size');
			
		$this->setLimit($pagesize, ($page - 1) * $pagesize);
	}

	/** Prepare query for execution
	* @param $clear Boolean, Clear the query after it has been executed
	* @return String containing the SQL statement
	*/
	function prepare($clear = false)
	{
		switch ($this->type) {
			case 'select':
				$q = $this->prepareSelect();
				break;
			case 'update':
				$q = $this->prepareUpdate();
				break;
			case 'insert':
				$q = $this->prepareInsert();
				break;
			case 'replace':
				$q = $this->prepareReplace();
				break;
			case 'delete':
				$q = $this->prepareDelete();
				break;
			case 'create':	// Create a temporary table
				$s = $this->prepareSelect();
				$q = 'CREATE TEMPORARY TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition)) {
					$q .= ' ' . $this->create_definition;
				}
				$q .= ' ' . $s;
				break;
			case 'alter':
				$q = $this->prepareAlter();
				break;
			case 'createPermanent':	// Create a temporary table
				$s = $this->prepareSelect();
				$q = 'CREATE TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition)) {
					$q .= ' ' . $this->create_definition;
				}
				$q .= ' ' . $s;
				break;
			case 'drop':
				$q = 'DROP TABLE IF EXISTS ' . $this->_table_prefix . $this->create_table;
				break;
		}
		if ($clear) {
			$this->clear();
		}
		return $q;
		dprint(__FILE__, __LINE__, 2, $q);
	}

  /** Prepare the SELECT component of the SQL query
  */
  function prepareSelect()
  {
    $q = 'SELECT ';
    if (isset($this->query)) {
      if (is_array($this->query)) {
	$inselect = false;
	$q .= implode(',', $this->query);
      } else {
	$q .= $this->query;
      }
    } else {
      $q .= '*';
    }
    $q .= ' FROM (';
    if (isset($this->table_list)) {
      if (is_array($this->table_list)) {
	$intable = false;
	/* added brackets for MySQL > 5.0.12 compatibility
	** patch #1358907 submitted to sf.net on 2005-11-17 04:12 by ilgiz
	*/
	$q .= '(';
	foreach ($this->table_list as $table_id => $table) {
	  if ($intable)
	    $q .= ",";
	  else
	    $intable = true;
	  $q .= $this->quote_db($this->_table_prefix . $table);
	  if (! is_numeric($table_id))
	    $q .= " as $table_id";
	}
	/* added brackets for MySQL > 5.0.12 compatibility
	** patch #1358907 submitted to sf.net on 2005-11-17 04:12 by ilgiz
	*/
	$q .= ')';
      } else {
	$q .= $this->_table_prefix . $this->table_list;
      }
    $q .= ')';
    } else {
      return false;
    }
    $q .= $this->make_join($this->join);
    $q .= $this->make_where_clause($this->where);
    $q .= $this->make_group_clause($this->group_by);
    $q .= $this->make_order_clause($this->order_by);
		// TODO: Prepare limit as well
    return $q;
  }

  /** Prepare the UPDATE component of the SQL query
   */
  function prepareUpdate()
  {
    // You can only update one table, so we get the table detail
    $q = 'UPDATE ';
    if (isset($this->table_list)) {
      if (is_array($this->table_list)) {
			reset($this->table_list);
	// Grab the first record
	list($key, $table) = each ($this->table_list);
      } else {
	$table = $this->table_list;
      }
    } else {
      return false;
    }
    $q .= $this->quote_db($this->_table_prefix . $table);

    $q .= ' SET ';
    $sets = '';
    $this->_bind_list[] = array();
    foreach( $this->update_list as $field => $value) {
      if ($sets)
        $sets .= ", ";
      $sets .= $this->quote_db($field) . ' = ?';
      $this->_bind_list[] = $value;
    }
    $q .= $sets;
    $q .= $this->make_where_clause($this->where);
    return $q;
  }

  /** Prepare the INSERT component of the SQL query
   */
  function prepareInsert()
  {
    $q = 'INSERT INTO ';
    if (isset($this->table_list)) {
      if (is_array($this->table_list)) {
			reset($this->table_list);
	// Grab the first record
	list($key, $table) = each ($this->table_list);
      } else {
	$table = $this->table_list;
      }
    } else {
      return false;
    }
    $q .= $this->quote_db($this->_table_prefix . $table);

    $fieldlist = '';
    $valuelist = '';
    $this->_bind_list = array();
    foreach( $this->value_list as $field => $value) {
      if ($fieldlist)
	$fieldlist .= ",";
      if ($valuelist)
	$valuelist .= ",";
      $fieldlist .= trim($field);
      $valuelist .= '?';
      $this->_bind_list[] = $value;
    }
    $q .= "($fieldlist) values ($valuelist)";
    return $q;
  }

  /** Prepare the REPLACE component of the SQL query
   */
  function prepareReplace()
  {
    $q = 'REPLACE INTO ';
    if (isset($this->table_list)) {
      if (is_array($this->table_list)) {
			reset($this->table_list);
	// Grab the first record
	list($key, $table) = each ($this->table_list);
      } else {
	$table = $this->table_list;
      }
    } else {
      return false;
    }
    $q .= $this->quote_db($this->_table_prefix . $table);

    $fieldlist = '';
    $valuelist = '';
    $this->_bind_list = array();
    foreach( $this->value_list as $field => $value) {
      if ($fieldlist)
	$fieldlist .= ",";
      if ($valuelist)
	$valuelist .= ",";
      $fieldlist .= trim($field);
      $valuelist .= '?';
      $this->_bind_list[] = $value;
    }
    $q .= "($fieldlist) values ($valuelist)";
    return $q;
  }
 
  /** Prepare the DELETE component of the SQL query
   */
  function prepareDelete()
  {
    $q = 'DELETE FROM ';
    if (isset($this->table_list)) {
      if (is_array($this->table_list)) {
	// Grab the first record
	list($key, $table) = each ($this->table_list);
      } else {
	$table = $this->table_list;
      }
    } else {
      return false;
    }
    $q .= $this->quote_db($this->_table_prefix . $table);
    $q .= $this->make_where_clause($this->where);
    return $q;
  }

  /** Prepare the ALTER component of the SQL query
    * @todo add ALTER DROP/CHANGE/MODIFY/IMPORT/DISCARD/.. definitions: http://dev.mysql.com/doc/mysql/en/alter-table.html
	*/
	function prepareAlter()
	{
		$q = 'ALTER TABLE ' . $this->quote_db($this->_table_prefix . $this->create_table) . ' ';
		if (isset($this->create_definition)) {
		  if (is_array($this->create_definition)) {
		    $first = true;
		    foreach ($this->create_definition as $def) {
		      if ($first)
			$first = false;
		      else
			$q .= ', ';
		      $q .= $def['action'] . ' ' . $def['type'] . ' ' . $def['spec'];
		    }
		  } else {
		    $q .= 'ADD ' . $this->create_definition;
		  }
		}

		return $q; 
	}

	/** Execute the query
	*
	* Execute the query and return a handle.  Supplants the db_exec query
	* @param $style Zend_Db fetch style. Can be FETCH_BOTH, FETCH_NUM or FETCH_ASSOC
	* @param $debug Defaults to false. If true, debug output includes explanation of query
	* @return Handle to the query result
	*/
	function &exec($style = QUERY_STYLE_BOTH, $debug = false)
	{
		if ($this->_query_id) {
			$this->_db->closeConnection();
		}
		$this->_query_id = null;
		try {
			$this->clearQuery();
			$this->_db->setFetchMode($style);
			$this->_fetch_style = $style;
			if ($q = $this->prepare()) {
				error_log('executing query ' . $q);
				dprint(__FILE__, __LINE__, 7, "executing query($q)");
				if (isset($this->limit)) {
					$this->_db->limit($q, $this->limit, $this->offset);
				}
				$this->_query_id =  $this->_db->query($q, $this->_bind_list);
			}
		}
		catch (Exception $e) {
			dprint(__FILE__, __LINE__, 0, "query failed($q)".' - error was: <span style="color:red">' . $e->getMessage() . '</span>');
			$this->_error = $e->getMessage();
		}
		return $this->_query_id;
	}

	/** Fetch the first row of the results
	 * @return First row as array
	 */ 
	function fetchRow()
	{
		if (! $this->_query_id) {
			return false;
		}
		return $this->_query_id->fetch();
	}

	/** Load database results as an array of associative arrays
	 *
	 * Replaces the db_loadList() function
	 * @param $maxrows Maximum number of rows to return
	 * @return Array of associative arrays containing row field values
	 */
	function loadList($maxrows = null)
	{
		if (! $this->exec(QUERY_STYLE_ASSOC)) {
			$this->clear();
			return false;
		}

		return $this->_query_id->fetchAll();
		$this->clear();
	}

	/** Load database results as an associative array, using the supplied field name as the array's keys
	 *
	 * Replaces the db_loadHashList() function
	 * @param $index Defaults to null, the field to use for array keys
	 * @return Associative array of rows, keyed with the field indicated by the $index parameter
	 */
	function loadHashList($index = null)
	{

		if (! $this->exec(QUERY_STYLE_ASSOC)) {
			exit ($this->_error);
		}
		$hashlist = array();
		$keys = null;
		while ($hash = $this->fetchRow()) {
			if ($index) {
				$hashlist[$hash[$index]] = $hash;
			} else {
				// If we are using fetch mode of ASSOC, then we don't
				// have an array index we can use, so we need to get one
				if (! $keys)
					$keys = array_keys($hash);
				$hashlist[$hash[$keys[0]]] = $hash[$keys[1]];
			}
		}
		$this->clear();
		return $hashlist;
	}

	/** Load a single result row as an associative array
	 * @return Associative array of field names to values
	 */
	function loadHash()
	{
		if (! $this->exec(QUERY_STYLE_ASSOC)) {
			exit ($this->_error);
		}
		$hash = $this->fetchRow();
		$this->clear();
		return $hash;
	}
	
	/** Load database results as an associative array
	 * 
	 * @note To devs: is this functionally different to loadHashList() ?
	 * @param $index Field index to use for naming the array keys.
	 * @return Associative array containing result rows
	 */
	function loadArrayList($index = 0)
	{
		if (! $this->exec(QUERY_STYLE_NUM)) {
			exit ($this->_error);
		}
		$this->clear();
		return $this->_query_id->fetchAll(QUERY_STYLE_ASSOC, $index);
	}

	/** Load an indexed array containing the first column of results only
	 * @return Indexed array of first column values
	 */
	function loadColumn()
	{
		if (! $this->exec(QUERY_STYLE_NUM)) {
		  die ($this->_error);
		}
		$result = array();
		while ($row = $this->_query_id->fetchColumn()) {
		  $result[] = $row;
		}
		$this->clear();
		return $result;
	}

    /** Load database results into a CDpObject based object
	 * @param &$object Reference to the object to propagate with database results
	 * @param $bindAll Defaults to false, Bind every field returned to the referenced object
	 * @param $strip Defaults to true
	 * @return True on success.
	 */
	function loadObject( &$object, $bindAll=false , $strip = true)
	{
		if (! $this->exec(QUERY_STYLE_ASSOC)) {
			die ($this->_error);
		}
		if ($object != null) {
			$hash = $this->fetchRow();
			$this->clear();
			if( !$hash ) {
				return false;
			}
			$this->bindHashToObject( $hash, $object, null, $strip, $bindAll );
			return true;
		} else {
			if ($object = $this->_query_id->fetchObject()) {
				$this->clear();
				return true;
			} else {
				$object = null;
				return false;
			}
		}
	}
	
	/** Bind a hash to an object
	 *
	 * Takes the hash/associative array specified by $hash and turns the fields into instance properties of $obj
	 * @param $hash The hash to bind
	 * @param &$obj A reference to the object to bind the hash to
	 * @param $prefix Defaults to null, prefix to use with hash keys
	 * @param $checkSlashes Defaults to true, strip any slashes from the hash values
	 * @param $bindAll Bind all values regardless of their existance as defined instance variables
	 */
	function bindHashToObject( $hash, &$obj, $prefix=null, $checkSlashes=true, $bindAll=false )
	{
		is_array( $hash ) or die( "bindHashToObject : hash expected" );
		is_object( $obj ) or die( "bindHashToObject : object expected" );
	
		if ($bindAll) {
			foreach ($hash as $k => $v) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		} else if ($prefix) {
			foreach (get_object_vars($obj) as $k => $v) {
				if (isset($hash[$prefix . $k ])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
				}
			}
		} else {
			foreach (get_object_vars($obj) as $k => $v) {
				if (isset($hash[$k])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
				}
			}
		}
	}

	/**
	 * inserts an Object into the database.
	 */
	public function insertObject( $table, &$object, $keyName = null, $verbose = false)
	{
		$this->clear();
		$this->addTable($table);
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v == null) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$this->addInsert($k, $v);
			$insert_list[] = $k;
			$values_list[] = $v;
		}
		$change = '"' . implode('","', $insert_list) . '"="' . implode('","', $values_list) . '"';
		if (!$this->exec()) {
			return false;
		}
		$id = $this->lastInsertId();
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return $change;
	}

	public function updateObject($table, &$object, $keyName, $updateNulls=true)
	{
		$this->clear();
		$this->addTable($table);
		$this->addWhere($keyName . '=' . $this->quote($object->$keyName));
		list($old_obj) = $this->loadList();

		$update_list = array();
		$values_list = array();
		foreach($old_obj as $field => $value) {
			if ($object->$field != $value && ($object->$field !== null || $updateNulls)) {
				$update_list[] = $field;
				$values_list[] = $object->$field;
			}
		}

		if (count($update_list)) {
			$change = '"' . implode('","', $update_list) . '"="' . implode('","', $values_list) . '"';
			// addHistory($table, $object->$keyName, 'modify', $change, 0);
			$q->addUpdate($update_list, $values_list, true);
			$q->addWhere($keyName . '=' . $this->quote($object->$keyName));
			$q->addTable($table);
			$ret = $q->exec();
			if ($ret) {
				return $change;
			} else {
				return $ret;
			}
		} else {
			return true;
		}
	}



  // {{{2 function loadResult
  /** Load a single column result from a single row
   * @return Value of the row column
   */
  function loadResult()
  {
    $result = false;

    if (! $this->exec(QUERY_STYLE_NUM)) {
      $GLOBALS['error_message'] = array($this->_error, UI_MSG_ERROR);
    } else if ($data = $this->fetchRow()) {
      $result =  $data[0];
    }
    $this->clear();
    return $result;
  }
  //2}}}

	public function error()
	{
		return $this->_error;
	}

  // {{{2 function make_where_clause
 
  /** Create a where clause based upon supplied field.
   *
   * @param	$where_clause Either string or array of subclauses.
   * @return SQL WHERE clause as a string.
   */
  function make_where_clause($where_clause)
  {
    $result = '';
    if (! isset($where_clause))
      return $result;
    if (is_array($where_clause)) {
      if (count($where_clause)) {
	$started = false;
	$result = ' WHERE ' . implode(' AND ', $where_clause);
      }
    } else if (strlen($where_clause) > 0) {
      $result = " where $where_clause";
    }
    return $result;
  }
  //2}}}

  // {{{2 function make_order_clause
  /** Create an order by clause based upon supplied field.
   *
   * @param	$order_clause	Either string or array of subclauses.
   * @return SQL ORDER BY clause as a string.
   */
  function make_order_clause($order_clause)
  {
    $result = "";
    if (! isset($order_clause))
      return $result;

    if (is_array($order_clause)) {
      $started = false;
      $result = ' ORDER BY ' . implode(',', $order_clause);
    } else if (strlen($order_clause) > 0) {
      $result = " ORDER BY $order_clause";
    }
    return $result;
  }
  //2}}}

  //{{{2 function make_group_clause
  /** Create a group by clause based upon supplied field.
   *
   * @param	$group_clause	Either string or array of subclauses.
   * @return SQL GROUP BY clause as a string.
   */	
  function make_group_clause($group_clause)
  {
    $result = "";
    if (! isset($group_clause))
      return $result;

    if (is_array($group_clause)) {
      $started = false;
      $result = ' GROUP BY ' . implode(',', $group_clause);
    } else if (strlen($group_clause) > 0) {
      $result = " GROUP BY $group_clause";
    }
    return $result;
  }
  //2}}}

  //{{{2 function make_join
  /** Create a join condition based upon supplied fields.
   *
   * @param	$join_clause	Either string or array of subclauses.
   * @return SQL JOIN condition as a string.
   */
  function make_join($join_clause)
  {
    $result = "";
    if (! isset($join_clause))
      return $result;
    if (is_array($join_clause)) {
      foreach ($join_clause as $join) {
	$result .= ' ' . strtoupper($join['type']) . ' JOIN ' . $this->quote_db($this->_table_prefix . $join['table']);
	if ($join['alias'])
	  $result .= ' AS ' . $join['alias'];
	if (is_array($join['condition'])) {
	  $result .= ' USING (' . implode(',', $join['condition']) . ')';
	} else {
	  $result .= ' ON ' . $join['condition'];
	}
      }
    } else {
      $result .= ' LEFT JOIN ' . $this->quote_db($this->_table_prefix . $join_clause);
    }
    return $result;
  }
  //2}}}
  /** Add quotes to a string
   *
   * @param	$string	A string to add quotes to.
   * @return The quoted string
   */
	function quote($string)
	{
		if (is_int($string)) {
			return $string;
		} else {
			$result = $this->_db->quote($string);
			return $result; // $this->_db->quote($string);
		}
	}
	
   /** Add quotes to a database identifier
    * @param $string The identifier to quote
    * @return The quoted identifier
    */
	function quote_db($string)
	{
		return $this->_db->quoteIdentifier($string); 
	}

	public function lastInsertId()
	{
		return $this->_db->lastInsertId();
	}
}
//1}}}

// vim600: fdm=marker sw=8 ts=8 ai:
?>
