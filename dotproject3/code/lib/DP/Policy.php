<?php
/**
 * Policy handler class for providing policy-based permissions
 *
 * @package dotproject
 * @subpackage system
 */

class DP_Policy extends Zend_Db_Table
{
	/**
	 * Constants related to access levels.
	 */
	const ACCESS_VIEW = 1;
	const ACCESS_EDIT = 2;
	const ACCESS_CHILD = 4;

	/**
	 * Constants related to access types
	 */
	const GROUP_MEMBER = 'Member';
	const GROUP_NON_MEMBER = 'Non-Member';
	const GROUP_OWNER = 'Owner';

	protected $_name = 'policies';
	protected $_primary = array('policy_resource', 'policy_type');
	protected $permissiveness = 'MIN';

	private $_instance = null;

	private $_policies = null;

	public function __construct()
	{
		parent::__construct();
		$this->permissiveness = DP_Config::getConfig('permissiveness', 'MIN');
	}

	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function getPolicy($table)
	{
		$policy = self::getInstance()->loadPolicy($table);
		return $policy;
	}

	public function loadPolicy($table)
	{
		if (empty($this->_policies)) {
			$this->_policies = array();
			$rowset = $this->fetchAll();
			foreach ($rowset as $row) {
				$this->_policies[$row['policy_resource']][$row['policy_type']] = $row['policy_view'] + $row['policy_edit'] + $row['policy_child'];
			}
		}
		if ($table) {
			return $this->_policies[$table];
		}
	}

	public function canView($table, $type = DP_Policy::GROUP_MEMBER)
	{
		$this->loadPolicy();
		return ($this->_policies[$table][$type] & DP_Policy::ACCESS_VIEW) == DP_Policy::ACCESS_VIEW;
	}

	public function canEdit($table, $type = DP_Policy::GROUP_MEMBER)
	{
		$this->loadPolicy();
		return ($this->_policies[$table][$type] & DP_Policy::ACCESS_EDIT) == DP_Policy::ACCESS_EDIT;
	}

	public function canAddChild($table, $type = DP_Policy::GROUP_MEMBER)
	{
		$this->loadPolicy():
		return ($this->_policies[$table][$type] & DP_Policy::ACCESS_CHILD) == DP_Policy::ACCESS_CHILD;
	}

	public function hasPermission($table, $type = DP_Policy::GROUP_MEMBER, $level = DP_Policy::ACCESS_VIEW)
	{
		$this->loadPolicy();
		return ($this->_policies[$table][$type] & $level) == $level;
	}

}
?>
