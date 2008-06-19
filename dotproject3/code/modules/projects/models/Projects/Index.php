<?php
class Projects_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();

		// @todo - Change over DP_Query to Zend_Db_Statement.
		
		// Create query to generate list data
		$q  = new DP_Query;
		$q->addTable('projects');
		$q->addJoin('companies','c','projects.project_company = c.company_id');
		$q->addQuery('projects.project_id, 
						projects.project_name, 
						projects.project_start_date,
						projects.project_end_date,
						c.company_id, 
						c.company_name');
		$this->query = $q;
		
		Zend_Debug::dump($q->prepare());
		
		$cq = new DP_Query;
		$cq->addTable('projects');
		$cq->addJoin('companies','c','projects.project_company = c.company_id');
		$cq->addQuery('projects.project_id,						
						projects.project_name, 
						projects.project_start_date,
						projects.project_end_date,
						c.company_id, 
						c.company_name');
		$this->cq = $cq;
	}
	
	public function count() {
		$full_list = $this->cq->loadList();
		return count($full_list);
	}
}
?>