<?php /* TASKS $Id$ */
/**
* Tasks :: Add/Edit Form
*
*/

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$task_parent = intval( dPgetParam( $_GET, "task_parent", 0 ) );

// load the record data
$obj = new CTask();

if (!$obj->load( $task_id ) && $task_id > 0) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// check for a valid project parent
$task_project = intval( $obj->task_project );
if (!$task_project) {
	$task_project = dPgetParam( $_GET, 'task_project', 0 );
	if (!$task_project) {
		$AppUI->setMsg( "badTaskProject", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

// check permissions
if ( $task_id ) {
	// we are editing an existing task
	$canEdit = !getDenyEdit( $m, $task_id );
} else {
	// we are trying to add a new task
	
	// do we have write access on this project?
	$canEdit = ( !getDenyEdit( 'projects', $task_project ) );
	
	// Asumption: if a user has write access on a project, he will also
	// be able to add tasks, files and events. There is no way for
	// allowing someone to edit a project information and not editing
	// its tasks, files and events.
}

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

//check permissions for the associated project
$canReadProject = !getDenyRead( 'projects', $obj->task_project);

$durnTypes = dPgetSysVal( 'TaskDurationType' );

// check the document access (public, participant, private)
if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$task_parent = isset( $obj->task_parent ) ? $obj->task_parent : $task_parent;

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;

// pull the related project
$project = new CProject();
$project->load( $task_project );

//Pull all users
$sql = "
SELECT user_id, CONCAT_WS(' ',user_first_name,user_last_name)
FROM users
ORDER BY user_first_name, user_last_name
";
$users = db_loadHashList( $sql );

if ( $task_id == 0 ) {
	// Add task creator to assigned users by default
	$assigned = array($AppUI->user_id => "$AppUI->user_first_name $AppUI->user_last_name [100%]");
	$assigned_perc = array($AppUI->user_id => "100");	
} else {
	// Pull users on this task
//			 SELECT u.user_id, CONCAT_WS(' ',u.user_first_name,u.user_last_name)
	$sql = "
			 SELECT u.user_id, CONCAT(CONCAT_WS(' [', CONCAT_WS(' ',u.user_first_name,u.user_last_name), t.perc_assignment), '%]')
			   FROM users u, user_tasks t
			 WHERE t.task_id =$task_id
			 AND t.task_id <> 0
			 AND t.user_id = u.user_id
			 ";
	$assigned = db_loadHashList( $sql );
	$sql = "
			 SELECT u.user_id, t.perc_assignment
			   FROM users u, user_tasks t
			 WHERE t.task_id =$task_id
			 AND t.task_id <> 0
			 AND t.user_id = u.user_id
			 ";
	$assigned_perc = db_loadHashList( $sql );	
}

// Pull tasks for the parent task list
$sql="
SELECT task_id, task_name, task_end_date, task_start_date, task_milestone 
FROM tasks
WHERE task_project = $task_project
	AND task_id <> $task_id
ORDER BY task_project
";


$projTasks = array( $obj->task_id => $AppUI->_('None') );
$res = db_exec( $sql );
while ($row = db_fetch_row( $res )) {
	/*
	if (strlen( $row[1] ) > 30) {
		$row[1] = substr( $row[1], 0, 27 ).'...';
	}
	*/
	$projTasks[$row[0]] = $row[1];
}

//create array with start and end date of all tasks.
$sql="
SELECT task_id, task_name, task_end_date, task_start_date, task_milestone 
FROM tasks
WHERE task_project = $task_project
ORDER BY task_project
";
$projTasksWithEndDates = array( $obj->task_id => $AppUI->_('None') );//arrays contains task end date info for setting new task start date as maximum end date of dependenced tasks
$res = db_exec( $sql );
while ($row = db_fetch_row( $res )) {
	if ($row[4] == 0) {
		$date = new CDate($row[2]);
	} else {
		$date = new CDate($row[3]);
	}
	$sdate = $date->format("%d/%m/%Y");
	$shour = $date->format("%H");
	$smin = $date->format("%M");
		
	$projTasksWithEndDates[$row[0]] = array($row[1], $sdate, $shour, $smin);
}

// Pull tasks dependencies
$sql = "
SELECT t.task_id, t.task_name
FROM tasks t, task_dependencies td
WHERE td.dependencies_task_id = $task_id
	AND t.task_id = td.dependencies_req_task_id
";
$taskDep = db_loadHashList( $sql );

// setup the title block
$ttl = $task_id > 0 ? "Edit Task" : "Add Task";
$titleBlock = new CTitleBlock( $ttl, 'applet-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ( $canReadProject ) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$task_project", "view this project" );
}
if ($task_id > 0)
  $titleBlock->addCrumb( "?m=tasks&a=view&task_id=$obj->task_id", "view this task" );
$titleBlock->show();

// Let's gather all the necessary information from the department table
// collect all the departments in the company
$depts = array( 0 => '' );

// ALTER TABLE `tasks` ADD `task_departments` CHAR( 100 ) ;
$company_id                = $project->project_company;
$selected_departments      = $obj->task_departments != "" ? explode(",", $obj->task_departments) : array();
$departments_count         = 0;
$department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
if($department_selection_list!=""){
	$department_selection_list = "<select name='dept_ids[]' size='$departments_count' multiple style='width:12em'>
								  $department_selection_list
    	                          </select>";
}

	
$initPercAsignment = "";
global $assigned;
$keys = array_keys($assigned);
for ($i = 0; $i < sizeof($keys); $i++) {
	$k = $keys[$i];
	$v = $assigned_perc[$k];
	$initPercAsignment = $initPercAsignment."".$k."=".$v.";";
}



function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent=0, $spaces = 0){
	global $departments_count;
	$parsed = '';
	
	if($departments_count < 10) $departments_count++;
	$sql = "select dept_id, dept_name
	        from departments
	        where dept_parent      = '$dept_parent'
	              and dept_company = '$company_id'";
	$depts_list = db_loadHashList($sql, "dept_id");

	foreach($depts_list as $dept_id => $dept_info){
		$selected = in_array($dept_id, $checked_array) ? "selected" : "";

		$parsed .= "<option value='$dept_id' $selected>".str_repeat("&nbsp;", $spaces).$dept_info["dept_name"]."</option>";
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces+5);
	}
	
	return $parsed;
}

//Dynamic tasks are by default now off because of dangerous behavior if incorrectly used
if ( is_null($obj->task_dynamic) ) $obj->task_dynamic = 0 ;

//Time arrays for selects
$start = $AppUI->getConfig('cal_day_start');
$end   = $AppUI->getConfig('cal_day_end');
$inc   = $AppUI->getConfig('cal_day_increment');
if ($start === null ) $start = 8;
if ($end   === null ) $end = 17;
if ($inc   === null)  $inc = 15;
$hours = array();
for ( $current = $start; $current < $end + 1; $current++ ) {
	if ( $current < 10 ) { 
		$current_key = "0" . $current;
	} else {
		$current_key = $current;
	}
	
	if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ){
		//User time format in 12hr
		$hours[$current_key] = ( $current > 12 ? $current-12 : $current );
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

$minutes = array();
$minutes["00"] = "00";
for ( $current = 0 + $inc; $current < 60; $current += $inc ) {
	$minutes[$current] = $current;
}

// Code to see if the current user is
// enabled to change time information related to task
$can_edit_time_information = false;
// Let's see if all users are able to edit task time information
if(isset($dPconfig['restrict_task_time_editing']) && $dPconfig['restrict_task_time_editing']==true && $obj->task_id > 0){

	// Am I the task owner?
	if($obj->task_owner == $AppUI->user_id){
		$can_edit_time_information = true;
	}
	
	// Am I the project owner?
	if($project->project_owner == $AppUI->user_id){
		$can_edit_time_information = true;
	}
	
	// Am I sys admin?
	if(!getDenyEdit("admin")){
		$can_edit_time_information = true;
	}
	
} else if (!isset($dPconfig['restrict_task_time_editing']) || $dPconfig['restrict_task_time_editing']==false || $obj->task_id == 0) { // If all users are able, then don't check anything
	$can_edit_time_information = true;
}

?>

<SCRIPT language="JavaScript">
var calendarField = '';
var calWin = null;
var selected_contacts_id = "<?= $obj->task_contacts; ?>";

<?
echo "var projTasksWithEndDates=new Array();\n";
$keys = array_keys($projTasksWithEndDates);
for ($i = 1; $i < sizeof($keys); $i++) {
	//array[task_is] = end_date, end_hour, end_minutes
	echo "projTasksWithEndDates[".$keys[$i]."]=new Array(\"".$projTasksWithEndDates[$keys[$i]][1]."\", \"".$projTasksWithEndDates[$keys[$i]][2]."\", \"".$projTasksWithEndDates[$keys[$i]][3]."\");\n";
}
?>

/**
setTasksStartDate sets new task's start date value which is maximum end date of all dependend tasks
to do: date format should be taken from config
*/
function setTasksStartDate() {

	var form = document.editFrm;
	var td = form.task_dependencies.length -1;
	var max_date = new Date("1970", "01", "01");
	var max_id = -1;
	
	if (form.set_task_start_date.checked == true) {	
		//build array of task dependencies
		for (td; td > -1; td--) {
			var i = form.task_dependencies.options[td].value;
			var val = projTasksWithEndDates[i][0]; //format 05/03/2004
			var sdate = new Date(val.substring(6,10),val.substring(3,5)-1, val.substring(0,2));
			if (sdate > max_date) {
				max_date = sdate;
				max_id = i;
			}
		}
		
		//check end date of parent task 
		if (form.task_parent.options.selectedIndex!=0) {
			var i = form.task_parent.options[form.task_parent.options.selectedIndex].value;	
			var val = projTasksWithEndDates[i][0]; //format 05/03/2004	
			var sdate = new Date(val.substring(6,10),val.substring(3,5)-1, val.substring(0,2));
			if (sdate > max_date) {
				max_date = sdate;
				max_id = i;		
			}
		}
		
		if (max_id != -1) {
			var hour  = projTasksWithEndDates[max_id][1];
			var minute = projTasksWithEndDates[max_id][2];
		
			form.start_date.value = projTasksWithEndDates[max_id][0];
			form.start_hour.value = hour;
			form.start_minute.value = minute;
			
			 var d = projTasksWithEndDates[max_id][0];
			 //hardcoded date format Ymd
			 form.task_start_date.value = d.substring(6,10) + "" + d.substring(3,5) + "" + d.substring(0,2);	 
		}	
	}
}

function popContacts() {
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&company_id=<?php echo $company_id; ?>&selected_contacts_id='+selected_contacts_id, 'contacts','left=50,top=50,height=250,width=400,resizable,scrollbars=yes');
}

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=251, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function setContacts(contact_id_string){
	if(!contact_id_string){
		contact_id_string = "";
	}
	document.editFrm.task_contacts.value = contact_id_string;
	selected_contacts_id = contact_id_string;
}

function submitIt(){
	var form = document.editFrm;
	var fl = form.assigned.length -1;
	var dl = form.task_dependencies.length -1;

	if (form.task_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('taskName');?>" );
		form.task_name.focus();
	}
<?php 
	if ( $AppUI->getConfig( 'check_task_dates' )  && $can_edit_time_information) {
?>
	else if (!form.task_start_date.value) {
		alert( "<?php echo $AppUI->_('taskValidStartDate');?>" );
		form.task_start_date.focus();
	}
	else if (!form.task_end_date.value) {
		alert( "<?php echo $AppUI->_('taskValidEndDate');?>" );
		form.task_end_date.focus();
	}
<?php
	}
?>	
	else {
		form.hassign.value = "";
		for (fl; fl > -1; fl--){
			form.hassign.value = "," + form.hassign.value +","+ form.assigned.options[fl].value
		}

		form.hdependencies.value = "";
		for (dl; dl > -1; dl--){
			form.hdependencies.value = "," + form.hdependencies.value +","+ form.task_dependencies.options[dl].value
		}

		<?php
			if($can_edit_time_information){
				?>
		if ( form.task_start_date.value.length > 0 ) {
			form.task_start_date.value += form.start_hour.value + form.start_minute.value;
		}
		
		if ( form.task_end_date.value.length > 0 ) {
			form.task_end_date.value += form.end_hour.value + form.end_minute.value;
		}
				<?php
			} // can_edit_time_information
		?>
		form.submit();
	}
}

function addUser() {
	var form = document.editFrm;
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of percentage assignment of selected resource
	var perc = form.percentage_assignment.options[form.percentage_assignment.selectedIndex].value;

	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assigned.options[au].value + ","
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf( "," + form.resources.options[fl].value + "," ) == -1) {
			t = form.assigned.length
			opt = new Option( form.resources.options[fl].text+" ["+perc+"%]", form.resources.options[fl].value);
			form.hperc_assign.value += form.resources.options[fl].value+"="+perc+";";
			form.assigned.options[t] = opt
		}
	}
}

