<?php
//Calendar functions
if(empty($thisMonth)){$thisMonth = date("n", time());}
if(empty($thisYear)){$thisYear = date("Y", time());}


//----------------------------------
//Pull added events with start date in this month
//----------------------------------
	$psql = "select 
	event_title, 
	event_id,	
	month(from_unixtime(event_start_date)) as m,
	DAYOFMONTH(from_unixtime(event_start_date)) as d,
	year(from_unixtime(event_start_date)) as y
	from 	events where month(from_unixtime(event_start_date)) = $thisMonth
	and year(from_unixtime(event_start_date)) = $thisYear";
	$perc = mysql_query($psql);
	while($perow = mysql_fetch_array($perc)){
		$events[] = array("color"=>"blue","type"=>"e","title"=>"Event: " . $perow["event_title"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["event_title"],"id" =>$perow["event_id"]);
	}
//----------------------------------
//Pull Starting projects for this month
//----------------------------------
	$psql = "select 
	project_name, 
	project_id,	
	month(project_start_date) as m,
	DAYOFMONTH(project_start_date) as d,
	year(project_start_date) as y,
	project_color_identifier as color
	from 	projects where month(project_start_date) = $thisMonth
	and year(project_start_date) = $thisYear";
	$perc = mysql_query($psql);
	while($perow = mysql_fetch_array($perc)){
		$events[] = array("color"=>$perow["color"],"type"=>"p","title"=>"Start: " . $perow["project_name"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["project_name"],"id" =>$perow["project_id"]);
	}
//----------------------------------	
//Pull Ending projects for this month
//----------------------------------
	$psql = "select 
	project_name, 
	project_id,	
	month(project_end_date) as m,
	DAYOFMONTH(project_end_date) as d,
	year(project_end_date) as y,
	project_color_identifier as color 
	from 	projects where month(project_end_date) = $thisMonth 
	and year(project_end_date) = $thisYear";
	
	$perc = mysql_query($psql);
	while($perow = mysql_fetch_array($perc)){
		$events[] = array("color"=>$perow["color"],"type"=>"p","title"=>"Due: " . $perow["project_name"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["project_name"],"id" =>$perow["project_id"]);
	}
	
//----------------------------------
//Pull starting tasks for this month
//----------------------------------
	$psql = "select 
	task_name, 
	task_id,	
	task_priority, 
	task_milestone,
	task_duration,
	UNIX_TIMESTAMP(task_start_date) as tsd,
	project_color_identifier as color,
	date_add(task_start_date, interval task_duration hour) as task_should_end 
	from 	tasks, projects
	where 
	(month(task_start_date) = $thisMonth and year(task_start_date) = $thisYear
	or month(date_add(task_start_date, interval task_duration hour)) = $thisMonth and year(date_add(task_start_date, interval task_duration hour)) = $thisYear)
	and task_project = project_id";
	
	$perc = mysql_query($psql);
	//echo $psql;
	echo mysql_error();
	while($perow = mysql_fetch_array($perc))
	{
	$z = ceil($perow["task_duration"] / 24);
	for($x=0;$x<$z;$x++){
		$dater = $perow["tsd"] + ($x * 86400);
		$day = strftime("%d", $dater);
		$month = strftime("%m", $dater);
		$year = strftime("%Y", $dater);
		$events[] = array("color"=>$perow["color"],"type"=>"t","title"=>$perow["task_name"], "d"=> $day,"m"=>$month, "y" => $year, "name"=>$perow["task_name"],"id" =>$perow["task_id"], "milestone"=>$perow["task_milestone"],"priority"=>$perow["task_priority"]);
		}
	}

//----------------------------------
//Pull due tasks for this month
//----------------------------------
	$psql = "select 
	task_name, 
	task_id,	
	month(task_end_date) as m,
	year(task_end_date) as y,
	DAYOFMONTH(task_end_date) as d,
	project_color_identifier as color
	from 	tasks, projects
	where month(task_end_date) = $thisMonth 
	and year(task_end_date) = $thisYear
	and task_project = project_id";
	
	$perc = mysql_query($psql);
	while($perow = mysql_fetch_array($perc)){
		$events[] = array("color"=>$perow["color"],"type"=>"t","title"=>$perow["task_name"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["task_name"],"id" =>$perow["task_id"]);
	}

function eventsForDate($d, $m, $y){
	global $events;
	$fillcal = array();

	if(is_array($events)){
		reset($events);
		while (list ($key, $val) = each ($events)){
			if($val["d"] == $d && $val["y"] == $y && $val["m"] == $m ){
				$fillcal[] = array("type"=>$val["type"],"title"=>$val["title"], "d"=> $val["d"],"m"=>$val["m"], "y" => $val["y"], "name"=>$val["name"],"id" =>$val["id"],"color" =>$val["color"],"priority" =>@$val["priority"],"milestone"=>@$val["milestone"]);
			}
		}
	}
	return $fillcal;



}


	
?>
