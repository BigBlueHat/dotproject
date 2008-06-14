<?php
class Companies_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();
		
		// Create query to generate list data
		$q  = new DP_Query;
		$q->addTable('companies', 'c');
		$q->addQuery('c.company_id, c.company_name, c.company_type');
		$q->addQuery('c.company_description');
		$q->addQuery('count(distinct p.project_id) as company_projects_active');
		$q->addQuery('count(distinct p2.project_id) as company_projects_inactive');
		$q->addQuery('con.contact_first_name, con.contact_last_name');
		$q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_status != 7');
		$q->addJoin('users', 'u', 'c.company_owner = u.user_id');
		$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$q->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_status = 7');
		$q->addGroup('c.company_id');

		$this->query = $q;
		
		$cq = new DP_Query;
		$cq->addTable('companies', 'c');
		$cq->addQuery('count(*)');
		$cq->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_status != 7');
		$cq->addJoin('users', 'u', 'c.company_owner = u.user_id');
		$cq->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$cq->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_status = 7');
		$cq->addGroup('c.company_id');
		
		$this->cq = $cq;
	}
	
	public function count() {
		$full_list = $this->cq->loadList();
		return count($full_list);
	}
}
?>