function removeUser() {
	var form = document.editFrm;
	fl = form.assigned.length -1;

	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			//remove from hperc_assign
			var selValue = form.assigned.options[form.assigned.selectedIndex].value;			
			var re = ".*("+selValue+"=[0-9]*;).*";
			var hiddenValue = form.hperc_assign.value;
			if (hiddenValue) {
				var b = hiddenValue.match(re);
				if (b[1]) {
					hiddenValue = hiddenValue.replace(b[1], '');
				}
				form.hperc_assign.value = hiddenValue;
				form.assigned.options[fl] = null;
			}
		}
	}
}

function addTaskDependency() {
	var form = document.editFrm;
	var at = form.all_tasks.length -1;
	var td = form.task_dependencies.length -1;
	var tasks = "x";

	//build array of task dependencies
	for (td; td > -1; td--) {
		tasks = tasks + "," + form.task_dependencies.options[td].value + ","
	}

	//Pull selected resources and add them to list
	for (at; at > -1; at--) {
		if (form.all_tasks.options[at].selected && tasks.indexOf( "," + form.all_tasks.options[at].value + "," ) == -1) {
			t = form.task_dependencies.length
			opt = new Option( form.all_tasks.options[at].text, form.all_tasks.options[at].value );
			form.task_dependencies.options[t] = opt
		}
	}
	
	setTasksStartDate();
}

