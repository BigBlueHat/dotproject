<?php
/*
	
	@lastmod Dec 8 2001 by leo
	@depends lib.DB.php
	
*/

class SmartFolder
{

	function SmartFolder( $sourcetable, $fields )
	{
		is_string( $sourcetable ) 
			or die( 'E_INTERNAL in SmartFolder.SmartFolder() - sourcetable must be a string' );
		is_array($fields) 
			or die( 'E_INTERNAL in SmartFolder.SmartFolder() - fields must be an array' );
		$this->source = $sourcetable;
		$this->fields = $fields;
	}
	// SmartFolder
	
	/*
	 * return the list of filters folder in a hash ( field => count of items found)
	 */
	function getFolders( $field )
	{
		in_array( $field, $this->fields ) 
			or die( "E_PARAM in SmartFolder.getFolders, field $field is no registered" );
		$sql = "SELECT $field, COUNT(*) FROM $this->source GROUP BY $field ORDER BY $field";
		return DB_loadHashList( $sql );
	} 
	// getFolders
	
	function getFolderContent( $field, $value, $orderby=NULL )
	{
		$fld = DB_escape( $field );
		// special treatment to fetch NULL values
		if( $value == '' )
			$criteria = "$fld IS NULL";
		else
			$criteria = "$fld='". DB_escape( $value ) . "'";
		// orderby is a optionnal param
		if( $orderby )
			$sqlOrder = "ORDER BY " . DB_escape( $orderby );
		$sql = "SELECT * FROM $this->source WHERE $criteria $sqlOrder";
//		echo $sql;
		$list = DB_loadList( $sql );
//		print_r($list); exit;
		return $list;
	}
} 
// class

?>