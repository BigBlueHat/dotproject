<?php 
class task_log {
	var $table = 'task_log';
	var $search_fields = array ("task_log_name","task_log_description");
	var $keyword = null;
		
	function ctask_log (){
		return new task_log();
	}
	
		function fetchResults(){
			global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring = "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Task Log')."</th>\n";
		if($results){
			foreach($results as $records){
				$outstring .= "<tr>";
				$outstring .= "<td>";
				$outstring .= "<a href = \"index.php?m=tasks&a=view&task_id=".$records["task_log_task"]."&tab=1&task_log_id=".$records["task_log_id"]. "\">".$records["task_log_name"]."</a>\n";
				$outstring .= "</td>";
			}
		$outstring .= "</tr>";
		}
		else {
			$outstring .= "<tr>"."<td>".$AppUI->_('Empty')."</td>"."</tr>";
		}
		return $outstring;
	}
	
	function setKeyword($keyword){
		$this->keyword = $keyword;
	}
	
	function _buildQuery(){
		$sql = "SELECT task_log_id, task_log_name, task_log_task"
			 . "\nFROM $this->table"
			 . "\nWHERE";
		foreach($this->search_fields as $field){
			$sql.=" $field LIKE '%$this->keyword%' or ";
		}
		$sql = substr($sql,0,-4);
		return $sql;
	}
}
?>