<?php

class DP_View_Helper_DataSource extends Zend_View_Helper_Abstract
{
	public $view;
	
	/**
	 * Add JavaScript which constructs the YUI XHR DataSource
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