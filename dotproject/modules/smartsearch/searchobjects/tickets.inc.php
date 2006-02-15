<?php 
class tickets {
	var $table = 'tickets';
	var $search_fields = array ("author","recipient","subject","type","cc","body","signature");
	var $keyword = null;
	
	
	function ctickets (){
		return new tickets();
	}
	
	function fetchResults(&$permissions){
		global $AppUI;
		$sql = $this->_buildQuery();
		$results = db_loadList($sql);

		$outstring = "<th nowrap='nowrap' >".$AppUI->_('Tickets')."</th>\n";
		if($results){
			foreach($results as $records){
			    if($permissions->checkModuleItem($this->table, "view", $records["ticket"])){
    				$outstring .= "<tr>";
    				$outstring .= "<td>";
    				$outstring .= "<a href = \"index.php?m=ticketsmith&a=view&ticket=".$records["ticket"]."\">".highlight($records["subject"], $this->keyword)."</a>\n";
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
	
	function search($permissions){
		$q  = new DBQuery;
		$q->addTable($this->table);
		$q->addQuery('ticket');
		$q->addQuery('subject');

		$sql = '';
		foreach($this->search_fields as $field){
						$sql.=" $field LIKE '%$this->keyword%' or ";
		}
		$sql = substr($sql,0,-4);
		$q->addWhere($sql);
		$results = $q->loadList();
		if($results)
			foreach($results as $k => $records)
				if($permissions->checkModuleItem($this->table, "view", $records["ticket"]))
					$tickets[$records['ticket']] = $records['subject'];

		return $tickets;
//		return $q->prepare(true);
	}
	
	function searchResults($permissions)
	{
		global $tpl;
		
		$tickets = $this->search($permissions);
		$results = array('results', $tickets);
		$tpl->assign('name', 'tickets');
		$tpl->assign('results', $results);
		return $tpl->fetchFile('list');
	}
}
?>
