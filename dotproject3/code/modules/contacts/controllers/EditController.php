<?php
/**
 * Edit controller for contacts
 * 
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 *
 */
class Contacts_EditController extends Zend_Controller_Action {
	
	/**
	 * Create a new contact.
	 */
	public function newAction() {
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/contacts', 'contacts');	
		$title_block->addCrumb('/contacts/edit/new', 'new contact');
		$this->_helper->actionStack('object', 'edit', 'contacts');		
	}
	
	/**
	 * Edit an existing object (or newly created one)
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();
		
		$contact_id = $this->getRequest()->id;


		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		
		
		// There dont seem to be any contact types?
		$types = DP_Config::getSysVal( 'ContactType' );
		$contact_type_element = $edit_form->getElement('contact_type');
		$contact_type_element->addMultiOptions($types);
		
		if ($contact_id) {
			// TODO - set default adapter pre dispatch
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);

			$contact_rows = Contact::find($contact_id);
			$row = $contact_rows->current();
			$rowhash = $row->toArray();
			$edit_form->setDefaults($rowhash);

			if (!$this->view->contact_first_name) {
				$this->view->contact_first_name = $rowhash['contact_first_name'];
				$this->view->contact_last_name = $rowhash['contact_last_name'];
			}
			
			if (!$this->view->titleblock) {
				//$title_block = $this->_helper->TitleBlock('Edit Contact', '/img/_icons/companies/handshake.png');
				$title_block = $this->_helper->TitleBlock('');
				$title_block->addCrumb('/contacts', 'contacts');
				$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $rowhash['contact_last_name'].', '.$rowhash['contact_first_name']);
				$title_block->addCrumb('/contacts/edit/object/id/'.$contact_id, 'edit');
			}
				
		} else {
			// no id supplied, error
		}
		
		$this->view->form = $edit_form;		
	}
	
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
			$contact = Contact::bind($_POST);
			
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			if ($contact->contact_id) {
				$contact->update();
			} else {
				$contact->insert();
			}
			
			// Display a nice message which confirms the save, and views the object
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'contacts');
            $this->_redirector->redirectAndExit();             
		}
	}
	
}
?>