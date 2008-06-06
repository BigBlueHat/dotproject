<?php
class Contacts_ViewController extends DP_Controller_Action
{
	public function objectAction()
	{
		Zend_Loader::registerAutoload();

		
		$contact_id = $this->getRequest()->id;
		if ($contact_id) {
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$contacts = new Db_Table_Contacts();
			$rows = $contacts->find($contact_id);			
		}
		
		if ($contact_id && (count($rows) != 0)) {
			$this->_helper->RequireModel('Db/Table/Companies', 'companies');
			
			$obj = $rows->current();
			$this->view->obj = $obj;
			
			$companies = new Db_Table_Companies();
			$rows = $companies->find($obj->contact_company);
			$parent_obj = $rows->current();
			
			$this->view->parent_obj = $parent_obj;
			
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/contacts', 'contacts');
			
			if ($obj->contact_order_by != '') {
					$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj->contact_order_by);	
			} else {
					$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj->contact_last_name.', '.$obj->contact_first_name);
			}
		}
		else
		{
			// invalid id
			// set a message (not found)
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setGoto('index', 'index', 'contacts');
            $this->_redirector->redirectAndExit();
		}
	}
}
?>