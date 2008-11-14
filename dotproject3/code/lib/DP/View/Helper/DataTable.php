<?php
/**
 * YUI DataTable View Helper.
 * 
 * This helper generates the construction code for a YUI DataTable.
 * The helper retrieves column definitions and specified editors, or assumes an editor type based on the column metadata.
 * 
 * Events exposed by the YUI API should pass through a singleton managing all system events to provide a non-JS API specific method of hooking into
 * all events that take place on the client side.
 * 
 * @author ebrosnan
 * @version 3.0 alpha
 */
class DP_View_Helper_DataTable extends Zend_View_Helper_Abstract
{
	public $view; // $view Zend_View calling view object.
	
	/**
	 * View Helper Default Method
	 * 
	 * @param $ds Object The datasource
	 */
	public function DataTable()
	{
		$id = "myDataTable";
		//var myDataTableDS = new YAHOO.util.XHRDataSource("http://dp3/orgunit/json/index", {responseType : YAHOO.util.DataSource.TYPE_JSON});
		$js_cols = '		
			
			myDataTableDS.responseSchema = {
				resultsList: "results",
				fields: [
					{ key: "id" },
					{ key: "name" },
					{ key: "description"}
				]
			};
		';
		$this->view->HeadScript()->appendScript($js_cols);
		
		// Add the construction code.
		$js = 'YAHOO.util.Event.addListener(window, "load", function() {
		';
		$js .= '	var '.$id.' = new YAHOO.widget.DataTable("'.$id.'Ctr", '.$id.'Cols, '.$id.'DS);
		
		});';
		$this->view->HeadScript()->appendScript($js);
		
		
		$container = '<div class="yui-skin-sam"><div id="'.$id.'Ctr"></div></div>';
		return $container;
	}

	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>