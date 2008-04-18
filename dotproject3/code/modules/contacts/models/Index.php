<?php
class Contacts_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();
		
		$showfields = array(
			'contact_company' => 'contact_company',
			'company_name' 		=> 'company_name',
			'contact_phone' 	=> 'contact_phone',
			'contact_email' 	=> 'contact_email'
		);
		
		// Create query to generate list data
		// assemble the sql statement
		
		$q = new DP_Query;
		$q->addQuery('contact_id, contact_order_by');
		$q->addQuery($showfields);
		$q->addQuery('contact_first_name, contact_last_name, contact_phone');
		$q->addTable('contacts', 'a');
		$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
		
		// TODO - filtering of private entries
		/*
		$q->addWhere("
			(contact_private=0
				OR (contact_private=1 AND contact_owner=$AppUI->user_id)
				OR contact_owner IS NULL OR contact_owner = 0
			)"); */

		$q->addOrder('contact_order_by');		
		
		$this->query = $q;
		
		$cq = new DP_Query;
		$cq->addQuery('count(*)');
		$cq->addTable('contacts', 'a');
		$cq->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
		
		// TODO - filtering of private entries
		/*
		$q->addWhere("
			(contact_private=0
				OR (contact_private=1 AND contact_owner=$AppUI->user_id)
				OR contact_owner IS NULL OR contact_owner = 0
			)"); */			
		
		$this->cq = $cq;
	}
	
	
	
	
	public function count() {
		$full_list = $this->cq->loadList();
		return count($full_list);
	}
}
?>