<?php
class Orgunit_Index extends DP_List_Dynamic {
	
	public function __construct() {
		parent::__construct();	
		
		$db = DP_Config::getDB();
		$select = $db->select();
		
		$select->from('ous');
		$this->query = $select;
		
		$cq = $db->select();
		$cq->from('ous',Array('COUNT(*)'));
		$this->cq = $cq;
	}
}
?>