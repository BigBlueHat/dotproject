<?php
//Calendar functions
if (empty( $thisMonth )) {
	$thisMonth = date( "n", time() );
}
if (empty( $thisYear )) {
	$thisYear = date( "Y", time() );
}

$company_id = isset($HTTP_GET_VARS["company_id"]) ? $HTTP_GET_VARS["company_id"] :
	(isset($HTTP_POST_VARS["company_id"]) ? $HTTP_POST_VARS["company_id"] : $AppUI->user_company);

$pwhere = $company_id ? "and project_company = $company_id" : '';
//----------------------------------
//Pull added events with start date in this month
//----------------------------------
$psql = "
SELECT event_title, event_id,
	month(from_unixtime(event_start_date)) AS m,
	DAYOFMONTH(from_unixtime(event_start_date)) AS d,
	year(from_unixtime(event_start_date)) AS y
FROM events
WHERE month(from_unixtime(event_start_date)) = $thisMonth
	AND year(from_unixtime(event_start_date)) = $thisYear
";

$perc = mysql_query( $psql );
while ($perow = mysql_fetch_array( $perc )) {
	$events[] = array("color"=>"blue","type"=>"e","title"=>"Event: " . $perow["event_title"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["event_title"],"id" =>$perow["event_id"]);
}
//----------------------------------
//Pull Starting projects for this month
//----------------------------------
// get denied projects
$dsql = "
SELECT distinct project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'projects' 
	AND permission_item = project_id
	AND permission_value = 0
";
##echo "<pre>$dsql</pre>";
$drc = mysql_query($dsql);
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
	$deny[] = $row[0];
}
// get allowed projects
$asql = "
SELECT distinct project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0 
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' and permission_item = -1)
		OR (permission_grant_on = 'projects' and permission_item = project_id)
	)
"
. (count($deny) > 0 ? 'AND project_id NOT IN (' . implode( ',', $deny ) . ')' : '');
##echo "<pre>$asql</pre>";
$arc = mysql_query( $asql );
$pallow = array();
while ($row = mysql_fetch_array( $arc, MYSQL_NUM )) {
	$pallow[] = $row[0];
}

$pwhere .= (count($pallow) > 0 ? ' AND project_id IN (' . implode( ',', $pallow ) . ')' : '');

$psql = "
SELECT project_name, project_id,
	MONTH(project_start_date) as m,
	DAYOFMONTH(project_start_date) as d,
	YEAR(project_start_date) as y,
	project_color_identifier as color
FROM projects
WHERE MONTH(project_start_date) = $thisMonth
	AND YEAR(project_start_date) = $thisYear
	$pwhere
";
##echo "<pre>$psql</pre>";
$perc = mysql_query( $psql );
while ($perow = mysql_fetch_array( $perc )) {
	$events[] = array( "color"=>$perow["color"],"type"=>"p","title"=>"Start: " . $perow["project_name"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["project_name"],"id" =>$perow["project_id"] );
}
//----------------------------------
//Pull Ending projects for this month
//----------------------------------
$psql = "
select project_name, project_id,
	MONTH(project_end_date) as m,
	DAYOFMONTH(project_end_date) as d,
	YEAR(project_end_date) as y,
	project_color_identifier as color
FROM projects
WHERE month(project_end_date) = $thisMonth
	AND YEAR(project_end_date) = $thisYear
	$pwhere
";
##echo "<pre>$psql</pre>";
$perc = mysql_query( $psql );
while ($perow = mysql_fetch_array( $perc )) {
	$events[] = array("color"=>$perow["color"],"type"=>"p","title"=>"Due: " . $perow["project_name"], "d"=> $perow["d"],"m"=>$perow["m"], "y" => $perow["y"], "name"=>$perow["project_name"],"id" =>$perow["project_id"]);
}

//----------------------------------
//Pull starting tasks for this month
//----------------------------------
$psql = "
select task_name, task_id,	task_priority, task_milestone,task_duration,
	UNIX_TIMESTAMP(task_start_date) as tsd,
	project_color_identifier as color,
	date_add(task_start_date, interval task_duration hour) as task_should_end
from tasks, projects
where (month(task_start_date) = $thisMonth
	and year(task_start_date) = $thisYear
	or month(date_add(task_start_date, interval task_duration hour)) = $thisMonth
	and year(date_add(task_start_date, interval task_duration hour)) = $thisYear)
	and task_project = project_id
	$pwhere
";

$perc = mysql_query($psql);
//echo $psql;
echo mysql_error();
while ($perow = mysql_fetch_array($perc)) {
	$z = ceil($perow["task_duration"] / 24);
	for ($x=0; $x < $z; $x++) {
		$dater = $perow["tsd"] + ($x * 86400);
		$day = strftime( "%d", $dater );
		$month = strftime( "%m", $dater );
		$year = strftime( "%Y", $dater );
		$events[] = array( "color"=>$perow["color"],"type"=>"t","title"=>$perow["task_name"], "d"=> $day,"m"=>$month, "y" => $year, "name"=>$perow["task_name"], "id" =>$perow["task_id"], "milestone"=>$perow["task_milestone"], "priority"=>$perow["task_priority"] );
	}
}

//----------------------------------
//Pull due tasks for this month
//----------------------------------
$psql = "
select task_name, task_id,
	month(task_end_date) as m,
	year(task_end_date) as y,
	DAYOFMONTH(task_end_date) as d,
	project_color_identifier as color
from tasks, projects
where month(task_end_date) = $thisMonth
	and year(task_end_date) = $thisYear
	and task_project = project_id
	$pwhere
";

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
