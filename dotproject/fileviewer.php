<?php
//file viewer
include "./includes/config.php";
include "./includes/db_connect.php";
include "./includes/main_functions.php";
include "./includes/permissions.php";

if($file_id) {


    $query = "select * from files where file_id=$file_id";
    $result = MYSQL_QUERY($query);
    $type = MYSQL_RESULT($result,0,"file_type");
		$size = MYSQL_RESULT($result,0,"file_size");
		$dname = MYSQL_RESULT($result,0,"file_name");
		$name = MYSQL_RESULT($result,0,"file_real_filename");
		$file_project = MYSQL_RESULT($result,0,"file_project");
		header("Content-length: $size");
    	header("Content-type: $type");
		header( "Content-disposition: attachment; filename=$dname" );
		readfile($root_dir . "/files/" . $file_project . "/" . $name);

};
?> 
