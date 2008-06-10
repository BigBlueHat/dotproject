<?php

/**
 * EditController
 * 
 * @author
 * @version 
 */


class Projects_EditController extends DP_Controller_Action {
	
	public function newAction() {
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/projects', 'projects');	
		$title_block->addCrumb('/projects/edit/new', 'new project');
		$this->_helper->actionStack('object', 'edit', 'projects');		
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
		$types = DP_Config::getSysVal( 'ProjectType' );
		$project_type_element = $edit_form->getElement('project_type');
		$project_type_element->addMultiOptions($types);
		
		$status = DP_Config::getSysVal( 'ProjectStatus' );
		$project_status_element = $edit_form->getElement('project_status');
		$project_status_element->addMultiOptions($status);
		
		$priorities = DP_Config::getSysVal( 'ProjectPriority' );
		$project_priority_element = $edit_form->getElement('project_priority');
		$project_priority_element->addMultiOptions($priorities);
		
		/*
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
		*/
		
		$this->view->form = $edit_form;		
	}
}
