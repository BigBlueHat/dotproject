<?php
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer - mysql version

	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/

//	db_connect.php
// 	include to connect to the db
/*
$rcq = mysql_pconnect( $dbhost, $dbuser, $dbpass);
if(!$rcq){
	echo "db connection error";
	die();
}
else{
	$rcq = mysql_select_db($db);
	if(!$rcq){
		echo "db selection error:<BR> unable to select_db()";
		die();
	}
}
*/

db_connect( $dbhost, $db, $dbuser, $dbpass );

/* DB METHODS */

function db_connect( $host='localhost', $dbname, $user='root', $passwd='', $port='3306' ) {
	mysql_pconnect( "$host:$port", $user, $passwd )
		 or die( 'FATAL ERROR: Connection to database server failed' );

	if ($dbname) {
		mysql_select_db( $dbname )
			or die( "FATAL ERROR: Database not found ($dbname)" );
	} else {
		die( "FATAL ERROR: Database name not supplied<br />(connection to database server succesful)" );
	}
}

function db_error( $errLine='0' ) {
	$s = '<span class="error">'.mysql_error().'</span>';
	$s .= ($errLine ? "<br />The SQL error occured at line $errLine" : '');
	return $s;
}

function db_errno() {
	return mysql_errno();
}

function db_loadObject( $sql, &$object, $errLine='0' ) {
	$hash = array();
	if( !db_loadHash( $sql, &$hash, $errLine ) ) {
		return false;
	}
	bindHashToObject( $hash, $object );
	return true;
}

function db_exec( $sql ) {
	$cur = mysql_query( $sql );
	if( !$cur ) {
		return false;
	}
	return $cur;
}

function db_num_rows( $qid ) {
	return mysql_num_rows( $qid );
}

function db_loadHash( $sql, &$hash, $errLine='0' ) {
	$cur = mysql_query( $sql );
	$cur or exit( db_error( $errLine ) );
	$hash = mysql_fetch_assoc( $cur );
	mysql_free_result( $cur );
	if ($hash == false)
		return false;
	else
		return true;
}

function db_loadHashList( $sql, $errLine='0' ) {
	$cur = mysql_query( $sql );
	$cur or exit( db_error( $errLine ) );
	$hashlist = array();
	while ($hash = mysql_fetch_array( $cur )) {
		$hashlist[$hash[0]] = $hash[1];
	}
	mysql_free_result( $cur );
	return $hashlist;
}

function db_loadList( $sql, $maxrows=NULL, $errLine='0'  ) {
	$cur = mysql_query( $sql );
	$cur or exit( '<span class="error">'.db_error().'</span>'.($errLine ? "<br />The SQL error occured at line $errLine" : '') );
	$list = array();
	$cnt = 0;
	while ($hash = mysql_fetch_array( $cur, MYSQL_ASSOC )) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	mysql_free_result( $cur );
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function db_loadObjectList( $sql, $object, $maxrows = NULL )
{
	$cur = mysql_query( $sql );
	if (!$cur) {
		die( "DB_loadObjectList : " . db_error() );
	}
	$list = array();
	$cnt = 0;
	while ($row = mysql_fetch_array( $cur )) {
		$list[] = $object->Load( $row[0] );
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	mysql_free_result( $cur );
	return $list;
}

function DB_insertArray( $table, &$hash, $verbose=false ) {
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach ($hash as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . mysql_escape_string( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

	( $verbose ) && print "$sql<br>\n";

	if (!mysql_query( $sql )) {
		return false;
	}
	$id = mysql_insert_id();
	return true;
}

function db_updateArray( $table, &$hash, $keyName, $verbose=false ) {
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach ($hash as $k => $v) {

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

function db_delete( $table, $keyName, $keyValue )
{
	$keyName = db_escape( $keyName );
	$keyValue = db_escape( $keyValue );
	$ret = mysql_query( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}

function db_dateConvert( $src, &$dest, $srcFmt )
{
	$result = strtotime( $src );
	$dest = $result;
	return ( $result != 0 );
}

function db_datetime( $timestamp = NULL )
{
	if (!$timestamp) {
		return NULL;
	}
	if (is_object($timestamp)) {
		return $timestamp->toString( '%Y-%m-%d %H:%M:%S');
	} else {
		return strftime( '%Y-%m-%d %H:%M:%S', $timestamp );
	}
}

function db_escape( $str ) {
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
	if ($prefix) {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$prefix . $k ])) {
				$obj->$k = $hash[$k];
			}
		}
	} else {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$k])) {
				$obj->$k = $hash[$k];
			}
		}
	}
	//echo "obj="; print_r($obj); exit;
}
?>
