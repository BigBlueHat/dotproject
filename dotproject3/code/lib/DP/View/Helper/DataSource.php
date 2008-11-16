<?php
/**
 * YUI DataSource Construction helper
 * 
 * Generates required JavaScript to construct a YUI XHRDataSource.
 * 
 * @package dotproject
 * @version 3.0 alpha
 * @author ebrosnan
 *
 */
class DP_View_Helper_DataSource extends Zend_View_Helper_Abstract
{
	/**
	 * @var Zend_View $view Reference to the calling view object.
	 */
	public $view;
	
	/**
	 * Generate JavaScript to construct a YUI XHRDataSource
	 * 
	 * @param mixed $id Identifier for the DataSource
	 * @param DP_Presentation_Json $json_iface Instance of a json presentation object.
	 */
	public function DataSource($id, DP_Presentation_Json $json_iface)
	{
		$fc = Zend_Controller_Front::getInstance();
		$baseurl = $fc->getBaseUrl();
		
		$js = "var {$id} = new YAHOO.util.XHRDataSource('".$baseurl.$json_iface->getUrl()."', {responseType : YAHOO.util.DataSource.TYPE_JSON});";
		$this->view->HeadScript()->appendScript($js);
	}
	
	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>