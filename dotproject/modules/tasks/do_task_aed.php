<?php /* TASKS $Id$ */

function setItem($item_name, $defval = null) {
	if (isset($_POST[$item_name]))
		return $_POST[$item_name];
	if (isset($_SESSION['tasks_subform']) && isset($_SESSION['tasks_subform'][$item_name]))
		return $_SESSION['tasks_subform'][$item_name];
	return $defval;
}

$del = isset($_POST['del']) ? $_POST['del'] : 0;
$hassign = setItem('hassign');
$hperc_assign = setItem('hperc_assign');
$hdependencies = setItem('hdependencies');
$notify = setItem('task_notify', 0);
$comment = setItem('email_comment','');
$sub_form = isset($_POST['sub_form']) ? $_POST['sub_form'] : 0;

if ($sub_form) {
	// in add-edit, so set it to what it should be
	$AppUI->setState('TaskAeTabIdx', $_POST['newTab']);
	if (isset($_POST['subform_processor'])) {
		$root = $dPconfig['root_dir'];
		if (isset($_POST['subform_module']))
			$mod = $AppUI->checkFileName($_POST['subform_module']);
		else
			$mod = 'tasks';
		$proc = $AppUI->checkFileName($_POST['subform_processor']);
		include "$root/modules/$mod/$proc.php";
	} 
	if (! isset($_SESSION['tasks_subform']))
		$_SESSION['tasks_subform'] = array();
	$_SESSION['tasks_subform'] = array_merge($_SESSION['tasks_subform'], $_POST);

} else {

	// Include any files for handling module-specific requirements
	foreach (findTabModules('tasks', 'addedit') as $mod) {
		$fname = $GLOBALS['dPconfig']['root_dir'] . "/modules/$mod/tasks_dosql.addedit.php";
		dprint(__FILE__, __LINE__, 1, "checking for $fname");
		if (file_exists($fname))
			require_once $fname;
	}

	$obj = new CTask();

	// If we have an array of pre_save functions, perform them in turn.
	if (isset($pre_save)) {
		foreach ($pre_save as $pre_save_function)
			$pre_save_function();
	} else {
		dprint(__FILE__, __LINE__, 1, "No pre_save functions.");
	}

	if ( isset($_SESSION['tasks_subform'])) {
		$obj->bind($_SESSION['tasks_subform']);
		unset($_SESSION['tasks_subform']);
	}

	if (!$obj->bind( $_POST )) {
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		$AppUI->redirect();
	}

	// Check to see if the task_project has changed
	if (isset($_POST['new_task_project']))
		$obj->task_project = $_POST['new_task_project'];

	// Map task_dynamic checkboxes to task_dynamic values for task dependancies.
	if ( $obj->task_dynamic != 1 ) {
		$task_dynamic_delay = setItem("task_dynamic_nodelay", '0');
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
	$obj->task_departments = implode(",", setItem("dept_ids", array()));

	//Assign custom fields to task_custom for them to be saved
	$custom_fields = dPgetSysVal("TaskCustomFields");
	$custom_field_data = array();
	if ( count($custom_fields) > 0 ){
		foreach ( $custom_fields as $key => $array ) {
			$custom_field_data[$key] = setItem("custom_$key");
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
			$obj->updateAssigned( $hassign , $hperc_assign_ar);
		}
		
		if (isset($hdependencies)) {
			$obj->updateDependencies( $hdependencies );
		}
		
		// If there is a set of post_save functions, then we process them

		if (isset($post_save)) {
			foreach ($post_save as $post_save_function) {
				$post_save_function();
			}
		}

		if ($notify) {
			if ($msg = $obj->notify($comment)) {
				$AppUI->setMsg( $msg, UI_MSG_ERROR );
			}
		}
		
		$AppUI->redirect();
	}

} // end of if subform
?>
