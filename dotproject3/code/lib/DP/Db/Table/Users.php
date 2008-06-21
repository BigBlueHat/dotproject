<?php
/**
 * Table gateway for users table.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Db_Table_Users extends Zend_Db_Table {
	protected $_name = 'users';
	protected $_primary = 'user_id';
}
?>