<?php
/**
 * Abstract class that implements most of the common initialisation work between add/edit screens.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 */

abstract class DP_Controller_Action_Edit extends Zend_Controller_Action 
{
	/**
	 * Instantiate and return the Zend_Form for this object.
	 * 
	 * @return Zend_Form Instance
	 */	
	abstract function getForm();
	
	/**
	 * Load the object row with the requested id and return it.
	 * 
	 * Abstracts much of the functionality repeated through all of the edit controllers.
	 * 
	 * @param integer $id object identifier.
	 * @return Array containing object row values.
	 */
	public function loadObject($id)
	{	
		if ($id == null) 
		{
			throw new Exception('Object ID Not Supplied.');
		}
		else
		{			
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
	
			$tbl = DP_Table::factory($this->getRequest()->getModuleName());
			$rows = $tbl->find($this->getRequest()->id);
				
			if ($rows->count() != 1) {
				throw new Exception("Invalid ID: Object Not found");
			}
				
			$obj = $rows->current();			
			$obj_hash = $obj->toArray();
			
			return $obj_hash;
		}		
	}
	
	/**
	 * Save an object submitted from a Zend_Form form.
	 * 
	 * @param Zend_Form $form The form that was submitted.
	 * @param mixed $id_key The key of the form value that represents the primary key column name.
	 * @return null
	 */
	public function saveObject(Zend_Form $form, $id_key)
	{
			$db = DP_Config::getDB();
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			
			$tbl = DP_Table::factory($this->getRequest()->getModuleName());
			
			$values = $form->getValues();
			$id = $values[$id_key];
			
			if ($id != '') {
				$where = $tbl->getAdapter()->quoteInto($id_key.' = ?', $id);
				$updated_obj = $tbl->createRow($values);
				$tbl->update($updated_obj->toArray(), $where);
				
				// Set message updated
				$this->_helper->FlashMessenger('Object updated.');
			} else {
				$new_obj = $tbl->createRow($values);
				$new_obj->$id_key = null;
				$new_obj->save();
				
				// Set message created
				$this->_helper->FlashMessenger('Object saved.');
			}		
	}
	
}
?>