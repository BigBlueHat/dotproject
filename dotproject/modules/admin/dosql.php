<?php
if(empty($user_id))$user_id==0;

//case deleting user
if(isset($del))
{ 
	$dsql = "delete from users where user_id=" . $user_id;
	mysql_query($dsql);
	$dsql = "delete from permissions where permission_user=" . $user_id;
	$message = "User Deleted";
}
else if($user_id == 0)
{
$dsql = "Insert into users (user_username , user_password , user_parent , user_type  , user_first_name  , user_last_name  , user_company  , user_email  , user_phone  , user_home_phone  , user_mobile  , user_address1  , user_address2  , user_city  , user_state  , user_zip  , user_country  , user_icq  , user_aol  , user_birthday, signature )
values
('$user_username',password('$user_password'),'$user_parent','$user_type ','$user_first_name ','$user_last_name ','$user_company ','$user_email ','$user_phone ','$user_home_phone ','$user_mobile ','$user_address1 ','$user_address2 ','$user_city ','$user_state ','$user_zip ','$user_country ','$user_icq ','$user_aol ','$user_birthday', '$signature')";

$message = "User Created";




}
else
{
$dsql = "update users set 
user_username='$user_username',
user_password=password('$user_password'),

user_first_name='$user_first_name',
user_last_name='$user_last_name',
user_company='$user_company',
user_email='$user_email',
user_phone='$user_phone',
user_home_phone='$user_home_phone',
user_mobile='$user_mobile',
user_address1='$user_address1',
user_address2='$user_address2',
user_city='$user_city',
user_state='$user_state',
user_zip='$user_zip',
user_country='$user_country',
user_icq='$user_icq',
user_aol='$user_aol',
signature='$signature',
user_birthday='$user_birthday'
where user_id = $user_id";
$message = "User Changed";
}

mysql_query($dsql);

$message2 = mysql_error();
if(strlen($message2) > 0)$message = $message2;


if(!ereg("admin", $perms) &! ereg("all", $perms)){?>
<script>
window.location="./index.php?m=admin&a=addedituser&user_id=<?php echo $user_id;?>&message=<?php echo $message;?>";
</script>
<?php die;}?>
<script>
window.location="./index.php?m=admin&message=<?php echo $message;?>";
</script>
