<?php /* TASKS $Id$ */

$del = isset($_POST['del']) ? $_POST['del'] : 0;
$hassign = @$_POST['hassign'];
$hperc_assign = @$_POST['hperc_assign'];
$hdependencies = @$_POST['hdependencies'];
$notify = isset($_POST['task_notify']) ? $_POST['task_notify'] : 0;
$comment = isset($_POST['email_comment']) ? $_POST['email_comment'] : '';
$sant = isset($_POST['sant']) ? $_POST['sant'] : 0;

$obj = new CTask();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// Map task_dynamic checkboxes to task_dynamic values for task dependancies.
if ( $obj->task_dynamic != 1 ) {
	$task_dynamic_delay = dPgetParam($_POST, "task_dynamic_nodelay", '0');
	if (in_array($obj->task_dynamic, $tracking_dynamics)) {
		$obj->task_dynamic = $task_dynamic_delay ? 21 : 31;
	} else {
		$obj->task_dynamic = $task_dynamic_delay ? 11 : 0;
	}
}

//format hperc_assign user_id=percentage_assignment;user_id=percentage_assignment;user_id=percentage_assignment;
$tmp_ar = explode(";", $hperc_assign);
$hperc_assign_ar = array();
for ($i = 0; $i < sizeof($tmp_ar); $i++) {
	$tmp = explode("=", $tmp_ar[$i]);
	$hperc_assign_ar[$tmp[0]] = $tmp[1];
}

// let's check if there are some assigned departments to task
$obj->task_departments = implode(",", dPgetParam($_POST, "dept_ids", array()));

//Assign custom fields to task_custom for them to be saved
$custom_fields = dPgetSysVal("TaskCustomFields");
$custom_field_data = array();
if ( count($custom_fields) > 0 ){
	foreach ( $custom_fields as $key => $array ) {
		$custom_field_data[$key] = $_POST["custom_$key"];
	}
	$obj->task_custom = serialize($custom_field_data);
}

// convert dates to SQL format first
if ($obj->task_start_date) {
	$date = new CDate( $obj->task_start_date );
	$obj->task_start_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->task_end_date) {
	$date = new CDate( $obj->task_end_date );
	$obj->task_end_date = $date->format( FMT_DATETIME_MYSQL );
}

//echo '<pre>';print_r( $hassign );echo '</pre>';die;
// prepare (and translate) the module name ready for the suffix
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( $AppUI->_("Task deleted"));
		$AppUI->redirect( '', -1 );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect(); // Store failed don't continue?
	} else {
		$AppUI->setMsg( @$_POST['task_id'] ? 'Task updated' : 'Task added', UI_MSG_OK);
	}

	if (isset($hassign)) {
                // returns the userNames of the concerning users if OverAssignment detected, otherwise false
		$overAssignment = $obj->updateAssigned( $hassign , $hperc_assign_ar);
                //check if OverAssignment occured, database has not been updated in this case
                if ($overAssignment) {
                        $AppUI->setMsg( "The following Users have not been assigned in order to prevent from Over-Assignment:", UI_MSG_ERROR );
                        $AppUI->setMsg( "<br>".$overAssignment, UI_MSG_ERROR, true );
                }
	}
	
	if (isset($hdependencies)) {
		$obj->updateDependencies( $hdependencies );
	}
	
	if ($notify) {
		if ($msg = $obj->notify($comment)) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
		}
	}

	if ($sant == true) {
		// save and add new task
		$redTarget = 'm=tasks&a=addedit&task_project='.$obj->task_project.'&task_parent='.$obj->task_id;
	} else {
		// save and go back to default place
		$redTarget = NULL;
	}
	$AppUI->redirect( $redTarget );
}
?>
