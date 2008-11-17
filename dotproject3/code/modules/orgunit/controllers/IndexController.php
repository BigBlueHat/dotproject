<?php

require_once ('Zend/Controller/Action.php');

class Orgunit_IndexController extends Zend_Controller_Action 
{

	public function indexAction() {		
		$db = DP_Config::getDB();
		Zend_Db_Table::setDefaultAdapter($db);

		// Instantiate a datasource with defaults from this module.
		// This also includes a default presentation (DP_Presentation_Json) configured with default schema (id, name, description) in this example.		
		$ds = Orgunit_DataSource::factory($this, "index");
		$cols = $ds->makeColsFromMetadata(); // Generate client side column definition from the datasource based on table schema. will be used for YUI.DataTable
		$cols->setLabels(Array('id'=>'#','name'=>'Company name','description'=>'Company description','mail'=>'E-mail')); // Set localised labels for the YUI.DataTable columns
		$cols->setEnabled(Array('id','name','description','mail'));
		// Assign datasource, presentation and YUI definitions.
		$this->view->orgunit_datasource = $ds;
		$this->view->orgunit_json_pres = $ds->getDefaultPresentation();
		$this->view->orgunit_columns = $cols;
	}
}
?>