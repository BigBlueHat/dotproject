<?php

/*
	lib.DB
	Database abstract layer - mysql version

	A generic database layer providing a set of low to middle level functions 
	originally written for WEBO project, see webo source for "real life" usages
	 
@methods

	DB_Connect( $db_url, $db_name, $db_user, $db_passwd )
		open a connexion to the database
		db_url format : "mysql://<host>:<port>[/<database>]"
		
	DB_Error()
		return the last error message from the database
	
	DB_Errno()
		return the last error number from the database
		
	DB_loadObject( $sql, &$object )
		fetch a single row in the db and populate the  object $object from that row
		object properties names must match columns and be declared in the class (*)
		
	DB_exec( $sql )
		execute a single sql query and return the status (1=ok, 0=error)
		aka. mysql_query()
		
	DB_num_rows( $queryid )
		return the number of rows of a query
		
	DB_loadHash( $sql, &$hash )
		fetch a single row using sql query $sql and return it in the associative array $hash
		return false if an error occurred, true otherwise
		
	DB_loadHashList( $sql )
		fill and return hash array (key=>label) from the sql query
		the first column of the SELECT statement will be the key, the second the label
		Example : 
			$hash = DB_loadHashList( "SELECT id, fullname FROM persons ORDER BY name" );
			$hash will be something like array( '1' => 'Alan Cox', '2' => 'Linus Torvald', ... )
	
	DB_loadList( $sql, $maxrows = NULL )
		Load a full array from a SQL query
		
	DB_loadObjectList( $sql, $object, $maxrows = NULL )
		[BETA CODE]
		
	DB_insertArray( $table, &$hash, $keyName = NULL )
		insert an associative array in the table named $table
		column names and hash keys *must match*
		if $keyName is provided, the hash is updated to contain the inserted ID, via a mysql_insert_id()
		
	DB_updateArray( $table, &$hash, $keyName )
		update an associative array in the table named $table with Primary key $keyName
		column names and hash keys *must match*
		
	DB_insertObject( $table, &$object, $keyName = NULL )
		same action that DB_insertArray but for an object
		object properties must be declared in the object.
		only scalar properties are taken, arrays and objects are safely ignored
		
	DB_updateObject( $table, &$object, $keyName )
		 build and execute a sql UPDATE statement for the PHP object $object
		table is the name of the table where object is to be stored
		keyname is the name of the primary key field
		the row with this primary key must allready exists in the table
		
	DB_DateConvert( $src, &$dest, $srcFmt )
		convert a database datetime string ($src) to a unix timestamp ($dest)
		srcFmt is not used yet
		
	DB_Datetime( $timestamp = NULL )
		return a datetime in database suited format, given a unix timestamp
		
	DB_Escape( $str )
		Escape a string according to the underlying DB, for use in a SQL query
		Ex: $escaped = DB_Escape( " L'autre" ); // -> "L\'autre" (for MySQL) 

@version
	0.8.0	- Dec 02 2001

@depends 
	php extension mysql

@author
	Leo West west_leo@yahooREMOVEME.com
	
*/


function DB_Connect( $db_url, $db_user, $db_passwd )
{
	$db = parse_url( $db_url );
	
	( $db['scheme'] == 'mysql' ) or die( "Database not supported: $db[scheme]" );
	
	$db['name'] = substr( $db['path'], 1 );
	isset($db['port']) or $db['port'] = 3306;
	
	$db_cnx = mysql_pconnect( "$db[host]:$db[port]", $db_user, $db_passwd )
		 or die( 'database connexion failed' );
	
	if( $db['name'] )
		mysql_select_db( $db['name'] ) or die( "Database not found ($db[name])" );

}


function DB_Error()
{
	return mysql_error();
}

function DB_Errno()
{
	return mysql_errno();
}

function DB_loadObject( $sql, &$object )
{
	$hash = array();
	if( ! DB_loadHash( $sql, &$hash ) ) {
		DB_Error();
		return false;
	}
	bindHashToObject( $hash, $object );
	return true;
}

function DB_exec( $sql )
{
	$cur = mysql_query( $sql );
	if( ! $cur ) {
		return false;
	}
	return $cur;
}

function DB_num_rows( $qid )
{
	return mysql_num_rows( $qid );
}

function DB_loadHash( $sql, &$hash )
{
	$cur = mysql_query( $sql );
	$cur or die( DB_Error() );
	$hash = mysql_fetch_assoc( $cur );
	mysql_free_result($cur );
	if( $hash == false  )
		return false;
	else
		return true;	
}

function DB_loadHashList( $sql )
{
	$cur = mysql_query( $sql );
	$cur or die( "Error in DB_loadHashList -" . DB_Error() . " - SQL=$sql" );
	$hashlist = array();
	while( $hash = mysql_fetch_array( $cur ) )
	{
		$hashlist[$hash[0]] = $hash[1];
	}
	mysql_free_result($cur);
	return $hashlist;
}


