<?php /* TASKS $Id$ */

require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

// user based access
$task_access = array(
	'0'=>'Public',
	'1'=>'Protected',
	'2'=>'Participant',
	'3'=>'Private'
);

/*
 * TASK DYNAMIC VALUE:
 * 0  = default(OFF), no dep tracking of others, others do track
 * 1  = dynamic, umbrella task, no dep tracking, others do track
 * 11 = OFF, no dep tracking, others do not track
 * 21 = FEATURE, dep tracking, others do not track
 * 31 = ON, dep tracking, others do track
 */

// When calculating a task's start date only consider
// end dates of tasks with these dynamic values.
$tracked_dynamics = array(
        '0' => '0',
        '1' => '1',
        '2' => '31'
);
// Tasks with these dynamics have their dates updated when
// one of their dependencies changes. (They track dependencies)
$tracking_dynamics = array(
        '0' => '21',
        '1' => '31'
);

/*
* CTask Class
*/
class CTask extends CDpObject {
/** @var int */
	var $task_id = NULL;
/** @var string */
	var $task_name = NULL;
/** @var int */
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_owner = NULL;
	var $task_start_date = NULL;
	var $task_duration = NULL;
	var $task_duration_type = NULL;
/** @deprecated */
	var $task_hours_worked = NULL;
	var $task_end_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_percent_complete = NULL;
	var $task_description = NULL;
	var $task_target_budget = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;

	var $task_order = NULL;
	var $task_client_publish = NULL;
	var $task_dynamic = NULL;
	var $task_access = NULL;
	var $task_notify = NULL;
	var $task_departments = NULL;
	var $task_contacts = NULL;
	var $task_custom = NULL;
	var $task_type   = NULL;

	
	function CTask() {
		$this->CDpObject( 'tasks', 'task_id' );
	}

// overload check
	function check() {
		global $AppUI;
		
		if ($this->task_id === NULL)
			return 'task id is NULL';

	// ensure changes to checkboxes are honoured
		$this->task_milestone = intval( $this->task_milestone );
		$this->task_dynamic   = intval( $this->task_dynamic );
		
		$this->task_percent_complete = intval( $this->task_percent_complete );
	
		if (!$this->task_duration) {
			$this->task_duration = '0';
		}
		if (!$this->task_duration_type) {
			$this->task_duration_type = 1;
		}
		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}
		if (!$this->task_notify) {
			$this->task_notify = 0;
		}
		
		/*
		 * Check for bad or circular task relationships (dep or child-parent).
		 * These checks are definately not exhaustive it is still quite possible
		 * to get things in a knot.
		 * Note: some of these checks may be problematic and might have to be removed
		 */
		static $addedit;
		if (!isset($addedit))
			$addedit = dPgetParam($_POST, 'dosql', '') == 'do_task_aed' ? true : false;
		$this_dependencies = array();

		/*
		 * If we are called from addedit then we want to use the incoming
		 * list of dependencies and attempt to stop bad deps from being created
		 */
		if ($addedit) {
			$hdependencies = dPgetParam($_POST, 'hdependencies', '0');
			if ($hdependencies)
				$this_dependencies = explode(',', $hdependencies);
		} else {
			$this_dependencies = explode(',', $this->getDependencies());
		}
		// Set to false for recursive updateDynamic calls etc.
		$addedit = false;

		// Have deps
		if (array_sum($this_dependencies)) {

			if ( $this->task_dynamic == '1')
				return $AppUI->_('BadDep_DynNoDep');

			$this_dependants = $this->task_id ? explode(',', $this->dependantTasks()) : array();

			// If the dependants' have parents add them to list of dependants
			foreach ($this_dependants as $dependant) {
				$dependant_task = new CTask();
				$dependant_task->load($dependant);
				if ( $dependant_task->task_id != $dependant_task->task_parent )
					$more_dependants = explode(',', $this->dependantTasks($dependant_task->task_parent));
			}
			$this_dependants = array_merge($this_dependants, $more_dependants);

			// Task dependencies can not be dependant on this task
			$intersect = array_intersect( $this_dependencies, $this_dependants );
			if (array_sum($intersect)) {
				$ids = "(".implode(',', $intersect).")";
				return $AppUI->_('BadDep_CircularDep').$ids;
			}
		}

