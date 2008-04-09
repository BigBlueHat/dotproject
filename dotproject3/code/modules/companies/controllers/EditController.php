<?php
// TODO - helpers for loading model classes
require_once DP_BASE_CODE . '/modules/companies/models/Company.php';
require_once DP_BASE_CODE . '/modules/companies/models/Companies_Table.php';

/**
 * Controller for company object editing functions.
 * 
 * @package dotproject
 * @subpackage companies
 * @version 3.0 alpha
 *
 */
class Companies_EditController extends DP_Controller_Action {
	
	/**
	 * Create a new company object
	 */
	public function newAction() {
		$title_block = DP_View_Factory::getTitleBlockView('dp-companies-new-tb', 'New Company', '/img/_icons/companies/handshake.png', $m, "$m.$a" );
		$title_block->addCrumb('/companies', 'companies list');	
		$this->view->titleblock = $title_block;
		
		$this->_helper->actionStack('object', 'edit', 'companies');
	}
	
	/**
	 * Modify an existing company object
	 */
	public function objectAction() {
		$company_id = $this->getRequest()->id;

		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		if ($company_id) {
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$company_rows = Company::find($company_id);
			$row = $company_rows->current();
			$edit_form->setDefaults($row->toArray());	
		}
		
		$this->view->form = $edit_form;

		if (!$this->view->titleblock) {
			$title_block = DP_View_Factory::getTitleBlockView('dp-companies-edit-tb', 'Edit Company', '/img/_icons/companies/handshake.png', $m, "$m.$a" );
			$title_block->addCrumb('/companies', 'companies list');
			$title_block->addCrumb('/companies/view/object/id/'.$company_id, 'view this company');
			$this->view->titleblock = $title_block;
		}	
	}
	
	/**
	 * Save a company object
	 */
	public function saveAction() {
		$this->_helper->viewRenderer('object');
		
		// Retrieve the form definition from views/forms
		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;		
		} else {
			// form is ok
			$company = Company::bind($_POST);
			
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			if ($company->company_id) {
				$company->update();
			} else {
				$company->insert();
			}
		}
	}
}
?>