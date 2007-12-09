<?php
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Db.php';
require_once 'Zend/Db/Exception.php';
require_once 'DP/Session.php';

/**
 * A container for the configuration requirements, needed here to
 * set up the database connection before AppUI or other classes are
 * instantiated.  In order to make sure we can be used in the widest
 * possible circumstances we use the singleton style.
 */

class DP_Config
{
	private $config = null;
	private static $_instance = null;
	private static $base_config = null;
	public static $db = null;

	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct()
	{
	}

	public function __destruct()
	{
		self::$_instance = null;
		self::$db = null;
	}

	public static function getDB()
	{
		if (null === self::$db) {
			foreach (array(PHP_SYSCONFDIR, PHP_CONFIG_FILE_PATH, PHP_CONFIG_FILE_SCAN_DIR, dirname(__FILE__).'/../../../config') as $d) {
				if (is_readable($d . '/dotproject.ini')) {
					self::$base_config = new Zend_Config_Ini($d.'/dotproject.ini', null);
					break;
				}
			}
			if (self::$base_config) {
				try {
					self::$db = Zend_Db::factory(self::$base_config->database);
					self::$db->setFetchMode(Zend_Db::FETCH_ASSOC);
				}
				catch(Execption $e) {
					error_log('exception caught in DP_Session::getDB construct');
				}
			} else {
				error_log('no base config');
			}
		}
		return self::$db;
	}

	public static function getConfig($param = null, $default = null)
	{
		return self::getInstance()->_getConfig($param, $default);
	}

	public function _getConfig($param = null, $default = null)
	{
		$this->_initConfig();
		if (isset($param)) {
			return isset($this->config[$param]) ? $this->config[$param] : $default;
		} else {
			return $this->config;
		}
	}

	protected function _initConfig()
	{
		if (null == $this->config) {
			try {
				$db = self::getDB();
				$q = $db->select()->from('config', array('config_name', 'config_value'));
				$this->config = $db->fetchPairs($q);
			}
			catch (Zend_Db_Exception $e) {
				error_log('DB Exception in _initConfig');
			}
		}
	}

	public function __get($var)
	{
		$this->_initConfig();
		return isset($this->config[$var]) ? $this->config[$var] : null;
	}

	public function __set($var, $value)
	{
		$this->_initConfig();
		$this->config[$var] = $value;
	}

	public function __isset($var)
	{
		$this->_initConfig();
		return isset($this->config[$var]);
	}

	public function getSysVal($key)
	{
		$db = self::getDB();
		$q = $db->select()
		  ->from('sysvals', array('sysval_value_id', 'sysval_value'))
		  ->where('sysval_title = ' . $db->quote($key));
		return $db->fetchPairs($q);
	}

}

?>
