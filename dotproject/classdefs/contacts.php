<?php
##
## CContact Class
##

class CContact {
	var $contact_id = NULL;
	var $contact_first_name = NULL;
	var $contact_last_name = NULL;
	var $contact_order_by = NULL;
	var $contact_title = NULL;
	var $contact_birthday = NULL;
	var $contact_company = NULL;
	var $contact_type = NULL;
	var $contact_email = NULL;
	var $contact_email2 = NULL;
	var $contact_phone = NULL;
	var $contact_phone2 = NULL;
	var $contact_mobile = NULL;
	var $contact_address1 = NULL;
	var $contact_address2 = NULL;
	var $contact_city = NULL;
	var $contact_state = NULL;
	var $contact_zip = NULL;
	var $contact_icq = NULL;
	var $contact_notes = NULL;
	var $contact_project = NULL;
	var $contact_country = NULL;
	var $contact_icon = NULL;
	var $contact_owner = NULL;
	var $contact_private = NULL;

	function CContact() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM contacts WHERE .contact_id = $oid";
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
		if ($this->contact_id === NULL) {
			return 'contact id is NULL';
		}
		if (!$this->contact_private) {
			$this->contact_private = '0';
		}
		$this->contact_owner = (int) $this->contact_owner;
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->contact_id ) {
			$ret = db_updateObject( 'contacts', $this, 'contact_id' );
		} else {
			$ret = db_insertObject( 'contacts', $this, 'contact_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM contacts WHERE contact_id = $this->contact_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>