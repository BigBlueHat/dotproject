<?php
if(empty($file_id))$file_id =0;
$sql = "select file_real_filename, file_project from files where file_id=$file_id";
$result = MYSQL_QUERY($sql);
$file = @MYSQL_RESULT($result,0,"file_real_filename");
$file_project = @MYSQL_RESULT($result,0,"file_project");
@unlink($root_dir . "/files/" . $file_project . "/" . $file);
$fsql ="delete from files
where file_id = $file_id";
$frc = mysql_query($fsql);
$message= mysql_error();
$fsql = "delete from files_index where file_id = $file_id";
$frc = mysql_query($fsql);
$message.= mysql_error();

?>



