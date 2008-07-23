<?php
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
class Companies_ViewController extends Zend_Controller_Action
{
	public function objectAction()
	{
		$company_id = $this->getRequest()->id;
		
		if ($company_id) {
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$companies = new Db_Table_Companies();
			$rows = $companies->find($company_id);
			$obj = $rows->current();
			
			$related_tab_view = DP_View_Factory::getTabBoxView('company_related_children');	
			$related_tab_view->setUrlPrefix($this_url);

			$child_list = DP_Related::findChildren($obj);
			
			foreach ($child_list as $child) {
				$child_view = DP_Related::factory($obj, $child);
				$related_tab_view->add($child_view, $child->title);
			}
			
			$related_tab_view->updateStateFromServer($this->getRequest());
			
			$this->view->obj = $obj;
			$this->view->related = $related_tab_view;
			
			$types = DP_Config::getSysVal( 'CompanyType' );
			$this->view->company_type = $types[$obj->company_type];
			//$title_block = $this->_helper->TitleBlock($obj->company_name, '/img/_icons/companies/handshake.png');
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/companies', 'companies');
			$title_block->addCrumb('/companies/view/object/id/'.$company_id, $obj->company_name);		
			// TODO - Check delete permission
			$title_block->addCrumbDelete('delete company');
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
