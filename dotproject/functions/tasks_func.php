<?php
$percent = array(0=>'0',5=>'5',10=>'10',15=>'15',20=>'20',25=>'25',30=>'30',35=>'35',40=>'40',45=>'45',50=>'50',55=>'55',60=>'60',65=>'65',70=>'70',75=>'75',80=>'80',85=>'85',90=>'90',95=>'95',100=>'100');

$filters = array(
	'my' => 'My Tasks',
	'myproj' => 'My Projects',
	'mycomp' => 'All Tasks for my Company',
	'myinact' => 'My Tasks (show in-active)',
	'all' => 'All Tasks'
);

$status = array(
 0 => 'Active',
 -1 => 'In-active'
);

$priority = array(
 -1 => 'low',
 0 => 'normal',
 1 => 'high'
);

function get_end_date($start_date, $duration) {
	define(DAYLY_WORKING_HOURS, 12);
	
	if(!$duration) return "";
	
	if($duration < 24) {
		// fix durations < 24 according to working hours
		$duration = ceil($duration / DAYLY_WORKING_HOURS) * 24;
	}
	return time2YMD(strtotime($start_date . " -1 day + " . $duration . " hours"));
}

?>