<?php

/**
 * EditController
 * 
 * @author
 * @version 
 */


class Projects_EditController extends DP_Controller_Action_Edit {
	
	/**
	 * Build the projects edit form.
	 * 
	 * Adding or changing anything programmatically means that the form must be
	 * accessible via instantiated object or method of this controller. Otherwise the programmatical
	 * changes have to be replicated in each method
	 * 
	 * @return Zend_Form instance.
	 */
	public function getForm() {
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
		
		// @todo Replace with ajax calls to company autocompleter
		// TEMP COMPANY LIST
		$this->_helper->ModelIncluder('companies');
		
		$companies = new Companies_Index();
		$limit = new DP_Pager();
		$limit->setItemsPerPage(10);
		$companies->addModifier($limit);
		$companies->clientWillRender();
		$ca = $companies->getArray();
		$cs = Array();
		foreach ($ca as $c) {
			$cs[$c['company_id']] = $c['company_name'];
		}
		
		$project_company_element = $edit_form->getElement('project_company');
		$project_company_element->addMultiOptions($cs);
		
		return $edit_form;
	}
	
	
	public function newAction() {
		$this->_helper->viewRenderer('object');
		
		$this->view->heading = "New project";
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/projects', 'projects');	
		$title_block->addCrumb('/projects/edit/new', 'new project');
		
		$edit_form = $this->getForm();
		$this->view->form = $edit_form;		
	}

	
	/**
	 * Edit an existing object (or newly created one)
	 */
	public function objectAction() {
		Zend_Loader::registerAutoload();

		$obj_hash = $this->loadObject($this->getRequest()->id);
		
		$this->view->heading = $obj_hash['project_name'];
		
		$edit_form = $this->getForm();
		$edit_form->setDefaults($obj_hash);
		$this->view->form = $edit_form;

		if (!$this->view->titleblock) {
			//$title_block = $this->_helper->TitleBlock('Edit Contact', '/img/_icons/companies/handshake.png');
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/projects', 'projects');

			$title_block->addCrumb('/projects/view/object/id/'.$obj_hash['project_id'], $obj_hash['project_name']);	
			$title_block->addCrumb('/projects/edit/object/id/'.$obj_hash['project_id'], 'edit');
		}		
	}
	
	// Save the project
	public function saveAction() {
		$this->_helper->viewRenderer('object');
		
		$edit_form = $this->getForm();	
		
		if (!$edit_form->isValid($_POST)) {
			$this->view->form = $edit_form;
		} else {
			$this->_helper->viewRenderer->setNoRender();
			
			$this->saveObject($edit_form, 'project_id');
					
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'projects');
            $this->_redirector->redirectAndExit();   		
		}
	}
}
