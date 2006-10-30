<?php /* PROJECTS $Id$ */
$obj = new CProject();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

require_once($baseDir . '/classes/CustomFields.class.php');
// convert dates to SQL format first
if ($obj->project_start_date) {
	$date = new CDate();
	$date->setDate($obj->project_start_date . '000000', DATE_FORMAT_TIMESTAMP);
	$obj->project_start_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->project_end_date) {
	$date = new CDate();
	$date->setDate($obj->project_end_date . '235959', DATE_FORMAT_TIMESTAMP);
//	$date->setTime( 23, 59, 59 );
	$obj->project_end_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->project_actual_end_date) {
	$date = new CDate( $obj->project_actual_end_date );
	$obj->project_actual_end_date = $date->format( FMT_DATETIME_MYSQL );
}

// let's check if there are some assigned departments to project
if(!dPgetParam($_POST, 'project_departments', 0)){
	$obj->project_departments = implode(',', dPgetParam($_POST, 'dept_ids', array()));
}

$del = dPgetParam( $_POST, 'del', 0 );

// prepare (and translate) the module name ready for the suffix
if ($del) {
	$project_id = dPgetParam($_POST, 'project_id', 0);
	$canDelete = $obj->canDelete($msg, $project_id);
	if (!$canDelete) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'Project deleted', UI_MSG_ALERT);
		$AppUI->redirect( 'm=projects' );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['project_id'];
		
		if ( $importTask_projectId = dPgetParam( $_POST, 'import_tasks_from', '0' ) )
		{
			$import_date = dPgetParam( $_POST, 'project_import_date', '' );
			$import_end_date = dPgetParam( $_POST, 'project_import_end_date', '' );
			if (empty($import_date))
			{
				if (!empty($import_end_date))
				{
					$importProject = new CProject();
					$importProject->load($importTask_projectId);
					$date = new CDate($import_end_date);
					$date->addDays( 0 - $importProject->calcDuration() );
					//$reschedule = true;
					//$date = $importProject->calcMaxStartDate($import_end_date);
				}
				else
				{
					if (!empty($obj->project_start_date))
						$date = new CDate($obj->project_start_date);
					else
						$date = new CDate();
				}
				$import_date = $date->format(FMT_DATETIME_MYSQL);
			}
			$keepAssignees = dPgetParam( $_POST, 'keepAssignees', '' );
			$keepFiles = dPgetParam( $_POST, 'keepFiles', '' );
			$obj->importTasks ($importTask_projectId, $import_date, $keepAssignees, $keepFiles);

			// Import forums
			$keepForums = dPgetParam($_POST, 'keepForums', false);
			if ($keepForums)
			{
				$q = new DBQuery;
				$q->addTable('forums');
				$q->addQuery('*');
				$q->addWhere('forum_project = ' . $importTask_projectId);
				$forums = $q->loadList();
				foreach($forums as $forum)
				{
					$forum['forum_id'] = '';
					$forum['forum_project'] = $obj->project_id;
					$q->addInsert(array_keys($forum), array_values($forum), true);
					$q->addTable('forums');
					$q->exec();
					$q->clear();
				}
			}

			// Import project files (task files are already imported).
			if ($keepFiles)
			{
				if (!is_dir($dPconfig['root_dir'].'/files/'.$obj->project_id))
				{
					$res = mkdir( $dPconfig['root_dir'].'/files/'.$obj->project_id, 0777 );
					if (!$res) 
						$AppUI->setMsg( 'Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT );
       }


				$q = new DBQuery;
				$q->addTable('files');
				$q->addQuery('*');
				$q->addWhere('file_task = 0');
				$q->addWhere('file_project = ' . $importTask_projectId);
				$files = $q->loadList();
				foreach($files as $file)
				{
    			$res = copy($dPconfig['root_dir'].'/files/'.$file['file_project'].'/'.$file['file_real_filename'], $dPconfig['root_dir'].'/files/'.$obj->project_id.'/'.$file['file_real_filename']);
					$file['file_id'] = '';
					$file['file_project'] = $obj->project_id;
					$q->addInsert(array_keys($file), array_values($file), true);
					$q->addTable('files');
					$q->exec();
					$q->clear();
				}
			}
		}

 		$custom_fields = New CustomFields( $m, 'addedit', $obj->project_id, "edit" );
 		$custom_fields->bind( $_POST );
 		$sql = $custom_fields->store( $obj->project_id ); // Store Custom Fields

		$AppUI->setMsg( $isNotNew ? 'Project updated' : 'Project inserted', UI_MSG_OK);
	}
	$AppUI->redirect();
}
?>
