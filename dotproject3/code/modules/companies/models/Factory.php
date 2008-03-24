<?php
require_once DP_BASE_CODE . '/modules/companies/models/CompaniesList.php';

/**
 * Factory class for creating Companies related objects.
 * 
 * Extends the DP_Object_Factory class by being able to store and retrieve company module objects.
 * @todo Possibly use an abstract factory instead of having parallel factories for every module.
 * @package companies
 * @subpackage models
 */
class Companies_Object_Factory extends DP_Object_Factory {

	/** string Name of the table in the db schema relating to child class */
	protected static $_tbl = 'companies';
	/** string Name of the primary key field in the table */
	protected static $_tbl_key = 'company_id';
	
	public static function getCompaniesList() {
		return new DP_CompaniesList();
	}
}
?>