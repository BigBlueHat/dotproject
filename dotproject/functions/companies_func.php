<?php


class dotCompany
{
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

  
  	function dotCompany()
  	{
	}

	function Load( $oid )
	{
		$obj = new dotCompany();
		$ret = DB_loadObject( "SELECT * FROM companies WHERE company_id=$oid", $obj );
		return $obj;
	}
	
	function Check()
	{
		// TODO
		return NULL; // object is ok
	}

	function Store()
	{
		$msg = $this->Check();
		if( $msg )
			return $msg;
			
		if( $this->contact_id )
			$ret = DB_updateObject( 'companies', $this, 'company_id' );
		else
			$ret = DB_insertObject( 'companies', $this, 'company_id' );
		if( ! $ret )
			return DB_Error();
		else
			return NULL;		
	}

	
}

?>