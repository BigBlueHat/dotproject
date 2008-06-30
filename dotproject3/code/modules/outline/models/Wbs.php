<?php
class Wbs {
	protected $tasks;
	protected $project;
	
	protected $depth;
	protected $last;
	
	private $tbl;
	
	public function __construct($data) 
	{
		$this->tasks = $data['tasks'];
		$this->project = $data['project'];
		
		$db = DP_Config::getDB();
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		$this->tbl = new Db_Table_Tasks();
		
		$this->parseArray($this->tasks);
	}
	
	public function parseArray($list, $parent_id = null) {
		$this->depth++;
		
		for($i = 0; $i < count($list); $i++) {
			$current = $list[$i];
			
			if (is_array($current)) {
				$this->parseArray($current, $this->last_id);
			} else {
				$current_id = $this->parseItem($current, $parent_id);

				$this->last_id = $current_id;
			}
		}		
		
		
		$this->depth--;
	}
	
	public function parseItem($item, $parent_id) {		
		$ins = $this->tbl->insert(
			Array('task_name'=>$item,
				'task_parent'=>$parent_id
			)
		);
		return $ins;
	}
	
	
}
?>