		// Has a parent
		if ( $this->task_id && $this->task_id != $this->task_parent ) {
			$this_children = $this->getChildren();
			$this_parent = new CTask();
			$this_parent->load($this->task_parent);
			$parents_dependants = explode(',', $this_parent->dependantTasks());

			if (in_array($this_parent->task_id, $this_dependencies))
				return $AppUI->_('BadDep_CannotDependOnParent');

			// Task parent cannot be child of this task
			if (in_array($this_parent->task_id, $this_children))
				return $AppUI->_('BadParent_CircularParent');

			if ( $this_parent->task_parent != $this_parent->task_id ) {

				// ... or parent's parent, cannot be child of this task. Could go on ...
				if (in_array($this_parent->task_parent, $this_children))
					return $AppUI->_('BadParent_CircularGrandParent')."(".$this_parent->task_parent.")";

				// parent's parent cannot be one of this task's dependencies
				if (in_array($this_parent->task_parent, $this_dependencies))
					return $AppUI->_('BadDep_CircularGrandParent')."(".$this_parent->task_parent.")";;

			} // grand parent

			if ( $this_parent->task_dynamic == '1' ) {
				$intersect = array_intersect( $this_dependencies, $parents_dependants );
				if (array_sum($intersect)) {
					$ids = "(".implode(',', $intersect).")";
					return $AppUI->_('BadDep_CircularDepOnParentDependant').$ids;
				}
			}

			if ( $this->task_dynamic == '1' ) {
				// then task's children can not be dependant on parent
				$intersect = array_intersect( $this_children, $parents_dependants );
				if (array_sum($intersect))
					return $AppUI->_('BadParent_ChildDepOnParent');
			}
		} // parent
		
		return NULL;
	}


	function updateDynamics( $fromChildren = false ) {
		//Has a parent or children, we will check if it is dynamic so that it's info is updated also
		
		$modified_task = new CTask();

		if ( $fromChildren ){
			$modified_task = &$this;
		} else {
			$modified_task->load($this->task_parent);
		}

		if ( $modified_task->task_dynamic == '1' ) {
			//Update allocated hours based on children
			$sql = "SELECT SUM( task_duration * task_duration_type ) from " . $this->_tbl . " WHERE task_parent = " . $modified_task->task_id .
					" and task_id != " . $modified_task->task_id . " GROUP BY task_parent;";
			$children_allocated_hours = (float) db_loadResult( $sql );
			if ( $modified_task->task_duration_type == 1 ) {
				$modified_task->task_duration = round($children_allocated_hours,2);
			} else {
				$modified_task->task_duration = round($children_allocated_hours / $modified_task->task_duration_type, 2);
			}

			//Update worked hours based on children
			$sql = "SELECT sum( task_log_hours ) FROM tasks, task_log
					WHERE task_id = task_log_task AND task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND task_dynamic != 1";
			$children_hours_worked = (float) db_loadResult( $sql );
			
			
			//Update worked hours based on dynamic children tasks
			$sql = "SELECT sum( task_hours_worked ) FROM tasks
					WHERE task_dynamic = 1 AND task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id;
			$children_hours_worked += (float) db_loadResult( $sql );
			
			$modified_task->task_hours_worked = $children_hours_worked;
					
			//Update percent complete
			$sql = "SELECT sum(task_percent_complete * task_duration * task_duration_type )
					FROM tasks WHERE task_parent = " . $modified_task->task_id . 
					" AND task_id != " . $modified_task->task_id;
			$real_children_hours_worked = (float) db_loadResult( $sql );
			
			$total_hours_allocated = (float)($modified_task->task_duration * $modified_task->task_duration_type);
			if($total_hours_allocated > 0){
			    $modified_task->task_percent_complete = $real_children_hours_worked / $total_hours_allocated;
			} else {
			    $sql = "SELECT avg(task_percent_complete)
    					FROM tasks WHERE task_parent = " . $modified_task->task_id . 
    					" AND task_id != " . $modified_task->task_id;
			    $modified_task->task_percent_complete = db_loadResult($sql);
			}


			//Update start date
			$sql = "SELECT min( task_start_date ) FROM tasks
					WHERE task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND ! isnull( task_start_date ) AND task_start_date !=  '0000-00-00 00:00:00'";
			$modified_task->task_start_date = db_loadResult( $sql );

			//Update end date
			$sql = "SELECT max( task_end_date ) FROM tasks
					WHERE task_parent = " . $modified_task->task_id .
					" AND task_id != " . $modified_task->task_id .
					" AND ! isnull( task_end_date ) AND task_end_date !=  '0000-00-00 00:00:00'";
			$modified_task->task_end_date = db_loadResult( $sql );

			//If we are updating a dynamic task from its children we don't want to store() it
			//when the method exists the next line in the store calling function will do that
			if ( $fromChildren == false ) $modified_task->store();
		}
	}

/**
*	Copy the current task
*
*	@author	handco <handco@users.sourceforge.net>
*	@param	int		id of the destination project
*	@return	object	The new record object or null if error
**/
	function copy($destProject_id = 0) {
		$newObj = $this->duplicate();

		// Copy this task to another project if it's specified
		if ($destProject_id != 0)
			$newObj->task_project = $destProject_id;

		if ($newObj->task_parent == $this->task_id)
			$newObj->task_parent = '';

		$newObj->store();

		return $newObj;
	}// end of copy()

