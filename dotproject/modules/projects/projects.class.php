<?php /* PROJECTS $Id$ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision$
*/

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getPearClass( 'Date' ) );

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
		$tables[] = array( 'label' => 'Tasks', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_project' );
	// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables );
	}

	function delete() {
		$sql = "SELECT task_id FROM tasks WHERE task_project = $this->project_id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "You cannot delete a project that has tasks associated with it.";
		} else{
			$sql = "DELETE FROM projects WHERE project_id = $this->project_id";
			if (!db_exec( $sql )) {
				return db_error();
			} else {
				return NULL;
			}
		}
	}
}
?>