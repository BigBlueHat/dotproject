<?php 
class companies {
	var $table = 'companies';
	var $search_fields = array ("company_name", "company_address1", "company_address2", "company_city", "company_state", "company_zip", "company_primary_url",
	 							"company_description", "company_email");
	var $keyword = null;
	
	function ccompanies (){
		return new companies();
	}
	
	function fetchResults(){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);
		$outstring = "<th nowrap='nowrap' STYLE='background: #08245b' >".$AppUI->_('Companies'). "</th>\n";
		if($results){
			foreach($results as $records){
				$outstring .= "<tr>";
				$outstring .= "<td>";
				$outstring .= "<a href = \"index.php?m=companies&a=view&company_id=".$records["company_id"]."\">".$records["company_name"]."</a>\n";
				$outstring .= "</td>\n";
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
		$sql = "SELECT company_id, company_name"
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