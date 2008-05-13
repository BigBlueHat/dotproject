<?php
/**
 * Handles requests to instantiate a sub view where contacts is a child and
 * a list format is required.
 * 
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 */
class ContactsSubList implements DP_Related_Handler {
	
	public function __construct($relrow) {
		$fc = Zend_Controller_Front::getInstance();
		$cdir = $fc->getControllerDirectory($relrow->child_module);
		$model_dir = dirname($cdir).'/models';
		Zend_Loader::loadFile($model_dir.'/Index.php');
	}
	
	public function makeRelatedView($parent, $relationship) {
		$parent_table = $parent->getTable();
		$parent_name = $parent_table->info(Zend_Db_Table_Abstract::NAME);
		
		switch($parent_name) {
			case 'companies':
				return $this->companyContactsList($parent);
		}
	}

	/**
	 * Get a list of contacts as children of specified company row.
	 * 
	 * @param Zend_Db_Table_Row $parent Parent row.
	 * @return DP_View Instance of DP_View.
	 */
	protected function companyContactsList($parent) {
		Zend_Loader::loadFile('Index.php');
		$contacts_index = new Contacts_Index();

		$parent_table = $parent->getTable();
		$parent_key = $parent_table->info(Zend_Db_Table_Abstract::PRIMARY);

		$parent_filter = new DP_Filter();
		$parent_filter->fieldEquals('contact_company', $parent->company_id);
		
		$contacts_index->addModifier($parent_filter);

		$contacts_sublist = DP_View_Factory::getListView('dp-contacts-sublist-view');

		$contacts_sublist->row_iterator->addRow(
								Array(new DP_View_Cell_ObjectLink('contact_id','contact_order_by', '/contacts/view/object/id/'))
											);
		
		$contacts_sublist->setDataSource($contacts_index);
		$contacts_sublist->setColumnHeaders(Array('contact_order_by'=>'Contact Name'));
		$contacts_sublist->width = '100%';
		
		return $contacts_sublist;
		// Return list view
		
	}
}
?>