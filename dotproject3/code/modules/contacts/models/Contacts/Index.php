<?php
class Contacts_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();
		
		$db = DP_Config::getDB();
		$select = $db->select();
		
		$select->from('contacts');
		$select->join(array('c'=>'companies'),
			'contact_company = c.company_id',
			array('company_id', 'company_name')
		);

		$this->query = $select;
		
		$cq = $db->select();
		$cq->from('contacts',Array('COUNT(*)'));
		$this->cq = $cq;
	}
}
?>