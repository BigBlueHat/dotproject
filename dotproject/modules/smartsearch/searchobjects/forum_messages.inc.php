<?php 
class forum_messages {
	var $table = 'forum_messages';
	var $search_fields = array ("message_title","message_body");
	var $keyword = null;
	
	function cforum_messages (){
		return new forum_messages();
	}
	
	function fetchResults(&$permissions){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring = "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Forum Messages')."</th>\n";
		if($results){
			foreach($results as $records){
			    if ($permissions->checkModuleItem($this->table, "view", $records["message_id"])) {
    				$outstring .= "<tr>";
    				$outstring .= "<td>";
    				$outstring .= "<a href = \"index.php?m=forums&a=view&forum_id=".$records["message_forum"]."&message_id=".$records["message_id"]."\">".$records["message_title"]."</a>\n";
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
		$sql = "SELECT message_id, message_forum, message_title"
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