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

			$contacts = new Db_Table_Contacts();
			$rows = $contacts->find($contact_id);
			$obj = $rows->current();
			
			$obj_hash = $obj->toArray();

			$edit_form->setDefaults($obj_hash);

			if (!$this->view->contact_first_name) {
				$this->view->contact_first_name = $obj_hash['contact_first_name'];
				$this->view->contact_last_name = $obj_hash['contact_last_name'];
			}
			
			if (!$this->view->titleblock) {
				//$title_block = $this->_helper->TitleBlock('Edit Contact', '/img/_icons/companies/handshake.png');
				$title_block = $this->_helper->TitleBlock('');
				$title_block->addCrumb('/contacts', 'contacts');
				if ($obj->contact_order_by != '') {
					$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj_hash['contact_order_by']);	
				} else {
					$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj_hash['contact_last_name'].', '.$obj_hash['contact_first_name']);
				}
				
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
			$this->_helper->viewRenderer->setNoRender();
			
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$contacts = new Db_Table_Contacts();
			$contact_id = $_POST['contact_id'];
			
			if ($contact_id != '') {
				$where = $contacts->getAdapter()->quoteInto('contact_id = ?', $contact_id);
				$updated_contact = $contacts->createRow($_POST);
				$contacts->update($updated_contact->toArray(), $where);
			} else {
				$new_contact = $contacts->createRow($_POST);
				// Empty primary key throws an exception, so force null value.
				$new_contact->contact_id = null;
				$new_contact->save();
			}
			
			// Display a nice message which confirms the save, and views the object
			
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'contacts');
            $this->_redirector->redirectAndExit();    
		}
	}
	
}
?>