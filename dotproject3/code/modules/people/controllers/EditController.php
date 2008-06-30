<?php
/**
 * Edit controller for people
 * 
 * @package dotproject
 * @subpackage people
 * @version 3.0 alpha
 *
 */
class People_EditController extends DP_Controller_Action_Edit {
	
	public function getForm() {
		$form_definition = $this->_helper->FormDefinition('edit-object');
		$edit_form = new Zend_Form($form_definition);
		$userpassword = $edit_form->userpassword;
		$userpassword->addPrefixPath('DP_Validator', 'DP/Validator/', 'validate');
		$userpassword->addValidator('PasswordConfirm', true);
		
		return $edit_form;
	}
	
	/**
	 * Create a new contact.
	 */
	public function newAction() {
		$this->_helper->viewRenderer('object');
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/people', 'people');	
		$title_block->addCrumb('/people/edit/new', 'new person');
		$this->view->heading = "New person"; // @todo translate

		$edit_form = $this->getForm();
		$this->view->form = $edit_form;
	}
	
	/**
	 * Edit an existing object (or newly created one)
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();
		
		$id = $this->getRequest()->id;

		$obj_hash = $this->loadObject($this->getRequest()->id);
		
		$this->view->heading = $obj_hash['displayname'];
		
		$edit_form = $this->getForm();
		$edit_form->setDefaults($obj_hash);
		$this->view->form = $edit_form;
		
		if (!$this->view->titleblock) {
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/people', 'People');

			$title_block->addCrumb('/people/view/object/id/'.$id, $obj_hash['displayname']);	
			$title_block->addCrumb('/people/edit/object/id/'.$id, 'Edit');
		}	
	}
	
	public function saveAction() {
		$this->_helper->viewRenderer('object');

		$edit_form = $this->getForm();
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;	
			$values = $edit_form->getValues();
			
			if (!$this->view->titleblock) {
				$id = $values['id'];
				$title_block = $this->_helper->TitleBlock('');
				$title_block->addCrumb('/people', 'People');
	
				$title_block->addCrumb('/people/view/object/id/'.$id, $values['displayname']);	
				$title_block->addCrumb('/people/edit/object/id/'.$id, 'Edit');
				
				$this->view->heading = $values['displayname'];
			}
		} else {
			$this->_helper->viewRenderer->setNoRender();
			
			$edit_form->userpassword->setValue(md5($edit_form->userpassword->getValue()));
			$this->saveObject($edit_form, 'id');
			
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'people');
            $this->_redirector->redirectAndExit();    
		}
	}
	
}
?>