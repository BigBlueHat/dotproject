<?php
/**
 * YUI Datatable Column Definitions Helper.
 * 
 * Creates the Javascript code necessary to define the column definitions. Takes a DP_YUI_ColumnDefs object and iterates through
 * the columns. The DP_YUI_ColumnDefs object provides reasonable defaults for formatting of the columns.
 * 
 * @package dotproject
 * @version 3.0 alpha
 * @author ebrosnan
 */
class DP_View_Helper_YUIColumnDefs extends Zend_View_Helper_Abstract 
{
	/**
	 * @var Zend_View $view Reference to the calling view object.
	 */
	public $view;
	
	/**
	 * Generate YUI datatable columns definition.
	 * 
	 * Uses the HeadScript helper to append the definition.
	 * 
	 * @param DP_YUI_ColumnDefs $cols Instance of column definitions object.
	 * @return string javascript object containing column definitions as string, without variable name
	 */
	public function YUIColumnDefs(DP_Datasource_Columns $cols) {
		
		$js = '[';
		
		foreach($cols as $k=>$def) {
			$js .= '{ ';
			
			$js .= 'key: "'.$def['key'].'", ';
			$js .= 'label: "'.$def['label'].'", ';
			$js .= 'formatter: "'.$def['formatter'].'", ';
			$js .= 'editor: '.$def['editor'].'';
			
			$js .= '}';
			if ($k < count($cols)) {
				$js .= ', ';
			}
		}
		
		$js .= ']';
	
		//$this->view->HeadScript()->appendScript($js);
		return $js;
	}
	
	
	// From Zend_View_Helper_Abstract

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>