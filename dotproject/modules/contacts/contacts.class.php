<?php /* CONTACTS $Id$ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision$
*/

require_once( $AppUI->getSystemClass ('dp' ) );

/**
* Contacts class
*/
class CContact extends CDpObject{
/** @var int */
	var $contact_id = NULL;
/** @var string */
	var $contact_first_name = NULL;
/** @var string */
	var $contact_last_name = NULL;
	var $contact_order_by = NULL;
	var $contact_title = NULL;
	var $contact_birthday = NULL;
	var $contact_company = NULL;
	var $contact_department = NULL;
	var $contact_type = NULL;
	var $contact_email = NULL;
	var $contact_email2 = NULL;
	var $contact_phone = NULL;
	var $contact_phone2 = NULL;
	var $contact_fax = NULL;
	var $contact_mobile = NULL;
	var $contact_address1 = NULL;
	var $contact_address2 = NULL;
	var $contact_city = NULL;
	var $contact_state = NULL;
	var $contact_zip = NULL;
	var $contact_icq = NULL;
	var $contact_aol = NULL;
        var $contact_birthday = NULL;
	var $contact_notes = NULL;
	var $contact_project = NULL;
	var $contact_country = NULL;
	var $contact_icon = NULL;
	var $contact_owner = NULL;
	var $contact_private = NULL;

	function CContact() {
		$this->CDpObject( 'contacts', 'contact_id' );
	}

	function check() {
		if ($this->contact_id === NULL) {
			return 'contact id is NULL';
		}
	// ensure changes of state in checkboxes is captured
		$this->contact_private = intval( $this->contact_private );
		$this->contact_owner = intval( $this->contact_owner );
		return NULL; // object is ok
	}
	
	function getCompanyID(){
		$sql = "select company_id from companies where company_name = '" . $this->contact_company . "'";
		$company_id = db_loadResult( $sql );
		return $company_id;
	}
	
}
?>
