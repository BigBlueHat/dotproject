<?php

/**
 * JSON request controller for Organisational Units.
 * 
 * Acts as a wrapper around the model.
 * 
 * @author ebrosnan
 * @version 3.0 alpha
 */
	
require_once 'Zend/Controller/Action.php';

class Orgunit_JsonController extends Zend_Controller_Action
{

	/**
	 * Get an index of organisational units.
	 * 
	 * Filtering criteria have a default state as determined by the model.
	 * 
	 * Filtering criteria are changed via json controller method calls which change the server side representation of filters.
	 * If the filter changed affects the listing, the return data with the method call will include amongst its events - an event to say that
	 * the data should be reloaded. The event singleton in javascript will dispatch this reload event to all view widgets whose data depends on this
	 * data source. If the filtering criteria causes an error then the server returns only an encapsulated event to say that the query failed for whatever reason.
	 * The filters can then be reverted to the previous state if possible (after displaying a dialog).
	 * 
	 * The "index" action represents the "index" datasource
	 */
    public function indexAction() 
    {
    	$this->_helper->layout->disableLayout();
    	
    	$db = DP_Config::getDB();
		Zend_Db_Table::setDefaultAdapter($db);
    	
		// Get datasource from module factory by identifier
		// Get json presentation from module factory by identifier
		// Both supply reasonable default state, and also use DP_Config to customise output
		// such as columns shown / translations etc.
		// Use json presentation to determine selected fields.
		$ous = Orgunit_DataSource::factory($this, "index");
        
        $ous_records = $ous->fetch();
        
        $response = Array("results"=>$ous_records->toArray(), "totalRecords"=>$ous_records->count());
        $ous_json = Zend_Json::encode($response);

        $this->view->json_data = $ous_json;
        
    }
    
    /**
     * Retrieve a single record
     */
    public function viewAction()
    {
    	
    }
    
    /**
     * Update a single record
     */
    public function updateAction()
    {
    	
    }
    
    
    // Add a filter/search
    public function addfilterAction()
    {
    	
    }
    
    // Add/Set paging
    public function addpagerAction()
    {
    	
    }
    
    // Add/Set sorting
    public function addsortAction()
    {
    	
    }
    
}
?>