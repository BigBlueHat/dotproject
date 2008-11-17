<?php
/**
 * Json Presentation Layer for DP DataSources.
 * 
 * The DP_Presentation_Json class acts as a presentation layer for DP DataSource objects. It contains and constructs
 * information relating to the usage of Json as a transport for DataSource information. It can selectively filter or show
 * certain parts of information, although filtering of rows should be done by a DP_Filter.
 * 
 * @package dotproject
 * @author ebrosnan
 * @version 3.0 alpha
 * 
 */
class DP_Presentation_Json extends DP_Presentation
{
	/**
	 * @var array $columns array of field keys returned in the json response.
	 */
	protected $columns;
	
	/**
	 * @var array $default_columns array of field keys returned if no columns are specified.
	 */
	protected $default_columns;
	
	/**
	 * @var string $module name of a module.
	 */
	private $module;
	
	/**
	 * @var string $controller name of a controller (defaults to "json")
	 */
	private $controller;
	
	/**
	 * @var string $action name of an action (which becomes the data source identifier)
	 */
	private $action;

	/**
	 * @var string $results_key index(es) to the results array in the json response.
	 */
	protected $results_key;

	
	/**
	 * DP_Presentation_Json Constructor Method.
	 * 
	 * The json presentation object is constructed with module/controller/action names. The action becomes the datasource identifier.
 	 * The presentation layer will default to the json controller if no specifics are given.
	 * 
	 * @param Array $columns array of field keys that will be presented in the result set, defaults to all
	 * @param mixed $module module name to fetch json response
	 * @param mixed $controller controller name to fetch json response
	 * @param mixed $action action name to fetch json response (also becomes the datasource identifier)
	 */
	public function __construct(array $columns = Array(), $module, $controller = 'json', $action) 
	{
		$this->columns = $columns;
		$this->module = $module;
		$this->controller = $controller;
		$this->action = $action;
		$this->results_key = "results";
		$this->default_columns = Array("id","name","description");
	}
	
	/**
	 * Set the results key
	 * 
	 * Set the key used in the json response to locate the result set.
	 * 
	 * @param mixed $rk The key to the result set.
	 */
	public function setResultsKey($rk)
	{
		$this->results_key = $rk;
	}
	
	/**
	 * Get the results key
	 * 
	 * Get the key used to locate the result set within the json response.
	 * 
	 * @return mixed Key of the result set in the json response.
	 */
	public function getResultsKey()
	{
		return $this->results_key;
	}
	
	/**
	 * Get the response schema.
	 * 
	 * Generates an array containing information relevant to generating a response schema for a YUI DataTable.
	 * 
	 * @return Array associative array of reponse information.
	 */
	public function getResponseSchema()
	{
		$schema = Array('resultsList'=>$this->getResultsKey(),
						'fields'=>Array());
		
		if (count($this->columns) > 0) {
			foreach ($this->columns as $c) {
				$schema['fields'][] = $c;	
			}
		} else {
			$schema['fields'] = $this->getDefaultFields();
		}
		
		// TODO - hardcoded metafields.
		$schema['metafields'] = Array("totalRecords"=>"results.totalRecords");
		
		return $schema;
	}
	
	/**
	 * Get the URL which provides the json representation of the datasource.
	 * 
	 * @return string Relative URL to json request action.
	 */
	public function getUrl()
	{
		return '/'.$this->module.'/'.$this->controller.'/'.$this->action;
	}
	
	/**
	 * Translate the result set to this presentation type.
	 *
	 * @param array $rows
	 */
    public function translate($rows)
    {
    	$response = Array("results"=>$rows->toArray(), "totalRecords"=>$rows->count());
    	return Zend_Json::encode($response);
    }
    
	// Static Methods
	
	/**
	 * Generate a presentation based on the calling controller and a datasource ID.
	 * 
	 * @param Zend_Controller_Action $controller a Zend_Controller, the calling controller.
	 * @param mixed $id The datasource ID.
	 * @return DP_Presentation_Json a new instance of a json presentation object.
	 */
	public static function factory(Zend_Controller_Action $controller, $id)
	{
		$module = $controller->getRequest()->getModuleName();
		//$controller_name = $controller->getRequest()->getControllerName();
		$controller_name = 'json';
		$action = $id;
		
		return new DP_Presentation_Json(Array(), $module, $controller_name, $action);
	}
}
?>