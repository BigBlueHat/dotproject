<?php
class People_ViewController extends Zend_Controller_Action
{
	public function getForm()
	{
		$form_definition = $this->_helper->FormDefinition('view-object');
		
		$edit_form = new Zend_Form($form_definition);
		//$edit_form->addPrefixPath('DP_Form_Decorator','DP/Form/Decorator','decorator');
		$elements = $edit_form->getElements();
		foreach ($elements as $em) {
			if ($em instanceof Zend_Form_Element_Text) {
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
			
			$tbl = new DP_Db_Table_People();
			$rows = $tbl->find($obj_id);
			$obj = $rows->current();
			
			$view_form = $this->getForm();
			$view_form->setDefaults($obj->toArray());
			
			$this->view->heading = $obj->displayname;
			$this->view->obj = $view_form;

			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/people', 'People');
			$title_block->addCrumb('/people/view/object/id/'.$obj_id, $obj->displayname);		
		}
		else
		{
			// invalid id
		}
	}
}
?>
