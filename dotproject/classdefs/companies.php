<?php /* CLASSDEFS $Id$ */
##
## CCompany Class
##

class CCompany {
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

	function CCompany() {
		// empty constructor
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

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->company_id === NULL) {
			return 'company id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->company_id ) {
			$ret = db_updateObject( 'companies', $this, 'company_id' );
		} else {
			$ret = db_insertObject( 'companies', $this, 'company_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT project_id FROM projects WHERE project_company = $this->company_id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "You cannot delete a company that has projects associated with it.";
		} else{
			$sql = "DELETE FROM companies WHERE company_id = $this->company_id";
			if (!db_exec( $sql )) {
				return db_error();
			} else {
				return NULL;
			}
		}
	}
}
?>