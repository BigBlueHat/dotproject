<?php /* COMPANIES $Id: companies.class.php 4779 2007-02-21 14:53:28Z cyberhorse $ */

/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 4779 $
*/

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class DP_Module_Companies extends DP_Module_Abstract implements DP_Module_Interface {
/** @var int Primary Key */
	var $company_id = NULL;
/** @var string */
	var $company_name = NULL;

// these next fields should be ported to a generic address book
	var $company_phone1 = NULL;
	var $company_phone2 = NULL;
	var $company_fax = NULL;
	var $company_address1 = NULL;
	var $company_address2 = NULL;
	var $company_city = NULL;
	var $company_state = NULL;
	var $company_zip = NULL;
	var $company_country = NULL;
	var $company_email = NULL;

/** @var string */
	var $company_primary_url = NULL;
/** @var int */
	var $company_owner = NULL;
/** @var string */
	var $company_description = NULL;
/** @var int */
	var $company_type = null;
	
	var $company_custom = null;

	function __construct() {
		parent::__construct( 'companies', 'company_id' );
		$this->search_fields = array('company_name', 
			'company_address1', 'company_address2', 'company_city', 'company_state', 'company_zip', 
			'company_primary_url', 'company_description', 'company_email');
	}

// overload check
	function check() {
		if ($this->company_id === NULL) {
			return 'company id is NULL';
		}
		$this->company_id = intval( $this->company_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		$tables[] = array( 'label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company' );
		$tables[] = array( 'label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company' );
		//FIXME: user_company doesn't exist anymore - what should this be replaced with?
		//$tables[] = array( 'label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company' );
	// call the parent class method to assign the oid
		return parent::canDelete( $msg, $oid, $tables );
	}

	// Interface methods
	public function canInstall() { return false; }
	public function setup() { return false; }
	public function config() { return false; }
	public function remove() { return false; }
	
	// Tab display functions
	public function hasTabs($module = null) 
	{
		if (empty($module) || $module == 'companies') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Report what tabs and what functions support those tabs.
	 */
	public function getTabs($module, $controller = null, $action = null)
	{
		$result = array();
		if ($module == 'companies') {
			$AppUI = DP_AppUI::getInstance();
			switch ($controller) {
				case 'index':
					$result[] = array('module' => 'companies', 'controller' => 'index', 'action' => 'list', 'name' => $AppUI->_('All Companies'), 'args' => array('list' => 0));
					foreach (DP_Config::getSysVal('CompanyType') as $k => $v) {
						$result[] = array(
							'module' => 'companies',
							'controller' => 'index',
							'action' => 'list',
							'name' => $AppUI->_($v),
							'args' => array('list' => $k)
						);
					}
					break;
				case 'view':
					$result[] = array('module' => 'companies', 'controller' => 'view', 'action' => 'deartment', 'name' => $AppUI->_('Departments'));
					break;
				case 'addedit':
				default:
					break;
				
			}
		}
		return $result;
	}
}
?>
