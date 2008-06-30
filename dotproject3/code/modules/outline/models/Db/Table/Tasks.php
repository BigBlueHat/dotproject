<?php
/**
 * Tasks table gateway.
 *
 * @package dotproject
 * @subpackage tasks
 * @version 3.0 alpha
 */
class Db_Table_Tasks extends Zend_Db_Table
{
	protected $_name = 'tasks';
	protected $_primary = 'task_id';
}
?>