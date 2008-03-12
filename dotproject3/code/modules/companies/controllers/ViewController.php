<?php
require_once 'DP/Controller/Action.php';

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
		$company_id = $this->getRequest()->getParam('company_id');
		$obj =& $this->moduleClass();
		$obj->load($company_id);
		$tpl = $this->getView();
		$tpl->assign('obj', $obj);
		$company_types = DP_Config::getSysVal('CompanyType');
		$tpl->assign('type', $company_types[$obj->company_type]);
	}

	public function departmentAction()
	{
		throw new Exception('Not implemented');
	}
}
?>
