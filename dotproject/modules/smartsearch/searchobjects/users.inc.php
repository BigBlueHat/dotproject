<?php 
class users {
	var $table = 'users';
	var $search_fields = array ("user_username", "user_first_name", "user_last_name", "user_email", "user_address1", "user_address2", "user_city",
	 							"user_state","user_zip","user_country","user_pic","user_signature");
	
	var $keyword = null;
	
	function cuser (){
		return new users();
	}
	
	function fetchResults(){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring .= "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Users')."</th>\n";
		if($results){
			foreach($results as $records){
				$outstring .= "<tr>";
				$outstring .= "<td>";
				$outstring .= "<a href = \"index.php?m=admin&a=viewuser&user_id=".$records["user_id"]."\">".$records["user_username"]."</a>\n";
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
		$sql = "SELECT user_id, user_username"
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