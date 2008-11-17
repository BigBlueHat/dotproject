<?php
/**
 * YUI DataTable View Helper.
 * 
 * This helper generates the construction code for a YUI DataTable.
 * 
 * Events exposed by the YUI API should pass through a singleton managing all system events to provide a non-JS API specific method of hooking into
 * all events that take place on the client side.
 * 
 * @package dotproject
 * @author ebrosnan
 * @version 3.0 alpha
 */
class DP_View_Helper_DataTable extends Zend_View_Helper_Abstract
{
	/**
	 * @var Zend_View $view Reference to the calling view object.
	 */
	public $view; 
	
	/**
	 * View Helper Default Method
	 * 
	 * @param mixed $div_id ID of the div element where the DataTable will be rendered.
	 * @param mixed $js_var variable name of the DataTable. Defaults to div_id + 'dt'
	 * 
	 * @return null
	 */
	public function DataTable($div_id, $cols_var, $ds_var, $js_var = null)
	{
		if ($js_var == null) {
			$js_var = $div_id . 'dt';
		}

		// Add the construction code.
		$js = 'var '.$js_var.' = new YAHOO.widget.DataTable("'.$div_id.'", '.$cols_var.', '.$ds_var.');';
		return $js;
	}

	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>