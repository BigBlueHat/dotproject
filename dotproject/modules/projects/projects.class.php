<?php /* PROJECTS $Id$ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision$
*/

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'companies' ) );

/**
 * The Project Class
 */
class CProject extends CDpObject {
	var $project_id = NULL;
	var $project_company = NULL;
	var $project_department = NULL;
	var $project_name = NULL;
	var $project_short_name = NULL;
	var $project_owner = NULL;
	var $project_url = NULL;
	var $project_demo_url = NULL;
	var $project_start_date = NULL;
	var $project_end_date = NULL;
	var $project_actual_end_date = NULL;
	var $project_status = NULL;
	var $project_percent_complete = NULL;
	var $project_color_identifier = NULL;
	var $project_description = NULL;
	var $project_target_budget = NULL;
	var $project_actual_budget = NULL;
	var $project_creator = NULL;
	var $project_active = NULL;
	var $project_private = NULL;
	var $project_departments= NULL;
	var $project_contacts = NULL;
	var $project_priority = NULL;
	var $project_type = NULL;

	function CProject() {
		$this->CDpObject( 'projects', 'project_id' );
	}

	function check() {
	// ensure changes of state in checkboxes is captured
		$this->project_active = intval( $this->project_active );
		$this->project_private = intval( $this->project_private );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		// TODO: check if user permissions are considered when deleting a project
		return true;
		
		// NOTE: I uncommented the dependencies check since it is
		// very anoying having to delete all tasks before being able
		// to delete a project.
		
		/*		
		$tables[] = array( 'label' => 'Tasks', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_project' );
		// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables );
		*/
	}

	function delete() {
		$sql = "SELECT task_id FROM tasks WHERE task_project = $this->project_id";
		$tasks_to_delete = db_loadColumn ( $sql );
		foreach ( $tasks_to_delete as $task_id ) {
			db_delete( 'user_tasks', 'task_id', $task_id );
			db_delete( 'task_dependencies', 'dependencies_req_task_id', $task_id );
		}
		db_delete( 'tasks', 'task_project', $this->project_id );
                if (!db_delete( 'projects', 'project_id', $this->project_id )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	/**	Import tasks from another project
	*
	*	@param	int		Project ID of the tasks come from.
	*	@return	bool	
	**/
	function importTasks ($from_project_id) {
		
		// Load the original
		$origProject = new CProject ();
		$origProject->load ($from_project_id);
		$sql = "SELECT task_id FROM tasks WHERE task_project = $from_project_id";

		$tasks = array_flip(db_loadColumn ($sql));

		$origDate = new CDate( $origProject->project_start_date );
		
		$destDate = new CDate ($this->project_start_date);
		
		$timeOffset = $destDate->getTime() - $origDate->getTime();

		$objTask = new CTask();
		
		// Dependencies array
		$deps = array();
		
		// Copy each task into this project and get their deps
		foreach ($tasks as $orig => $void) {
			$objTask->load ($orig);
			$destTask = $objTask->copy($this->project_id);
			$tasks[$orig] = $destTask;
			$deps[$orig] = $objTask->getDependencies ();
		}

		// Fix record integrity 
		foreach ($tasks as $old_id => $newTask) {

			// Fix parent Task
			// This task had a parent task, adjust it to new parent task_id
			if ($newTask->task_id != $newTask->task_parent)
				$newTask->task_parent = $tasks[$newTask->task_parent]->task_id;

			// Fix task start date from project start date offset
			$origDate->setDate ($newTask->task_start_date);
			$destDate->setDate ($origDate->getTime() + $timeOffset , DATE_FORMAT_UNIXTIME ); 
			$destDate = $newTask->next_working_day( $destDate );
			$newTask->task_start_date = $destDate->format(FMT_DATETIME_MYSQL);   
			
			// Fix task end date from start date + work duration
			$newTask->calc_task_end_date();
			
			// Dependencies
			if (!empty($deps[$old_id])) {
				$oldDeps = explode (',', $deps[$old_id]);
				// New dependencies array
				$newDeps = array();
				foreach ($oldDeps as $dep) 
					$newDeps[] = $tasks[$dep]->task_id;
					
				// Update the new task dependencies
				$csList = implode (',', $newDeps);
				$newTask->updateDependencies ($csList);
			} // end of update dependencies 

			$newTask->store();

		} // end Fix record integrity	

			
	} // end of importTasks

	/**
	**	Overload of the dpObject::getAllowedRecords 
	**	to ensure that the allowed projects are owned by allowed companies.
	**
	**	@author	handco <handco@sourceforge.net>
	**	@see	dpObject::getAllowedRecords
	**/

	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ){
		$oCpy = new CCompany ();
		
		$aCpies = $oCpy->getAllowedRecords ($uid, "company_id, company_name");
		if (count($aCpies)) {
		  $buffer = '(project_company IN (' . 
				  implode(',' , array_keys($aCpies)) . 
				  '))'; 

		  if ($extra['where'] != "") 
			  $extra['where'] = $extra['where'] . ' AND ' . $buffer;
		  else
			  $extra['where'] = ' AND ' . $buffer; 
		} else {
		  // There are no allowed companies, so don't allow projects.
		  if ($extra['where'] != '')
		    $extra['where'] = $extra['where'] . ' AND 1 = 0 ';
		  else
		    $extra['where'] = '1 = 0';
		}

		return parent::getAllowedRecords ($uid, $fields, $orderby, $index, $extra);
				
	}
	
	/**
	 *	Overload of the dpObject::getDeniedRecords 
	 *	to ensure that the projects owned by denied companies are denied.
	 *
	 *	@author	handco <handco@sourceforge.net>
	 *	@see	dpObject::getAllowedRecords
	 */
	function getDeniedRecords( $uid ) {
		$aBuf1 = parent::getDeniedRecords ($uid);
		
		$oCpy = new CCompany ();
		// Retrieve which projects are allowed due to the company rules 
		$aCpiesAllowed = $oCpy->getAllowedRecords ($uid, "company_id,company_name");
		
		$sql = "SELECT project_id FROM projects ";
		if (count($aCpiesAllowed))
		   $sql .= "WHERE NOT (project_company IN (" . implode (',', array_keys($aCpiesAllowed)) . '));';
		$aBuf2 = db_loadColumn ($sql);
		
		return array_merge ($aBuf1, $aBuf2); 
		
	}

	function store() {

		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}

		if( $this->project_id ) {
			$ret = db_updateObject( 'projects', $this, 'project_id', false );
		} else {
			$ret = db_insertObject( 'projects', $this, 'project_id' );
		}
		
		//split out related departments and store them seperatly.
		$sql = 'DELETE FROM project_departments WHERE task_id='.$this->project_id;
		db_exec( $sql );
		$departments = explode(',',$this->project_departments);
		foreach($departments as $department){
			$sql = 'INSERT INTO project_departments (project_id, department_id) values ('.$this->project_id.', '.$department.')';
			db_exec( $sql );
		}
		
		//split out related contacts and store them seperatly.
		$sql = 'DELETE FROM project_contacts WHERE task_id='.$this->project_id;
		db_exec( $sql );
		$contacts = explode(',',$this->project_contacts);
		foreach($contacts as $contact){
			$sql = 'INSERT INTO project_contacts (project_id, contact_id) values ('.$this->project_id.', '.$contact.')';
			db_exec( $sql );
		}

		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}

	}
}
?>
