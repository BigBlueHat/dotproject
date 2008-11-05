<?php
/**
 * YUI Datatable Column Definitions Helper.
 * 
 * Creates the Javascript code necessary to define the column definitions. Takes a DP_YUI_ColumnDefs object and iterates through
 * the columns. The DP_YUI_ColumnDefs object provides reasonable defaults for formatting of the columns.
 * 
 * @package dotproject
 * @version 3.0 alpha
 *
 */
class DP_View_Helper_YUIColumnDefs extends Zend_View_Helper_Abstract 
{
	/**
	 * @var Zend_View_Interface $view The current view object.
	 */
	public $view;
	
	public function YUIColumnDefs(DP_YUI_ColumnDefs $cols) {
		
		$js = 'var myDataTableCols = [';
		
		foreach($cols as $k=>$def) {
			$js .= '{ ';
			
			$js .= 'key: "'.$def['key'].'", ';
			$js .= 'label: "'.$def['key'].'", ';
			$js .= 'formatter: "'.$def['formatter'].'", ';
			$js .= 'editor: '.$def['editor'].'';
			
			$js .= '}';
			if ($k < count($cols)) {
				$js .= ', ';
			}
		}
		
		$js .= ']';
	
		$this->view->HeadScript()->appendScript($js);
	}
	
	
	
	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>