/**
* @todo Parent store could be partially used
*/
	function store() {
		GLOBAL $AppUI;

		$importing_tasks = false;
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}
		if( $this->task_id ) {
                addHistory('tasks', $this->task_id, 'update', $this->task_name, $this->task_project);
			$this->_action = 'updated';
			// Load the old task from disk
			$oTsk = new CTask();
			$oTsk->load ($this->task_id);

			// if task_status changed, then update subtasks
			if ($this->task_status != $oTsk->task_status)
				$this->updateSubTasksStatus($this->task_status);
			
			// Moving this task to another project?
			if ($this->task_project != $oTsk->task_project)
				$this->updateSubTasksProject($this->task_project);
			
			if ( $this->task_dynamic == '1' )
				$this->updateDynamics(true);

			// shiftDependantTasks needs this done first
			$ret = db_updateObject( 'tasks', $this, 'task_id', false );

			// Milestone or task end date, or dynamic status has changed,
			// shift the dates of the tasks that depend on this task
			if (($this->task_end_date != $oTsk->task_end_date) ||
			    ($this->task_dynamic != $oTsk->task_dynamic)   ||
			    ($this->task_milestone == '1')) {
				$this->shiftDependantTasks();
			}
		} else {
			$this->_action = 'added';
			$ret = db_insertObject( 'tasks', $this, 'task_id' );
                        addHistory('tasks', $this->task_id, 'add', $this->task_name, $this->task_project);

			if (!$this->task_parent) {
				$sql = "UPDATE tasks SET task_parent = $this->task_id WHERE task_id = $this->task_id";
				db_exec( $sql );
			} else {
				// importing tasks do not update dynamics
				$importing_tasks = true;
			}

			// insert entry in user tasks
			$sql = "INSERT INTO user_tasks (user_id, task_id, user_type) VALUES ($AppUI->user_id, $this->task_id, -1)";
			db_exec( $sql );
		}
		
		//split out related departments and store them seperatly.
		$sql = 'DELETE FROM task_departments WHERE task_id='.$this->task_id;
		db_exec( $sql );
		print_r($this->task_departments);
		if(!is_null($this->task_departments)){
		  $departments = explode(',',$this->task_departments);
    	  foreach($departments as $department){
    		   $sql = 'INSERT INTO task_departments (task_id, department_id) values ('.$this->task_id.', '.$department.')';
    		   db_exec( $sql );
    	  }
		}
		
		//split out related contacts and store them seperatly.
		$sql = 'DELETE FROM task_contacts WHERE task_id='.$this->task_id;
		db_exec( $sql );
		if(!is_null($this->task_contacts)){
    		$contacts = explode(',',$this->task_contacts);
    		foreach($contacts as $contact){
    			$sql = 'INSERT INTO task_contacts (task_id, contact_id) values ('.$this->task_id.', '.$contact.')';
    			db_exec( $sql );
    		}
		}

		if ( !$importing_tasks && $this->task_parent != $this->task_id )
			$this->updateDynamics();

		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

