<?php

$user_id = isset( $HTTP_POST_VARS['user_id'] ) ? $HTTP_POST_VARS['user_id'] : 0;
$permission_id = isset( $HTTP_POST_VARS['permission_id'] ) ? $HTTP_POST_VARS['permission_id'] : 0;
$permission_grant_on = isset( $HTTP_POST_VARS['permission_grant_on'] ) ? $HTTP_POST_VARS['permission_grant_on'] : 0;
$permission_item = isset( $HTTP_POST_VARS['permission_item'] ) ? $HTTP_POST_VARS['permission_item'] : 0;
$permission_value = isset( $HTTP_POST_VARS['permission_value'] ) ? $HTTP_POST_VARS['permission_value'] : 0;

$del = isset( $HTTP_POST_VARS['del'] ) ? $HTTP_POST_VARS['del'] : 0;
$return = isset( $HTTP_POST_VARS['return'] ) ? $HTTP_POST_VARS['return'] : '';

if ($del && $permission_id <> 0) {
	$sql = "DELETE FROM permissions WHERE permission_id=$permission_id";
	mysql_query( $sql );
	$message = "Permission Deleted ";
} else if ($permission_id == 0) {
	$sql = "
	INSERT INTO permissions (
		permission_user, permission_grant_on, permission_item, permission_value
	) VALUES (
		'$user_id', '$permission_grant_on', '$permission_item', '$permission_value'
	)";
	mysql_query( $sql );
	$message = "Permission Created ";
} else {
	$sql ="UPDATE permissions
	SET
	permission_grant_on = '$permission_grant_on',
	permission_item = '$permission_item',
	permission_value = '$permission_value'
	WHERE permission_id = $permission_id";
	mysql_query( $sql );
	$message = "Permission Updated ";
}
$e = mysql_error();
if ($e) {
	$message = $e;
}
$return .= "&message=" . $message;
?>