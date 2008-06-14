<?php
/**
 * Edit controller for contacts
 * 
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 *
 */
class Contacts_EditController extends DP_Controller_Action_Edit {
	
	public function getForm() {
		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);

		// There dont seem to be any contact types?
		$types = DP_Config::getSysVal( 'ContactType' );
		$contact_type_element = $edit_form->getElement('contact_type');
		$contact_type_element->addMultiOptions($types);

		return $edit_form;
	}
	
	/**
	 * Create a new contact.
	 */
	public function newAction() {
		$this->_helper->viewRenderer('object');
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/contacts', 'contacts');	
		$title_block->addCrumb('/contacts/edit/new', 'new contact');
		$this->view->heading = "New contact"; // @todo translate

		$edit_form = $this->getForm();
		$this->view->form = $edit_form;
	}
	
	/**
	 * Edit an existing object (or newly created one)
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();
		
		$contact_id = $this->getRequest()->id;

		$obj_hash = $this->loadObject($this->getRequest()->id);
		
		$this->view->heading = ($obj_hash['contact_order_by'] == '') ? $obj_hash['contact_last_name'].', '.$obj_hash['contact_first_name'] : $obj_hash['contact_order_by'];
		
		$edit_form = $this->getForm();
		$edit_form->setDefaults($obj_hash);
		$this->view->form = $edit_form;
		
		if (!$this->view->titleblock) {
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/contacts', 'contacts');

			if ($obj_hash['contact_order_by'] != '') {
				$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj_hash['contact_order_by']);	
			} else {
				$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj_hash['contact_last_name'].', '.$obj_hash['contact_first_name']);
			}
			
			$title_block->addCrumb('/contacts/edit/object/id/'.$contact_id, 'edit');
		}	
	}
	
	public function saveAction() {
		$this->_helper->viewRenderer('object');

		$edit_form = $this->getForm();
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;		
		} else {
			$this->_helper->viewRenderer->setNoRender();
			
			$this->saveObject($edit_form, 'contact_id');
			
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'contacts');
            $this->_redirector->redirectAndExit();    
		}
	}
	
}
?>