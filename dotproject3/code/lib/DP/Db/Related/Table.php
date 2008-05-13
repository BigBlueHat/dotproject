<?php
/**
 * Table gateway for relationships table.
 * 
 * The relationships table describes relationships between
 * objects in the system. The system uses this table to determine
 * relevant information to display.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Db_Related_Table extends Zend_Db_Table {
	protected $_name = 'relationships';
	protected $_primary = 'id';
}
?>