function removeTaskDependency() {
	var form = document.editFrm;
	td = form.task_dependencies.length -1;

	for (td; td > -1; td--) {
		if (form.task_dependencies.options[td].selected) {
			form.task_dependencies.options[td] = null;
		}
	}
	
	setTasksStartDate();
}

function setAMPM( field) {
	if ( field.value > 11 ){
		document.editFrm[field.name + "_ampm"].value = "pm";
	} else {
		document.editFrm[field.name + "_ampm"].value = "am";
	}
}

var workHours = <?php echo $AppUI->getConfig( 'daily_working_hours' );?>;
var hourMSecs = 3600*1000;

/**
* no comment needed
*/
function isInArray(myArray, intValue) {

	for (var i = 0; i < myArray.length; i++) {
		if (myArray[i] == intValue) {
			return true;
		}
	}		
	return false;
}

/**
* @modify_reason calculating duration does not include time information and cal_working_days stored in config.php
*/
function calcDuration() {

	//working days array from config.php	
	var working_days = new Array(<?php echo $AppUI->getConfig( 'cal_working_days' );?>);
	var cal_day_start = <?php echo $AppUI->getConfig( 'cal_day_start' );?>;
	var cal_day_end = <?php echo $AppUI->getConfig( 'cal_day_end' );?>;		
	var daily_working_hours = <?php echo $AppUI->getConfig('daily_working_hours'); ?>;
	
	var f = document.editFrm;
	var int_st_date = new String(f.task_start_date.value + f.start_hour.value + f.start_minute.value);
	var int_en_date = new String(f.task_end_date.value + f.end_hour.value + f.end_minute.value);

	var sDate = new Date(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var eDate = new Date(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	
	var s = Date.UTC(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var e = Date.UTC(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	var durn = (e - s) / hourMSecs; //hours

	//now we should subtract non-working days from durn variable
	var duration = durn  / 24;
	var weekDays = 0; //full working days
	for (var i = 0; i < duration; i++) {
		var myDate = new Date(int_st_date.substring(0,4), (int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10));
		var myDay = myDate.getDate();
		myDate.setDate(myDay + i);
		if ( !isInArray(working_days, myDate.getDay()) ) {
			weekDays++;
		}
	}
	
	//calculating correct durn value
	durn = durn - weekDays*24;	//[hours]
	//could be 1 or 24 (based on TaskDurationType value)
	var durnType = parseFloat(f.task_duration_type.value);	
	durn /= durnType;

	if (durnType == 1){
		var hours = durn % 24;
		//if hours > 8 than we need to sum distance between start_hours and cal_day_end AND between end_hours and cal_day_end
		if (hours > daily_working_hours) {
			var a = cal_day_end - sDate.getHours();
			var b = eDate.getHours() - cal_day_start;
			hours = a + b;
		}
		var wrkdays = (durn - hours) / 24;
		durn = Math.floor(wrkdays) * workHours  + hours;
	} else if (durnType == 24 ) {
		//we should talk about working days so task duration equals 41 hrs means 6 (NOT 5) days!!!
		if (durn > Math.round(durn)) {
			durn++;
		}
	}

	if ( s > e )
		alert( 'End date is before start date!');
	else
		f.task_duration.value = Math.round(durn);
}

/**
* @modify reason calcFinish does not use time info and working_days array 
*/
function calcFinish() {
	//working days array from config.php	
	var working_days = new Array(<?php echo $AppUI->getConfig( 'cal_working_days' );?>);
	var cal_day_start = <?php echo $AppUI->getConfig( 'cal_day_start' );?>;
	var cal_day_end = <?php echo $AppUI->getConfig( 'cal_day_end' );?>;		
	
	var f = document.editFrm;
	//var int_st_date = new String(f.task_start_date.value);
	var int_st_date_time = new String(f.task_start_date.value + f.start_hour.value + f.start_minute.value);	
	var int_st_date = int_st_date_time;
	var s = new Date(int_st_date_time.substring(0,4),(int_st_date_time.substring(4,6)-1),int_st_date_time.substring(6,8), int_st_date_time.substring(8,10), int_st_date_time.substring(10,12));

	var durn = parseFloat(f.task_duration.value);//hours
	var durnType = parseFloat(f.task_duration_type.value); //1 or 24

	//temporary variables
	var inc = durn;
	var e = s;
	var hoursToAddToLastDay = 0;
	var hoursToAddToFirstDay = durn;
	var fullWorkingDays = 0;

	if ( durnType==24 ) {
		fullWorkingDays = Math.ceil(inc);
	 	for (var i = 0; i < Math.ceil(fullWorkingDays); i++) {
			e.setDate(s.getDate() + 1);
			if ( !isInArray(working_days, e.getDay()) ) {
				fullWorkingDays++;
			}		
		}	
		f.end_hour.value = f.start_hour.value;
	} else {
		if ( s.getHours() + inc > cal_day_end ) {
			hoursToAddToFirstDay = cal_day_end - s.getHours();
			inc -= hoursToAddToFirstDay;
			hoursToAddToLastDay = inc % workHours;
			fullWorkingDays = Math.round((inc - hoursToAddToLastDay) / workHours);
			if (hoursToAddToFirstDay != 0) {
				e = s;
				//we need to carefully add one day 
				//we should to check if this non-working day
				while ( true ) {
					e.setDate(e.getDate()+1);
					if (isInArray(working_days, e.getDay())) {					
						break;
					}
				}		
			} 
		}
		e.setHours(e.getHours()+hoursToAddToFirstDay);
		
	 	for (var i = 0; i < Math.ceil(fullWorkingDays); i++) {
			e.setDate(s.getDate() + 1);
			if ( !isInArray(working_days, e.getDay()) ) {
				fullWorkingDays++;
			}		
		}
		if (!(fullWorkingDays == 0 && hoursToAddToLastDay == 0)) {
			e.setHours(cal_day_start+hoursToAddToLastDay);
		}
		f.end_hour.value = (e.getHours() < 10 ? "0"+e.getHours() : e.getHours());
	}
	
	var tz1 = "";
	var tz2 = "";

	if ( e.getDate() < 10 ) tz1 = "0";
	if ( (e.getMonth()+1) < 10 ) tz2 = "0";

	f.task_end_date.value = e.getUTCFullYear()+tz2+(e.getMonth()+1)+tz1+e.getDate();
	f.end_date.value = tz1+e.getDate()+"/"+tz2+(e.getMonth()+1)+"/"+e.getUTCFullYear();
}

function changeRecordType(value){
	// if the record type is changed, then hide everything
	hideAllRows();
	// and how only those fields needed for the current type
	eval("show"+task_types[value]+"();");
}

</script>

<table border="1" cellpadding="4" cellspacing="0" width="100%" class="std">
<form name="editFrm" action="?m=tasks&project_id=<?php echo $task_project;?>" method="post">
	<input name="hperc_assign" type="hidden" value="<?php echo $initPercAsignment;?>"/>
	<input name="dosql" type="hidden" value="do_task_aed" />
	<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
	<input name="task_project" type="hidden" value="<?php echo $task_project;?>" />
	<input name='task_contacts' type='hidden' value="<?php echo $obj->task_contacts; ?>" />
<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier;?>" >
		<font color="<?php echo bestColor( $project->project_color_identifier ); ?>">
			<strong><?php echo $AppUI->_('Project');?>: <?php echo @$project->project_name;?></strong>
		</font>
	</td>
</tr>

<tr valign="top" width="50%">
	<td>
		<?php echo $AppUI->_( 'Task Name' );?> *
		<br /><input type="text" class="text" name="task_name" value="<?php echo dPformSafe( $obj->task_name );?>" size="40" maxlength="255" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Status' );?></td>
			<td>
				<?php echo arraySelect( $status, 'task_status', 'size="1" class="text"', $obj->task_status, true );?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Priority' );?> *</td>
			<td nowrap>
				<?php echo arraySelect( $priority, 'task_priority', 'size="1" class="text"', $obj->task_priority, true );?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Progress' );?></td>
			<td>
				<?php echo arraySelect( $percent, 'task_percent_complete', 'size="1" class="text"', $obj->task_percent_complete ) . '%';?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Milestone' );?>?</td>
			<td>
				<input type="checkbox" value=1 name="task_milestone" <?php if($obj->task_milestone){?>checked<?php }?> />
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr valign="top">
	<td width="50%">
	    <table>
	    	<tr>
	    		<td>
				    			<?php
				    				if($can_edit_time_information){
				    					?>
								<?php echo $AppUI->_( 'Task Creator' );?>
								<br />
							<?php echo arraySelect( $users, 'task_owner', 'class="text"', !isset($obj->task_owner) ? $AppUI->user_id : $obj->task_owner );?>
								<br />
									<?php
				    				} // $can_edit_time_information
								?>
								<?php echo $AppUI->_( 'Access' );?>
								<br />
								<?php echo arraySelect( $task_access, 'task_access', 'class="text"', intval( $obj->task_access ), true );?>
								<br /><?php echo $AppUI->_( 'Web Address' );?>
								<br /><input type="text" class="text" name="task_related_url" value="<?php echo @$obj->task_related_url;?>" size="40" maxlength="255" />
								<br />
							</td>
							<td valign='top'>
								<?php echo $AppUI->_("Task Type"); ?>
								<br />
								<?php echo arraySelect(dPgetSysVal("TaskType"), "task_type",  "class='text' onchange='javascript:changeRecordType(this.value);'", $obj->task_type, false); ?>
								<br /><br />
					<?php
						echo "<input type='button' class='button' value='".$AppUI->_("Select contacts...")."' onclick='javascript:popContacts();' />";
						// Let's check if the actual company has departments registered
						if($department_selection_list != ""){
							?>
									<?php echo $AppUI->_("Departments"); ?><br />
									<?php echo $department_selection_list; ?>
							<?php
						}
						
					?>
				</td>
			</tr>
		<tr>
			<td><?php echo $AppUI->_( 'Task Parent' );?>:</td>
			<td><?php echo $AppUI->_( 'Target Budget' );?></td>
		</tr>
		<tr>
			<td>
				<?php echo arraySelect( $projTasks, 'task_parent', 'class="text" onchange="javascript:setTasksStartDate()"', $task_parent ); ?>
			</td>
			<td><?php echo $dPconfig['currency_symbol'] ?><input type="text" class="text" name="task_target_budget" value="<?php echo @$obj->task_target_budget;?>" size="10" maxlength="10" /></td>
		</tr>
		</table>
	</td>
	<td  align="center" width="50%">
		<table cellspacing="0" cellpadding="2" border="0">
			<?php
				if($can_edit_time_information){
			?>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
					<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : "" ;?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('start_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
				<td>
					<table><tr>
						
				<?php
					echo "<td>" . arraySelect($hours, "start_hour",'size="1" onchange="setAMPM(this)" class="text"', $start_date ? $start_date->getHour() : $start ) . "</td><td>" . " : " . "</td>";
					echo "<td>" . arraySelect($minutes, "start_minute",'size="1" class="text"', $start_date ? $start_date->getMinute() : "0" ) . "</td>";
					if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
						echo '<td><input type="text" name="start_hour_ampm" value="' . ( $start_date ? $start_date->getAMPM() : ( $start > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" /></td>';
					}
				?>
					</tr></table>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Finish Date' );?></td>
				<td nowrap="nowrap">
					<input type="hidden" name="task_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
					<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
					<a href="#" onClick="popCalendar('end_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a>
				</td>
				<td>
				<table><tr>
				<?php
					echo "<td>" . arraySelect($hours, "end_hour",'size="1" onchange="setAMPM(this)" class="text"', $end_date ? $end_date->getHour() : $end ) . "</td><td>" . " : " . "</td>";
					echo "<td>" .arraySelect($minutes, "end_minute",'size="1" class="text"', $end_date ? $end_date->getMinute() : "00" ) . "</td>";
					if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
						echo '<td><input type="text" name="end_hour_ampm" value="' . ( $end_date ? $end_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" /></td>';
					}
				?>
				</tr></table>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Expected Duration' );?>:</td>
				<td nowrap="nowrap">
					<input type="text" class="text" name="task_duration" maxlength="8" size="6" value="<?php echo $obj->task_duration ? $obj->task_duration : 1;?>" />
				<?php
					echo arraySelect( $durnTypes, 'task_duration_type', 'class="text"', $obj->task_duration_type, true );
				?>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Calculate' );?>:</td>
				<td nowrap="nowrap">
					<input type="button" value="<?php echo $AppUI->_('Duration');?>" onclick="calcDuration()" class="button" />
					<input type="button" value="<?php echo $AppUI->_('Finish Date');?>" onclick="calcFinish()" class="button" />
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Dynamic Task' );?>?</td>
				<td nowrap="nowrap">
					<input type="checkbox" name="task_dynamic" value="1" <?php if($obj->task_dynamic!="0") echo "checked"?> />
				</td>
			</tr>
			<?php
				} else {  
			?>
			<tr>
					<td colspan='2'><?php echo $AppUI->_("Only the task owner, project owner, or system administrator is able to edit time related information."); ?></td>
				</tr>
			<?php
				}// end of can_edit_time_information
			?>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'All Tasks' );?>:</td>
				<td><?php echo $AppUI->_( 'Task Dependencies' );?>:</td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $projTasks, 'all_tasks', 'style="width:220px" size="10" style="font-size:9pt;" ', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:220px" size="10" style="font-size:9pt;" ', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<input type="checkbox" name="set_task_start_date" /><?php echo $AppUI->_( 'Set task start date based on dependancy' );?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addTaskDependency()" /></td>
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeTaskDependency()" /></td>
			</tr>
		</table>
	</td>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'Resources' );?>:</td>
				<td><?php echo $AppUI->_( 'Assigned to Task' );?>:</td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $users, 'resources', 'style="width:220px" size="10" style="font-size:9pt;" ', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $assigned, 'assigned', 'style="width:220px" size="10" style="font-size:9pt;" ', null ); ?>
				</td>
			<tr>
				<td colspan="2" align="center">
					<table>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser()" /></td>
						<td>
							<select name="percentage_assignment">
							<?php 
								for ($i = 5; $i <= 100; $i+=5) {
									echo "<option ".(($i==100)? "selected=\"true\"" : "" )." value=\"".$i."\">".$i."%</option>";
								}
							?>
							</select>
						</td>				
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser()" /></td>					
					</tr>
					</table>
				</td>
			</tr>
			</tr>
<!-- 			<tr>
				<td colspan=3 align="center">
					<input type="checkbox" name="task_notify" value="1" <?php //if($obj->task_notify!="0") echo "checked"?> /> <?php //echo $AppUI->_( 'notifyChange' );?>
				</td>
			</tr> -->
		</table>
	</td>
</tr>
<tr>
	<td valign="top" align="center">
		<table><tr><td align="left">
		<?php echo $AppUI->_( 'Description' );?>:
		<br />
		<textarea name="task_description" class="textarea" cols="60" rows="10" wrap="virtual"><?php echo @$obj->task_description;?></textarea>
		</td></tr></table><br />
	</td>
	<td valign="top" align="center">
		<table><tr><td align="left">
		<?php echo $AppUI->_( 'Comment' );?>:		
		<br />
		<textarea name="email_comment" class="textarea" cols="60" rows="10" wrap="virtual"></textarea><br />
		<input type="checkbox" name="task_notify" value="1" <?php if($obj->task_notify!="0") echo "checked"?> /> <?php echo $AppUI->_( 'notifyChange' );?>
		</td></tr></table><br />
		<?php
			error_reporting(E_ALL);
			require_once("./classes/customfieldsparser.class.php");
			// let's create the parser
			$cfp = new CustomFieldsParser("TaskCustomFields", $obj->task_id);
			
			// we will need the amount of record types
			$amount_task_record_types = count($cfp->custom_record_types);
		?>
		
		<?php
			// let's parse the custom fields form table
			echo $cfp->parseTableForm(true);
		?>
		
		<script language="javascript">
		    var task_types;
		    
		    // We need to create an array of all the names
		    // of the record types in JS so we can map the Key to the type name (used in the field filter)
		    task_types = new Array(<?php echo $amount_task_record_types; ?>);
		    
		    <?php
		    	foreach($cfp->custom_record_types as $key => $record_type){
		    		echo "task_types[$key] = new String('".$record_type."');\n";
		    	}
		    	reset($cfp->custom_record_types);
		    	if(count($cfp->custom_record_types) == 0){
		    		$record_type = "";
		    	} else {
		    		$record_type = isset($cfp->custom_record_types[$obj->task_type]) ? $cfp->custom_record_types[$obj->task_type] : null;
		    		if(is_null($record_type)){
		    			$record_type = current($cfp->custom_record_types);
		    		}
		    	}
		    	
		    	$actual_record_type = str_replace(" ", "_", $record_type);
		    	
		    	// Let's parse all the show functions
		    	echo $cfp->parseShowFunctions();
		    ?>
		    
		    
			<?php echo $cfp->showHideAllRowsFunction(); ?>
			
			// by default hide everything and show the actual type record
			<?php echo "\n\nhideAllRows();";
			      if($actual_record_type != ""){
				      echo "show$actual_record_type();";
			      } 
			?>
		</script>
	</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="3" width="100%">
<tr>
	<td height="40" width="35%">
		* <?php echo $AppUI->_( 'requiredField' );?>
	</td>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?php echo $AppUI->_('taskCancel');?>')){location.href = '?<?php echo $AppUI->getPlace();?>';}" />
			</td>
			<td>
				<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save');?>" onClick="submitIt();" />
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="hassign" />
<input type="hidden" name="hdependencies" />
</form>

</body>
</html>
