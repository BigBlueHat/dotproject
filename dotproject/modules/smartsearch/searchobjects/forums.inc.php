<?php 
class forums {
	var $table = 'forums';
	var $search_fields = array ("forum_name","forum_description");
	var $keyword = null;
	var $follow_up_link = 'index.php?m=forums&a=viewer&forum_id=';
	
	function cforums (){
		return new forums();
	}
	
	function fetchResults(&$permissions){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring = "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Forums')."</th>\n";
		if($results){
			foreach($results as $records){
			    if ($permissions->checkModuleItem($this->table, "view", $records["forum_id"])) {
    				$outstring .= "<tr>";
    				$outstring .= "<td>";
    				$outstring .= "<a href = \"index.php?m=forums&a=viewer&forum_id=".$records["forum_id"]."\">".$records["forum_name"]."</a>\n";
    				$outstring .= "</td>\n";
			    }
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
		$sql = "SELECT forum_id, forum_name"
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