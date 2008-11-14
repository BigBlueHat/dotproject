<?php
/**
 * Json Presentation Layer for DP DataSources.
 * 
 * The DP_Presentation_Json class acts as a presentation layer for DP DataSource objects. It contains and constructs
 * information relating to the usage of Json as a transport for DataSource information. It is also used by View helpers to construct
 * YUI DataSources.
 * 
 * It should contain the URL of the json resource, the formatting of the returned data etc.
 * 
 * 
 */

class DP_Presentation_Json
{
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
	 */
	public function __construct($module, $controller = 'json', $action) 
	{
		$this->module = $module;
		$this->controller = $controller;
		$this->action = $action;
		$this->results_key = "results";
	}
	
	public function setResultsKey($rk)
	{
		$this->results_key = $rk;
	}
	
	public function getResultsKey()
	{
		return $this->results_key;
	}
	
	/**
	 * Get the json url
	 */
	public function getUrl()
	{
		return '/'.$this->module.'/'.$this->controller.'/'.$this->action;
	}
}
?>