function DB_loadList( $sql, $maxrows = NULL )
{
	$cur = mysql_query( $sql );
	$cur  or DB_Error();
	$list = array();
	$cnt = 0;
	while( $hash = mysql_fetch_array( $cur, MYSQL_ASSOC ) ) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ )
			break;
	}
	mysql_free_result($cur);
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function DB_loadObjectList( $sql, $object, $maxrows = NULL )
{
	$cur = mysql_query( $sql );
	if( ! $cur ) die( "DB_loadObjectList : " . DB_Error() );
	$list = array();
	$cnt = 0;
	while( $row = mysql_fetch_array( $cur ) ) 
	{	
		$list[] = $object->Load( $row[0] );
		if( $maxrows && $maxrows == $cnt++ )
			break;
	}
	mysql_free_result($cur);
	return $list;
}

function DB_insertArray( $table, &$hash, $keyName = NULL, $verbose=false )
{
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach( $hash as $k => $v ) {
		if( is_array($v) or is_object($v) or $v == NULL ) 
			continue;
		$fields[] = $k;
		$values[] = "'" . mysql_escape_string( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	
	( $verbose ) && print "$sql<br>\n";
	
	if( ! mysql_query( $sql ) )
		return false;
	$id = mysql_insert_id();
	return true;	
}

function DB_updateArray( $table, &$hash, $keyName, $verbose=false )
{
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach( $hash as $k => $v ) {
			
		if( is_array($v) or is_object($v) or $k[0] == '_' ) // internal or NA field 
			continue;

		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . mysql_escape_string( $v ) . "'";
			continue;
		}
		if( $v == '' ) 
			$val = 'NULL';
		else
			$val = "'" . mysql_escape_string( $v ) . "'";
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	( $verbose ) && print "$sql<br>\n";
	$ret = mysql_query( $sql );
	return $ret;
}

function DB_Delete( $table, $keyName, $keyValue )
{
	$keyName = DB_escape( $keyName );
	$keyValue = DB_escape( $keyValue );
	$ret = mysql_query( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}

function DB_insertObject( $table, &$object, $keyName = NULL )
{
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach( get_object_vars( $object ) as $k => $v ) {
		if( is_array($v) or is_object($v) or $v == NULL ) 
			continue;
		if( $k[0] == '_' ) // internal field
			continue;
		$fields[] = $k;
		$values[] = "'" . mysql_escape_string( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	if( ! mysql_query( $sql ) ) {
		return false;
	}
	$id = mysql_insert_id();
	if( $keyName && $id )
		$object->$keyName = $id;
	return true;
	
}

function DB_updateObject( $table, &$object, $keyName )
{
	$fmtsql = "UPDATE $table SET %s WHERE %s";	
	foreach( get_object_vars( $object ) as $k => $v ) {
	
		if( is_array($v) or is_object($v) or $k[0] == '_' ) // internal or NA field 
			continue;

		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . mysql_escape_string( $v ) . "'";
			continue;
		}
		if( $v == '' ) 
			$val = 'NULL';
		else
			$val = "'" . mysql_escape_string( $v ) . "'";
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	$ret = mysql_query( $sql );
	return $ret;
}

function DB_DateConvert( $src, &$dest, $srcFmt )
{
	$result = strtotime( $src );
	$dest = $result;
	return ( $result != 0 );
}

function DB_Datetime( $timestamp = NULL )
{
	if( ! $timestamp )
		return NULL;
	if( is_object($timestamp) )
		return $timestamp->toString( '%Y-%m-%d %H:%M:%S');
	else
		return strftime( '%Y-%m-%d %H:%M:%S', $timestamp );
}

function DB_Escape( $str )
{
	return mysql_escape_string( $str );
}


/*
 *  copy the hash array content into the object as properties
 *  only existing properties of object are filled. when undefined in hash, properties wont be deleted
 *  @param obj byref the object to fill of any class
 *  @param hash the input array 
 */
function bindHashToObject( $hash, &$obj, $prefix=NULL )
{
	is_array( $hash ) or die( "bindHashToObject : hash expected" );
	is_object( $obj ) or die( "bindHashToObject : object expected" );
	if( $prefix ) {
		foreach( get_object_vars($obj) as $k => $v ) {
			if( isset($hash[$prefix . $k ]) ) {
				$obj->$k = $hash[$k];
			}
		}
	} else {
		foreach( get_object_vars($obj) as $k => $v ) {
			if( isset($hash[$k]) ) {
				$obj->$k = $hash[$k];
			}
		}
	}
	echo "obj="; print_r($obj ); exit;
}



?>