/**
* @todo Parent store could be partially used
* @todo Can't delete a task with children
*/
	function delete() {
		$this->_action = 'deleted';
	// delete linked user tasks
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}

		//load it before deleting it because we need info on it to update the parents later on
		$this->load($this->task_id);
                addHistory('tasks', $this->task_id, 'delete', $this->task_name, $this->task_project);
		
		// delete the tasks...what about orphans?
		// delete task with parent is this task
		
		$sql = "DELETE FROM tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			if ( $this->task_parent != $this->task_id ){
				// Has parent, run the update sequence, this child will no longer be in the
				// database
				$this->updateDynamics();
			}
		}

		$sql = "SELECT * FROM tasks WHERE task_parent = $this->task_id";
		$children_taks = db_loadHashList($sql, "task_id");
		
		if(count($children_taks) > 0){
		     $sql = "DELETE FROM tasks WHERE task_parent = $this->task_id";
                    	     if (!db_exec( $sql )) {
			return db_error();
		      }else{
		          $this->_action ='deleted whit childs';
		      }
		}
	
		 return NULL;
	}

	function updateDependencies( $cslist ) {
	// delete all current entries
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
		db_exec( $sql );

	// process dependencies
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $task_id) {
			if (intval( $task_id ) > 0) {
				$sql = "REPLACE INTO task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($this->task_id, $task_id)";				
				db_exec($sql);
			}
		}
	}
	
	/**
	*	Retrieve the tasks dependencies 
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@return	string	comma delimited list of tasks id's
	**/
	function getDependencies () {
		// Call the static method for this object
		$result = $this->staticGetDependencies ($this->task_id);
		return $result;
	} // end of getDependencies ()

	//}}}

	//{{{ staticGetDependencies ()
	/**
	*	Retrieve the tasks dependencies
	*
	*	@author	handco	<handco@users.sourceforge.net>
	*	@param	integer	ID of the task we want dependencies
	*	@return	string	comma delimited list of tasks id's
	**/
	function staticGetDependencies ($taskId) {
		$sql = "
            SELECT dependencies_req_task_id
            FROM task_dependencies td
            WHERE td.dependencies_task_id = $taskId
		";
		$list = db_loadColumn ($sql);
		$result = $list ? implode (',', $list) : '';

		return $result;
	} // end of staticGetDependencies ()

	//}}}

	function notifyOwner() {
		GLOBAL $AppUI, $dPconfig, $locale_char_set;
		
		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;

		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\ncc.contact_email as creator_email,"
		."\ncc.contact_first_name as creator_first_name,"
		."\ncc.contact_last_name as creator_last_name,"
		."\noc.contact_email as owner_email,"
		."\noc.contact_first_name as owner_first_name,"
		."\noc.contact_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\nac.contact_email as assignee_email,"
		."\nac.contact_first_name as assignee_first_name,"
		."\nac.contact_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
                ."\nLEFT JOIN contacts oc ON oc.contact_id = o.user_contact" 
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
                ."\nLEFT JOIN contacts cc ON cc.contact_id = c.user_contact" 
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
                ."\nLEFT JOIN contacts ac ON ac.contact_id = a.user_contact" 
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			$body .= "\n".$AppUI->_('URL').":     {$dPconfig['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			$body .= "\n\n" . $AppUI->_('Creator').":" . $AppUI->user_first_name . " " . $AppUI->user_last_name;
		
			$body .= "\n\n" . $AppUI->_('Progress') . ": " . $this->task_percent_complete . "%";
			$body .= "\n\n" . dPgetParam($_POST, "task_log_description");
			
			
			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}
		
		if ($mail->ValidEmail($users[0]['owner_email'])) {
			$mail->To( $users[0]['owner_email'], true );
			$mail->Send();
		}
		
		return '';
	}
	
	//additional comment will be included in email body 
	function notify( $comment = '' ) {
		GLOBAL $AppUI, $dPconfig, $locale_char_set;
		$df = $AppUI->getPref('SHDATEFORMAT');
		$df .= " " . $AppUI->getPref('TIMEFORMAT');

		$sql = "SELECT project_name FROM projects WHERE project_id=$this->task_project";
		$projname = db_loadResult( $sql );

		$mail = new Mail;
		
		$mail->Subject( "$projname::$this->task_name ".$AppUI->_($this->_action), $locale_char_set);

	// c = creator
	// a = assignee
	// o = owner
		$sql = "SELECT t.task_id,"
		."\ncc.contact_email as creator_email,"
		."\ncc.contact_first_name as creator_first_name,"
		."\ncc.contact_last_name as creator_last_name,"
		."\noc.contact_email as owner_email,"
		."\noc.contact_first_name as owner_first_name,"
		."\noc.contact_last_name as owner_last_name,"
		."\na.user_id as assignee_id,"
		."\nac.contact_email as assignee_email,"
		."\nac.contact_first_name as assignee_first_name,"
		."\nac.contact_last_name as assignee_last_name"
		."\nFROM tasks t"
		."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
		."\nLEFT JOIN users o ON o.user_id = t.task_owner"
                ."\nLEFT JOIN contacts oc ON oc.contact_id = o.user_contact"
		."\nLEFT JOIN users c ON c.user_id = t.task_creator"
                ."\nLEFT JOIN contacts cc ON cc.contact_id = c.user_contact"
		."\nLEFT JOIN users a ON a.user_id = u.user_id"
                ."\nLEFT JOIN contacts ac ON ac.contact_id = a.user_contact"
		."\nWHERE t.task_id = $this->task_id";
		$users = db_loadList( $sql );

		if (count( $users )) {
			$task_start_date       = new CDate($this->task_start_date);
			$task_finish_date      = new CDate($this->task_end_date);
			
			$body = $AppUI->_('Project').": $projname";
			$body .= "\n".$AppUI->_('Task').":    $this->task_name";
			//Priority not working for some reason, will wait till later
			//$body .= "\n".$AppUI->_('Priority'). ": $this->task_priority";
			$body .= "\n".$AppUI->_('Start Date') . ": " . $task_start_date->format( $df );
			$body .= "\n".$AppUI->_('Finish Date') . ": " . $task_finish_date->format( $df );
			$body .= "\n".$AppUI->_('URL').":     {$dPconfig['base_url']}/index.php?m=tasks&a=view&task_id=$this->task_id";
			$body .= "\n\n" . $AppUI->_('Description') . ":"
				. "\n$this->task_description";
			if ($users[0]['creator_email']) {
				$body .= "\n\n" . $AppUI->_('Creator').":"
					. "\n" . $users[0]['creator_first_name'] . " " . $users[0]['creator_last_name' ]
					. ", " . $users[0]['creator_email'];
			}
			$body .= "\n\n" . $AppUI->_('Owner').":"
				. "\n" . $users[0]['owner_first_name'] . " " . $users[0]['owner_last_name' ]
				. ", " . $users[0]['owner_email'];

			if ($comment != '') {
				$body .= "\n\n".$comment;
			}
			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name 
				. '" <' . $AppUI->user_email . '>'
			);
		}

		foreach ($users as $row) {
			if ($row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To( $row['assignee_email'], true );
					$mail->Send();
				}
			}
		}
		return '';
	}
