<?php
if (empty( $user_id )) {
	$user_id == 0;
}

$signature = htmlspecialchars( $signature );
$message2 = "";
//case deleting user
if (isset( $del )) { 
	$dsql = "DELETE FROM users WHERE user_id=" . $user_id;
	mysql_query( $dsql );
	$dsql = "DELETE FROM permissions WHERE permission_user=" . $user_id;
	$message = "User Deleted";

} else if ($user_id == 0) {
	$dsql = "INSERT INTO users (user_username, user_password, user_parent, user_type, user_first_name, user_last_name, user_company, user_department, user_email, user_phone, user_home_phone, user_mobile, user_address1, user_address2, user_city, user_state, user_zip, user_country, user_icq, user_aol, user_birthday, signature )
	VALUES
	('$user_username',PASSWORD('$user_password'), '$user_parent', '$user_type', '$user_first_name', '$user_last_name', '$user_company', '$user_department', '$user_email', '$user_phone','$user_home_phone','$user_mobile', '$user_address1', '$user_address2', '$user_city', '$user_state', '$user_zip', '$user_country', '$user_icq', '$user_aol', '$user_birthday', '$signature')";

	mysql_query( $dsql );
	$user_id = mysql_insert_id();

	$message = "User Created";

} else {
	$dsql = "UPDATE users SET 
	user_username='$user_username',
	user_type='$user_type',
	user_first_name='$user_first_name',
	user_last_name='$user_last_name',
	user_company='$user_company',
	user_department='$user_department',
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
	mysql_query( $dsql );
	$message2 = mysql_error();

	// Fix to stop corruption of password if it is not changed.
	$dsql = "UPDATE users SET
	user_password=password('$user_password')
	where user_id = $user_id and user_password != '$user_password'";
	mysql_query( $dsql );
}

$message2 .= mysql_error();
if (strlen( $message2 ) > 0) {
	$message = $message2;
}

if (empty( $perms['all'] ) && empty( $perms['admin'] )) { ?>
	<script>
	window.location="./index.php?m=admin&a=addedituser&user_id=<?php echo $user_id;?>&message=<?php echo $message;?>";
	</script>
<?php } else { ?>
	<script>
	window.location="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>&message=<?php echo $message;?>";
	</script>
<?php } ?>
