<?
//	db_connect.php
// 	include to connect to the db

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


?>