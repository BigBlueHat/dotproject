<?php
class Projects_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();	
		
		$db = DP_Config::getDB();
		$select = $db->select();
		
		$select->from('projects');
		$select->join(array('c'=>'companies'),
			'project_company = c.company_id',
			array('company_id', 'company_name')
		);
		$select->join(array('u'=>'users'),
			'project_owner = u.user_id'
		);

		$this->query = $select;
		
		$cq = $db->select();
		$cq->from('projects',Array('COUNT(*)'));
		$this->cq = $cq;
	}
}
?>