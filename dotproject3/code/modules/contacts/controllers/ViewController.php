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
			
			$contact_rows = Contact::find($contact_id);
			$obj = Contact::load($contact_rows);
			
			$this->view->obj = $obj;
			
			//$types = DP_Config::getSysVal( 'CompanyType' );
			//$this->view->company_type = $types[$obj->company_type];
			//$title_block = $this->_helper->TitleBlock($obj->company_name, '/img/_icons/companies/handshake.png');
			$title_block = $this->_helper->TitleBlock('');
			$title_block->addCrumb('/contacts', 'contacts');
			$title_block->addCrumb('/contacts/view/object/id/'.$contact_id, $obj->contact_last_name.', '.$obj->contact_first_name);		
			// TODO - Check delete permission
		}
		else
		{
			// invalid id
		}
	}
}
?>