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
		$colDefs = "myColDefs";
		$dataSource = "myDataSource";
		
		// Add the construction code.
		$js = 'var '.$id.' = new YAHOO.widget.DataTable("'.$id.'-ctr", '.$colDefs.', '.$dataSource.');';
		$this->view->HeadScript()->appendScript($js);
		
		
		$container = '<div id="'.$id.'-ctr"></div>';
		return $container;
	}

	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>