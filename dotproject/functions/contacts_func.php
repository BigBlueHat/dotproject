<?php


class dotContact
{
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
	var $contact_country = NULL;
	var $contact_zip = NULL;
	var $contact_icq = NULL;
	var $contact_notes = NULL;
	var $contact_project = NULL;
	var $contact_icon = 'obj/contact';
  
  	function dotContact()
  	{
	}

	function Load( $oid )
	{
		$obj = new dotContact();
		$ret = DB_loadObject( "SELECT * FROM contacts WHERE contact_id=$oid", $obj );
		AppLog( "contacts/contact/$obj->contact_id", 'load', $ret );
		return $obj;
	}
	
	function Check()
	{
		// TODO
		return NULL; // object is ok
	}

	function Bind( $hash )
	{
		is_array($hash) or die( "dotContact.Bind : hash expected" );
		bindHashToObject( $hash, $this );
	}

	function Store()
	{
		$msg = $this->Check();
		if( $msg )
			return $msg;
			
		if( $this->contact_id ) {
			$ret = DB_updateObject( 'contacts', $this, 'contact_id' );
			AppLog( "contacts/contact/$this->contact_id", 'update', $ret );
			if( ! $ret )
				return DB_Error();
			else
				return "Contact updated";
		} else {
			$ret = DB_insertObject( 'contacts', $this, 'contact_id' );
			AppLog( "contacts/contact/$this->contact_id", 'insert', $ret );
			if( ! $ret )
				return DB_Error();
			else
				return "Contact added";
		}
	}

	function Delete()
	{
		$ret = DB_delete( 'contacts', 'contact_id', $this->contact_id );
		AppLog( "contacts/contact/$this->contact_id", 'delete', $ret );
		if( ! $ret )
			return DB_Error();
		else
			return "Contact deleted";
	}	
}

?>