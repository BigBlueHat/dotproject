<?php
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	MYSQL VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/

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

function db_error() {
	return mysql_error();
}

function db_errno() {
	return mysql_errno();
}

function db_insert_id() {
	return mysql_insert_id();
}

function db_exec( $sql ) {
	$cur = mysql_query( $sql );
	if( !$cur ) {
		return false;
	}
	return $cur;
}

function db_free_result( $cur ) {
	mysql_free_result( $cur );
}

function db_num_rows( $qid ) {
	return mysql_num_rows( $qid );
}

function db_fetch_row( $cur ) {
	return mysql_fetch_row( $cur );
}

function db_fetch_assoc( $cur ) {
	return mysql_fetch_assoc( $cur );
}

function db_fetch_array( $cur  ) {
	return mysql_fetch_array( $cur );
}

function db_escape( $str ) {
	return mysql_escape_string( $str );
}

function db_version() {
	;
	if( ($cur = mysql_query( "SELECT VERSION()" )) ) {
		$row =  mysql_fetch_row( $cur );
		mysql_free_result( $cur );
		return $row[0];
	} else {
		return 0;
	}
}

function db_unix2dateTime( $time ) {
	// converts a unix time stamp to the default date format
	return $time > 0 ? date("Y-m-d H:i:s", $time) : null;
}

function db_dateTime2unix($time) {
	// converts a DB date to a unix time stamp
	return strtotime( substr( $time, 0, 10 ) );
}


?>
