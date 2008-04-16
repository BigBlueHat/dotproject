<?php
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
		//$title_block = $this->_helper->TitleBlock('New Company', '/img/_icons/companies/handshake.png');
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/companies', 'companies');	
		$title_block->addCrumb('/companies/edit/new', 'new company');
		$this->view->company_name = 'New company';
		$this->_helper->actionStack('object', 'edit', 'companies');
	}
	
	/**
	 * Modify an existing company object
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();
		
		$company_id = $this->getRequest()->id;


		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		$types = DP_Config::getSysVal( 'CompanyType' );
		$company_type_element = $edit_form->getElement('company_type');
		$company_type_element->addMultiOptions($types);
		
		if ($company_id) {
			// TODO - set default adapter pre dispatch
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);

			$company_rows = Company::find($company_id);
			$row = $company_rows->current();
			$rowhash = $row->toArray();
			$edit_form->setDefaults($rowhash);

			if (!$this->view->company_name) {
				$this->view->company_name = $rowhash['company_name'];
			}
			
			if (!$this->view->titleblock) {
				//$title_block = $this->_helper->TitleBlock('Edit Company', '/img/_icons/companies/handshake.png');
				$title_block = $this->_helper->TitleBlock('');
				$title_block->addCrumb('/companies', 'companies');
				$title_block->addCrumb('/companies/view/object/id/'.$company_id, $rowhash['company_name']);
				$title_block->addCrumb('/companies/edit/object/id/'.$company_id, 'edit');
			}
				
		} else {
			// no id supplied, error
		}
		
		$this->view->form = $edit_form;
	}
	
	/**
	 * Save a company object
	 */
	public function saveAction() {
		$this->_helper->viewRenderer('object');
		Zend_Loader::registerAutoload();
		
		// Retrieve the form definition from views/forms
		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;		
		} else {
			// form is ok
			$this->_helper->viewRenderer->setNoRender();
			$company = Company::bind($_POST);
			
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			if ($company->company_id) {
				$company->update();
			} else {
				$company->insert();
			}
			
			// Display a nice message which confirms the save, and views the object
			//$this->_helper->actionStack('index', 'index', 'companies');
		}
	}
}
?>