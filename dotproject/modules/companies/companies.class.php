<?php /* COMPANIES $Id$ */

include_once( $AppUI->getSystemClass ('dp' ) );

##
## CCompany Class
##

class CCompany extends CDpObject {
	var $company_id = NULL;
	var $company_username = NULL;
	var $company_password = NULL;
	var $company_name = NULL;
	var $company_phone1 = NULL;
	var $company_phone2 = NULL;
	var $company_fax = NULL;
	var $company_address1 = NULL;
	var $company_address2 = NULL;
	var $company_city = NULL;
	var $company_state = NULL;
	var $company_zip = NULL;
	var $company_primary_url = NULL;
	var $company_owner = NULL;
	var $company_description = NULL;
	var $company_type = null;
	var $company_email = NULL;

	function CCompany() {
		$this->CDpObject( 'companies', 'company_id' );
	}

	function load( $oid ) {
		$sql = "
		SELECT companies.*,users.user_first_name,users.user_last_name
		FROM companies
		LEFT JOIN users ON users.user_id = companies.company_owner
		WHERE companies.company_id = $oid
		";

		return db_loadObject( $sql, $this );
	}

// overload check
	function check() {
		if ($this->company_id === NULL) {
			return 'company id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		global $AppUI;
	// call the parent class method to assign the oid
		CDpObject::canDelete( $msg, $oid );

	// do the specific checks
		$sql = "SELECT company_id,"
			. "\nCOUNT(DISTINCT project_id) AS p,"
			. "\nCOUNT(DISTINCT dept_id) AS d,"
			. "\nCOUNT(DISTINCT user_id) AS u"
			. "\nFROM companies"
			. "\nLEFT JOIN projects ON project_company = company_id"
			. "\nLEFT JOIN departments ON dept_company = company_id"
			. "\nLEFT JOIN users ON user_owner = company_id"
			. "\nWHERE company_id = $this->company_id GROUP BY company_id";

		$foo = null;
		$obj = db_loadObject( $sql, $foo );

		$msg = array();
		if ($obj->p) {
			$msg[] = $AppUI->_( 'Projects' );
		}
		if ($obj->d) {
			$msg[] = $AppUI->_( 'Departments' );
		}
		if ($obj->u) {
			$msg[] = $AppUI->_( 'Users' );
		}

		if (count( $msg )) {
			$msg = $AppUI->_( "noDeleteRecord" ) . ": " . implode( ', ', $msg );
			return false;
		} else {
			return true;
		}
	}
}
?>