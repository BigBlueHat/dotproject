<?php
require_once 'DP/Controller/Action.php';
require_once DP_BASE_CODE . '/modules/companies/models/Company.php';
require_once DP_BASE_CODE . '/modules/companies/models/Companies_Table.php';
/**
 * This is the file containing the definition of the view controller for the companies module
 * 
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 * @subpackage companies
 */

/**
 * Handles company view related methods.
 * 
 * @package dotproject
 * @subpackage companies
 */
class Companies_ViewController extends DP_Controller_Action
{
	public function indexAction()
	{


	}
	
	public function objectAction()
	{
		$company_id = $this->getRequest()->id;
		
		$title_block = DP_View_Factory::getTitleBlockView('dp-companies-view-tb', 'View Company', '/img/_icons/companies/handshake.png', $m, "$m.$a" );
		$title_block->addCrumb('/companies', 'companies list');
		$title_block->addCrumb('/companies/edit/object/id/'.$company_id, 'edit this company');
		
		//<a href="javascript:delIt()" title=""><img src="./images/icons/stock_delete-16.png"  width="16" height="16" border="0"></a></td><td>&nbsp;<a href="javascript:delIt()" title="">delete company</a>
		$title_block->addCrumbDelete('delete company');
		
		$this->view->titleblock = $title_block;

		
		if ($company_id) {
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$company_rows = Company::find($company_id);
			$obj = Company::load($company_rows);
			
			$this->view->obj = $obj;
		}
		else
		{
			// invalid id
		}
	}

	public function departmentAction()
	{
		throw new Exception('Not implemented');
	}
}
?>
