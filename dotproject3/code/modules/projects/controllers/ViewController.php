<?php
class Projects_ViewController extends Zend_Controller_Action
{
	public function getForm()
	{
		$form_definition = $this->_helper->FormDefinition('edit-object');
		
		$edit_form = new Zend_Form($form_definition);
		//$edit_form->addPrefixPath('DP_Form_Decorator','DP/Form/Decorator','decorator');
		$elements = $edit_form->getElements();
		foreach ($elements as $em) {
			$em->helper = 'viewText';
		}
		
		
		
		return $edit_form;
	}
	
	public function objectAction()
	{
		$obj_id = $this->getRequest()->id;
		$this->view->addHelperPath('DP/View/Helper','DP_View_Helper_');
		
		if ($obj_id) {
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$projects = new Db_Table_Projects();
			$rows = $projects->find($obj_id);
			$obj = $rows->current();
			
			$view_form = $this->getForm();
			$view_form->setDefaults($obj->toArray());
			/*
			$related_tab_view = DP_View_Factory::getTabBoxView('project_related_children');	
			$related_tab_view->setUrlPrefix($this_url);

			$child_list = DP_Related::findChildren($obj);
			
			foreach ($child_list as $child) {
				$child_view = DP_Related::factory($obj, $child);
				$related_tab_view->add($child_view, $child->title);
			}
			
			$related_tab_view->updateStateFromServer($this->getRequest());
			*/
			$this->view->obj = $view_form;
			//$this->view->related = $related_tab_view;
			
			//$types = DP_Config::getSysVal( 'CompanyType' );
			//$this->view->company_type = $types[$obj->company_type];
			//$title_block = $this->_helper->TitleBlock($obj->company_name, '/img/_icons/companies/handshake.png');
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/projects', 'projects');
			$title_block->addCrumb('/projects/view/object/id/'.$obj_id, $obj->project_name);		
		}
		else
		{
			// invalid id
		}
	}
}
?>
