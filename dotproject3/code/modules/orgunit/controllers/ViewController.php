<?php
class Orgunit_ViewController extends Zend_Controller_Action
{
	public function getForm()
	{
		$form_definition = $this->_helper->FormDefinition('view-object');
		
		$edit_form = new Zend_Form($form_definition);
		$elements = $edit_form->getElements();
		foreach ($elements as $em) {
			if ($em instanceof Zend_Form_Element_Text) {
				$em->helper = 'viewText';
			}
			if ($em instanceof Zend_Form_Element_Textarea) {
				$em->helper = 'viewText';
			}
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
			
			$tbl = new Db_Table_Orgunits();
			$rows = $tbl->find($obj_id);
			$obj = $rows->current();
			
			$view_form = $this->getForm();
			$view_form->setDefaults($obj->toArray());
			
			$this->view->heading = $obj->name;
			$this->view->obj = $view_form;

			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/orgunit', 'Organizational Units');
			$title_block->addCrumb('/orgunit/view/object/id/'.$obj_id, $obj->name);		
		}
		else
		{
			// invalid id
		}
	}
}
?>