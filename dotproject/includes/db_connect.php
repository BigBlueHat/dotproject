<?php
require_once( "$root_dir/includes/db_$dbtype.php" );

db_connect( $dbhost, $db, $dbuser, $dbpass );

##
##	Generic functions based on library function (that is, non-db specific)
##

function db_loadObject( $sql, &$object ) {
	$hash = array();
	if( !db_loadHash( $sql, &$hash ) ) {
		return false;
	}
	bindHashToObject( $hash, $object );
	return true;
}

function db_loadHash( $sql, &$hash ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hash = db_fetch_assoc( $cur );
	db_free_result( $cur );
	if ($hash == false)
		return false;
	else
		return true;
}

function db_loadHashList( $sql ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hashlist = array();
	while ($hash = db_fetch_array( $cur )) {
		$hashlist[$hash[0]] = $hash[1];
	}
	db_free_result( $cur );
	return $hashlist;
}

function db_loadList( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($hash = db_fetch_assoc( $cur )) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function db_loadObjectList( $sql, $object, $maxrows = NULL )
{
	$cur = db_exec( $sql );
	if (!$cur) {
		die( "DB_loadObjectList : " . db_error() );
	}
	$list = array();
	$cnt = 0;
	while ($row = db_fetch_array( $cur )) {
		$list[] = $object->Load( $row[0] );
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
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

	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
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
	$ret = db_exec( $sql );
	return $ret;
}

function db_delete( $table, $keyName, $keyValue )
{
	$keyName = db_escape( $keyName );
	$keyValue = db_escape( $keyValue );
	$ret = db_exec( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}


function db_insertObject( $table, &$object, $keyName = NULL )
{
	$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
	foreach (get_object_vars( $object ) as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		if ($k[0] == '_') { // internal field
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . mysql_escape_string( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	if ($keyName && $id)
		$object->$keyName = $id;
	return true;
}

function db_updateObject( $table, &$object, $keyName )
{
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach (get_object_vars( $object ) as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
			continue;
		}
		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . mysql_escape_string( $v ) . "'";
			continue;
		}
		if( $v == '' ) {
			$val = 'NULL';
		} else {
			$val = "'" . mysql_escape_string( $v ) . "'";
		}
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	return db_exec( $sql );
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
