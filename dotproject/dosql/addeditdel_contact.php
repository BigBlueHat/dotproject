<?php
if(empty($contact_id))$contact_id=0;

//IF delete
if($HTTP_POST_VARS["del"]){

$sql = "delete from contacts where contact_id = $contact_id";
mysql_query($sql);
$message  ="Contact Deleted";





}
//If update
elseif($HTTP_POST_VARS["contact_id"] > 0){

$sql = "update contacts set
contact_first_name = '$contact_first_name',
contact_last_name = '$contact_last_name',
contact_order_by = '$contact_order_by',
contact_title = '$contact_title',
contact_birthday = '$contact_birthday',
contact_company = '$contact_company',
contact_type = '$contact_type',
contact_email = '$contact_email',
contact_email2 = '$contact_email2',
contact_phone = '$contact_phone',
contact_phone2 = '$contact_phone2',
contact_mobile = '$contact_mobile',
contact_address1 = '$contact_address1',
contact_address2 = '$contact_address2',
contact_city = '$contact_city',
contact_state = '$contact_state',
contact_zip = '$contact_zip',
contact_icq = '$contact_icq',
contact_notes = '$contact_notes',
contact_project = '$contact_project'
where
contact_id = $contact_id
";
mysql_query($sql);
$message  =$contact_order_by . " Updated";














}
//If Insert
else{

$sql = "insert into contacts
(contact_first_name , contact_last_name , contact_order_by , contact_title , contact_birthday , contact_company , contact_type , contact_email , contact_email2 , contact_phone , contact_phone2 , contact_mobile , contact_address1 , contact_address2 , contact_city , contact_state , contact_zip , contact_icq , contact_notes , contact_project)
values 
('$contact_first_name' , '$contact_last_name' , '$contact_order_by' , '$contact_title' , '$contact_birthday' , '$contact_company' , '$contact_type' , '$contact_email' , '$contact_email2' , '$contact_phone' , '$contact_phone2' , '$contact_mobile' , '$contact_address1' , '$contact_address2' , '$contact_city' , '$contact_state' , '$contact_zip' , '$contact_icq' , '$contact_notes' , '$contact_project')";

mysql_query($sql);
$message  =$contact_order_by . " Inserted";







}

if($x = mysql_error())	{
	$message =  $sql . "<BR>". $x;
}
else{
	header("Location: ./index.php?m=contacts&message=" . $message);
}
?>

