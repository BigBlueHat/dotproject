<?php
/**
 * YUI DataSource Action Helper
 * 
 * Processes a rowset returned from a query or table gateway into an array that can be converted to JSON
 * directly and used by a YUI DataSource object.
 * 
 * @author ebrosnan
 * @version 3.0 alpha
 */
require_once 'Zend/Loader/PluginLoader.php';
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * {1} Action Helper 
 * 
 * @uses actionHelper {0}
 */
class DP_Controller_Action_Helper_YUIDataSource extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;

    /**
     * Constructor: initialize plugin loader
     * 
     * @return void
     */
    public function __construct(){
        // TODO Auto-generated Constructor
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    /**
     * 
     */
    public function direct(Zend_Db_Table_Rowset_Abstract $rowset){
    	// TODO: code
    }
}