/**
* @param Date Start date of the period
* @param Date End date of the period
* @param integer The target company
*/
	function getTasksForPeriod( $start_date, $end_date, $company_id=0 ) {
		GLOBAL $AppUI;
	// convert to default db time stamp
		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );
		
		// filter tasks for not allowed projects
		$tasks_filter = '';
		$join = winnow('projects', 'task_project', $tasks_filter);

	// assemble where clause
		$where = "task_project = project_id"
			. "\n\tAND ("
			. "\n\t\t(task_start_date <= '$db_end' AND task_end_date >= '$db_start')"
			. "\n\t\tOR task_start_date BETWEEN '$db_start' AND '$db_end'"
			. "\n\t)"
		    . "\n\tAND ($tasks_filter)";
	/*
			OR
			task_end_date BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_end'
			OR
			(DATE_ADD(task_start_date, INTERVAL task_duration DAY)) BETWEEN '$db_start' AND '$db_end'
	*/
		$where .= $company_id ? "\n\tAND project_company = $company_id" : '';

	// exclude read denied projects
		$obj = new CProject();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

	// get any specifically denied tasks
		$obj = new CTask();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_id NOT IN (" . implode( ',', $deny ) . ')' : '';

	// assemble query
		$sql = "SELECT task_name, task_id, task_start_date, task_end_date,"
			. "\n\ttask_duration, task_duration_type,"
			. "\n\tproject_color_identifier AS color,"
			. "\n\tproject_name"
			. "\nFROM tasks,projects"
		    . "\n$join"
			. "\nWHERE $where"
			. "\nORDER BY task_start_date";
//echo "<pre>$sql</pre>";
	// execute and return
		return db_loadList( $sql );
	}

	function canAccess( $user_id ) {
		//echo intval($this->task_access);
		switch ($this->task_access) {
			case 0:
				// public
				return true;
				break;
			case 1:
				// protected
				$sql = "SELECT user_company FROM users WHERE user_id=$user_id";
				$user_company = db_loadResult( $sql );
				$sql = "SELECT user_company FROM users WHERE user_id=$this->task_owner";
				$owner_company = db_loadResult( $sql );
				//echo "$user_company,$owner_company";die;

				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return (($owner_company == $user_company && $count > 0) || $this->task_owner == $user_id);
				break;
			case 2:
				// participant
				$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
				$count = db_loadResult( $sql );
				return ($count > 0 || $this->task_owner == $user_id);
				break;
			case 3:
				// private
				return ($this->task_owner == $user_id);
				break;
		}
	}

	/**
	*       retrieve tasks are dependant of another.
	*       @param  integer         ID of the master task
	*       @param  boolean         true if is a dep call (recurse call)
	*       @param  boolean         false for no recursion (needed for calc_end_date)
	**/
	function dependantTasks ($taskId = false, $isDep = false, $recurse = true) {
		static $aDeps = false;

		// Initialize the dependencies array
		if (($taskId == false) && ($isDep == false))
			$aDeps = array();

		// retrieve dependants tasks 
		if (!$taskId)
			$taskId = $this->task_id;

		$sql = "
			SELECT dependencies_task_id
			FROM task_dependencies AS td, tasks AS t
			WHERE td.dependencies_req_task_id = $taskId
			AND td.dependencies_task_id = t.task_id
		";
		// AND t.task_dynamic != 1   dynamics are not updated but they are considered

		$aBuf = db_loadColumn($sql);
		$aBuf = !empty($aBuf) ? $aBuf : array();
		//$aBuf = array_values(db_loadColumn ($sql));

		if ($recurse) {
			// recurse to find sub dependants
			foreach ($aBuf as $depId) {
				// work around for infinite loop
				if (!in_array($depId, $aDeps)) {
					$aDeps[] = $depId;
					$this->dependantTasks ($depId, true);
				}
			}

		} else {
			$aDeps = $aBuf;
		}

		// return if we are in a dependency call
		if ($isDep)
			return;
                       
		return implode (',', $aDeps);

	} // end of dependantTasks()

	/*
	 *       shift dependants tasks dates
	 *       @param  integer         time offset in seconds 
	 *       @return void
	 */
	function shiftDependantTasks () {
		// Get tasks that depend on this task
		$csDeps = explode( ",", $this->dependantTasks('','',false));

		if ($csDeps[0] == '')
			return;

		// Stage 1: Update dependant task dates (accounting for working hours)
		foreach( $csDeps as $task_id )
			$this->update_dep_dates( $task_id );

		// Stage 2: Now shift the dependant tasks' dependants
		foreach( $csDeps as $task_id ) {
			$newTask = new CTask();
			$newTask->load($task_id);
			$newTask->shiftDependantTasks();
		}
		return;

	} // end of shiftDependantTasks()

	/*
	 *	Update this task's dates in the DB.
	 *	start date: 	based on max dependency end date
	 *	end date:   	based on start date + working duration
	 *
	 *	@param		integer task_id of task to update
	 */
	function update_dep_dates( $task_id ) {
		GLOBAL $tracking_dynamics;

		$destDate = new CDate();
		$newTask = new CTask();

		$newTask->load($task_id);

		// Do not update tasks that are not tracking dependencies
		if (!in_array($newTask->task_dynamic, $tracking_dynamics))
			return;

		// start date, based on maximal dep end date
		$destDate->setDate( $this->get_deps_max_end_date( $newTask ) );
		$destDate = $this->next_working_day( $destDate );
		$new_start_date = $destDate->format( FMT_DATETIME_MYSQL );

		// end date, based on start date and work duration
		$newTask->task_start_date = $new_start_date;
		$newTask->calc_task_end_date();
		$new_end_date = $newTask->task_end_date;

		$sql = "UPDATE tasks
		SET
				task_start_date = '$new_start_date',
				task_end_date = '$new_end_date'
			WHERE 	task_dynamic != '1' AND task_id = $task_id
		";

		db_exec( $sql );

		if ( $newTask->task_parent != $newTask->task_id )
			$newTask->updateDynamics();
		return;
	}

	// Return date obj for the start of next working day
	function next_working_day( $dateObj ) {
		global $AppUI;
		while ( ! $dateObj->isWorkingDay() || $dateObj->getHour() >= dPgetConfig( 'cal_day_end' ) ) {
			$dateObj->addDays(1);
			$dateObj->setTime(dPgetConfig( 'cal_day_start' ), '0', '0');
		}
		return $dateObj;
	}
	// Return date obj for the end of the previous working day
	function prev_working_day( $dateObj ) {
		global $AppUI;
		while ( ! $dateObj->isWorkingDay() || ( $dateObj->getHour() < dPgetConfig( 'cal_day_start' ) ) ||
	      		( $dateObj->getHour() == dPgetConfig( 'cal_day_start' ) && $dateObj->getMinute() == '0' ) ) {
			$dateObj->addDays(-1);
			$dateObj->setTime(dPgetConfig( 'cal_day_end' ), '0', '0');
		}
		return $dateObj;
	}

	/*

	 Get the last end date of all of this task's dependencies

	 @param Task object
	 returns FMT_DATETIME_MYSQL date

	 */

	function get_deps_max_end_date( $taskObj ) {
		global $tracked_dynamics;

		$deps = $taskObj->getDependencies();
		$obj = new CTask();

		// Don't respect end dates of excluded tasks
		if ($tracked_dynamics) {
			$track_these = implode(',', $tracked_dynamics);
			$sql = "SELECT MAX(task_end_date) FROM tasks
				WHERE task_id IN ($deps) AND task_dynamic IN ($track_these)";
		}

		$last_end_date = db_loadResult( $sql );

		if ( !$last_end_date ) {
			// Set to project start date
			$id = $taskObj->task_project;
			$sql = "SELECT project_start_date FROM projects
				WHERE project_id = $id";
			$last_end_date = db_loadResult( $sql );
		}

		return $last_end_date;
	}

	/*
	* Calculate this task obj's end date. Based on start date
	* and the task duration and duration type.
	*/
	function calc_task_end_date() {
		$e = $this->calc_end_date( $this->task_start_date, $this->task_duration, $this->task_duration_type );
		$this->task_end_date = $e->format( FMT_DATETIME_MYSQL );
	}

	/*

	 Calculate end date given start date and work time.
	 Accounting for (non)working days and working hours.

	 @param date obj or mysql time - start date
	 @param int - number
	 @param int - durnType 24=days, 1=hours
	 returns date obj

	*/

	function calc_end_date( $start_date=null, $durn='8', $durnType='1' ) {
		GLOBAL $AppUI;
	
		$cal_day_start = dPgetConfig( 'cal_day_start' );
		$cal_day_end = dPgetConfig( 'cal_day_end' );
		$daily_working_hours = dPgetConfig( 'daily_working_hours' );

		$s = new CDate( $start_date );
		$e = $s;
		$inc = $durn;
		$full_working_days = 0;
		$hours_to_add_to_last_day = 0;
		$hours_to_add_to_first_day = $durn;

		// Calc the end date
		if ( $durnType == 24 ) { // Units are full days

			$full_working_days = ceil($durn);
			for ( $i = 0 ; $i < $full_working_days ; $i++ ) {
				$e->addDays(1);
				$e->setTime(dPgetConfig( 'cal_day_start' ), '0', '0');
				if ( !$e->isWorkingDay() )
					$full_working_days++;
			}
			$e->setHour( $s->getHour() );

		} else {  // Units are hours

			// First partial day
			if (( $s->getHour() + $inc ) > $cal_day_end ) {
				// Account hours for partial work day
				$hours_to_add_to_first_day = $cal_day_end - $s->getHour();	
				if ( $hours_to_add_to_first_day > $daily_working_hours )
					$hours_to_add_to_first_day = $daily_working_hours;
				$inc -= $hours_to_add_to_first_day;
				$hours_to_add_to_last_day = $inc % $daily_working_hours;
				// number of full working days remaining
				$full_working_days = round(($inc - $hours_to_add_to_last_day) / $daily_working_hours);

				if ( $hours_to_add_to_first_day != 0 ) {	
					while (1) {
						// Move on to the next workday
						$e->addDays(1);
						$e->setTime(dPgetConfig( 'cal_day_start' ), '0', '0');
						if ( $e->isWorkingDay() )
							break;
					}
				}
			} else {
				// less than one day's work, update the hour and be done..
				$e->setHour( $e->getHour() + $hours_to_add_to_first_day );
			}

			// Full days
			for ( $i = 0 ; $i < $full_working_days ; $i++ ) {
				$e->addDays(1);
				$e->setTime(dPgetConfig( 'cal_day_start' ), '0', '0');
				if ( !$e->isWorkingDay() )
					$full_working_days++;
			}
			// Last partial day
			if ( !($full_working_days == 0 && $hours_to_add_to_last_day == 0) )
				$e->setHour( $cal_day_start + $hours_to_add_to_last_day );

		}
		// Go to start of prev work day if current work day hasn't begun
		if ( $durn != 0 )
			$e = $this->prev_working_day( $e );

		return $e;

	} // End of calc_end_date

	/**
	* Function that returns the amount of hours this
	* task consumes per user each day
	*/
	function getTaskDurationPerDay(){
		$duration              = $this->task_duration*$this->task_duration_type;
		$task_start_date       = new CDate($this->task_start_date);
		$task_finish_date      = new CDate($this->task_end_date);
		$number_assigned_users = count($this->getAssignedUsers());
		
		$day_diff              = $task_finish_date->dateDiff($task_start_date);
		$number_of_days_worked = 0;
		$actual_date           = $task_start_date;

		for($i=0; $i<=$day_diff; $i++){
			if($actual_date->isWorkingDay()){
				$number_of_days_worked++;
			}
			$actual_date->addDays(1);
		}
		// May be it was a Sunday task
		if($number_of_days_worked == 0) $number_of_days_worked = 1;
		if($number_assigned_users == 0) $number_assigned_users = 1;
		return ($duration/$number_assigned_users) / $number_of_days_worked;
	}

         // unassign a user from task
	function removeAssigned( $user_id ) {
	// delete all current entries
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id AND user_id = $user_id";
		db_exec( $sql );

	}

	//using user allocation percentage ($perc_assign)
        // @return      returns the Names of the concerned Users if there occured an overAssignment, otherwise false
	function updateAssigned( $cslist, $perc_assign, $del=true, $rmUsers=false ) {

        // process assignees
		$tarr = explode( ",", $cslist );

	        // delete all current entries from $cslist
                if ($del == true && $rmUsers == true) {
                        foreach ($tarr as $user_id) {
                                $sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id
                                        AND user_id = $user_id";
                                db_exec( $sql );
                        }

                         return false;

                } else if ($del == true) {      // delete all on this task for a hand-over of the task
                        $sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
                        db_exec( $sql );
                }


                // get Allocation info in order to check if overAssignment occurs
                $alloc = $this->getAllocation("user_id");
                $overAssignment = false;

		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
				$perc = $perc_assign[$user_id];
                                // overAssignment check
                                if ($perc > $alloc[$user_id]['freeCapacity']) {
                                        // add Username of the overAssigned User
                                        $overAssignment .= " ".$alloc[$user_id]['userFC'];
                                } else {
                                        $sql = "REPLACE INTO user_tasks (user_id, task_id, perc_assignment) VALUES ($user_id, $this->task_id, $perc)";
                                        db_exec( $sql );
                                }
			}
		}
                return $overAssignment;
	}

	function getAssignedUsers(){
		$sql = "select u.*, ut.perc_assignment, ut.user_task_priority
		        from users as u, user_tasks as ut
		        where ut.task_id = '$this->task_id'
		              and ut.user_id = u.user_id";
		return db_loadHashList($sql, "user_id");
	}

        /**
        *  Calculate the extent of utilization of user assignments
        *  @param string hash   a hash for the returned hashList
        *  @param array users   an array of user_ids calculating their assignment capacity
        *  @return array        returns hashList of extent of utilization for assignment of the users
        */
        function getAllocation( $hash = NULL, $users = NULL ) {
                // use userlist if available otherwise pull data for all users
                $where = !empty($users) ? 'WHERE u.user_id IN ('.implode(",", $users).') ' : '';
                // retrieve the systemwide default preference for the assignment maximum
                $sql = "SELECT pref_value FROM user_preferences WHERE pref_user = 0 AND pref_name = 'TASKASSIGNMAX'";
                $result = db_loadHash($sql, $sysChargeMax);
                $scm = $sysChargeMax['pref_value'];
                // provide actual assignment charge, individual chargeMax and freeCapacity of users' assignments to tasks
                $sql = "SELECT u.user_id,
                        CONCAT(CONCAT_WS(' [', CONCAT_WS(' ',contact_first_name,contact_last_name), IF(IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_assignment)),up.pref_value)>0,IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_assignment)),up.pref_value),0)), '%]') AS userFC,
                        IFNULL(SUM(ut.perc_assignment),0) AS charge, u.user_username,
                        IFNULL(up.pref_value,$scm) AS chargeMax,
                        IF(IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_assignment)),up.pref_value)>0,IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_assignment)),up.pref_value),0) AS freeCapacity
                        FROM users u
                        LEFT JOIN contacts ON contact_id = user_contact
                        LEFT JOIN user_tasks ut ON ut.user_id = u.user_id
                        LEFT JOIN user_preferences up ON (up.pref_user = u.user_id AND up.pref_name = 'TASKASSIGNMAX')".$where."
                        GROUP BY u.user_id
                        ORDER BY contact_last_name, contact_first_name";
