<?php
/**
 * Controller for company object editing functions.
 * 
 * @package dotproject
 * @subpackage companies
 * @version 3.0 alpha
 *
 */
class Companies_EditController extends DP_Controller_Action_Edit {
    
	public function getForm() 
	{
		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		$types = DP_Config::getSysVal( 'CompanyType' );
		$company_type_element = $edit_form->getElement('company_type');
		$company_type_element->addMultiOptions($types);

		return $edit_form;
	}
	/**
	 * Create a new company object
	 */
	public function newAction() {
		$this->_helper->viewRenderer('object');
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/companies', 'companies');	
		$title_block->addCrumb('/companies/edit/new', 'new company');
		$this->view->heading = 'New company';
		
		$edit_form = $this->getForm();
		$this->view->form = $edit_form;
	}
	
	/**
	 * Modify an existing company object
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();
		
		$company_id = $this->getRequest()->id;

		$obj_hash = $this->loadObject($company_id);
		
		$this->view->heading = $obj_hash['company_name'];
		
		$edit_form = $this->getForm();
		$edit_form->setDefaults($obj_hash);
		
		$this->view->form = $edit_form;

		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/companies', 'companies');
		$title_block->addCrumb('/companies/view/object/id/'.$company_id, $obj_hash['company_name']);
		$title_block->addCrumb('/companies/edit/object/id/'.$company_id, 'edit');
	}
	
	/**
	 * Save a company object
	 */
	public function saveAction() {
		$this->_helper->viewRenderer('object');
		Zend_Loader::registerAutoload();
		
		$edit_form = $this->getForm();
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;		
		} else {
			$this->_helper->viewRenderer->setNoRender();

			$this->saveObject($edit_form, 'company_id');
			
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'companies');
            $this->_redirector->redirectAndExit();             
		}
	}
}
?>