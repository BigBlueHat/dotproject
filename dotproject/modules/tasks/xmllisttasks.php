<?php
	if (!defined('DP_BASE_DIR')){
		die('You should not access this file directly');
	}

		$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );

		$q = new DBQuery;
	
		$q->addQuery('task_id');
		$q->addQuery('task_name');
		$q->addQuery('task_start_date');
		$q->addQuery('task_end_date');
		$q->addQuery('task_description');
		$q->addQuery('task_duration');
		$q->addQuery('task_milestone');
		$q->addQuery('task_related_url');
	
		$q->addTable('tasks');
	
		$q->addWhere("task_project = $project_id");

		$obj =& new CTask;
		$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
		if ( count($allowedTasks))
			$q->addWhere($allowedTasks);
	
		$tasks = $q->loadList();
		header('Content-type: text/xml');

		echo "<data>";

		foreach($tasks as $t)
		{
			$start_date = new CDate($t['task_start_date']);
			$end_date = new CDate($t['task_end_date']);
		
			echo "<event start=\"".$start_date->format("%B %d %Y %H:%M:%S GMT")."\" end=\"".$end_date->format("%B %d %Y %H:%M:%S GMT")."\"";
			if ($t['task_milestone'] == 0) echo " isDuration=\"true\"";
			echo " link=\"".DP_BASE_URL."/index.php?m=tasks&amp;a=view&amp;task_id=".$t['task_id']."\"";
			echo " title=\"".$t['task_name']."\">\n";
			echo $t['task_description'];
			echo "</event>\n";
		}
	
		echo "</data>";

?>