//               echo "<pre>$sql</pre>";
                return db_loadHashList($sql, $hash);
        }

 	function getUserSpecificTaskPriority( $user_id = 0, $task_id = NULL ) {
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;
		$sql = "SELECT user_task_priority FROM user_tasks WHERE user_id = $user_id AND task_id = $task_id";
		$prio = db_loadHash($sql, $priority);
		return $prio ? $priority['user_task_priority'] : NULL;
	}
	
	function updateUserSpecificTaskPriority( $user_task_priority = 0, $user_id = 0, $task_id = NULL ) {
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;
		$sql = "REPLACE INTO user_tasks (user_id, task_id, user_task_priority) VALUES ($user_id, $task_id, $user_task_priority)";
		db_exec( $sql );
	}

    function getProject() {
     $sql = "SELECT project_name, project_short_name, project_color_identifier FROM projects WHERE project_id = '$this->task_project'";
     $proj = db_loadHash($sql, $projects);
     return $projects;
    }

	//Returns task children IDs
	function getChildren() {
		$sql = "select task_id from tasks where task_id != '$this->task_id'
				and task_parent = '$this->task_id'";
		return db_loadList($sql);
	}


	/**
	* This function, recursively, updates all tasks status
	* to the one passed as parameter
	*/
	function updateSubTasksStatus($new_status, $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}
		
		// get children
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";
		
		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;
		
		// update status of children
		$sql = "update tasks set task_status = '$new_status' where task_parent = '$task_id'";

		db_exec($sql);
		// update status of children's children
		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksStatus($new_status, $id);
			}
		}
	}

	/**
	* This function recursively updates all tasks project
	* to the one passed as parameter
	*/ 
	function updateSubTasksProject($new_project , $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";
		
		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;
		
		$sql = "update tasks set task_project = '$new_project' where task_parent = '$task_id'";
		db_exec($sql);

		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksProject($new_project, $id);
			}
		}
	}
	
	function canUserEditTimeInformation(){
		global $dPconfig, $AppUI;

		$project = new CProject();
		$project->load( $this->task_project );
		
		// Code to see if the current user is
		// enabled to change time information related to task
		$can_edit_time_information = false;
		// Let's see if all users are able to edit task time information
		if(isset($dPconfig['restrict_task_time_editing']) && $dPconfig['restrict_task_time_editing']==true && $this->task_id > 0){
		
			// Am I the task owner?
			if($this->task_owner == $AppUI->user_id){
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
			
		} else if (!isset($dPconfig['restrict_task_time_editing']) || $dPconfig['restrict_task_time_editing']==false || $this->task_id == 0) { // If all users are able, then don't check anything
			$can_edit_time_information = true;
		}
		return $can_edit_time_information;
	}
}


/**
* CTask Class
*/
class CTaskLog extends CDpObject {
	var $task_log_id = NULL;
	var $task_log_task = NULL;
	var $task_log_name = NULL;
	var $task_log_description = NULL;
	var $task_log_creator = NULL;
	var $task_log_hours = NULL;
	var $task_log_date = NULL;
	var $task_log_costcode = NULL;
        var $task_log_problem = NULL;
        var $task_log_reference = NULL;
        var $task_log_related_url = NULL;

	function CTaskLog() {
		$this->CDpObject( 'task_log', 'task_log_id' );

                // ensure changes to checkboxes are honoured
                $this->task_log_problem = intval( $this->task_log_problem );
	}

// overload check method
	function check() {
		$this->task_log_hours = (float) $this->task_log_hours;
		return NULL;
	}
}
?>
