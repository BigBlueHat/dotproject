<?php

$user_id = isset( $HTTP_POST_VARS['user_id'] ) ? $HTTP_POST_VARS['user_id'] : 0;
$permission_id = isset( $HTTP_POST_VARS['permission_id'] ) ? $HTTP_POST_VARS['permission_id'] : 0;
$permission_grant_on = isset( $HTTP_POST_VARS['permission_grant_on'] ) ? $HTTP_POST_VARS['permission_grant_on'] : 0;
$permission_item = isset( $HTTP_POST_VARS['permission_item'] ) ? $HTTP_POST_VARS['permission_item'] : 0;
$permission_value = isset( $HTTP_POST_VARS['permission_value'] ) ? $HTTP_POST_VARS['permission_value'] : 0;

$del = isset( $HTTP_POST_VARS['del'] ) ? $HTTP_POST_VARS['del'] : 0;
$return = isset( $HTTP_POST_VARS['return'] ) ? $HTTP_POST_VARS['return'] : '';

$message = '';

if ($del && $permission_id <> 0) {
	//delete_permission( $permission_id );
	db_delete( 'permissions', 'permission_id', $permission_id );
	$message = 'Permission deleted';
} else if ($permission_id == 0) {
	$fields = array(
		'permission_user' => $user_id,
		'permission_grant_on' => $permission_grant_on,
		'permission_item' => $permission_item,
		'permission_value' => $permission_value
	);
	db_insertArray( 'permissions', $fields );
	$message = 'Permission added';
} else {
	$fields = array(
		'permission_id' => $permission_id,
		'permission_grant_on' => $permission_grant_on,
		'permission_item' => $permission_item,
		'permission_value' => $permission_value
	);
	db_updateArray( 'permissions', $fields, 'permission_id' );
	$message = 'Permission updated';
}
$e = db_error();
if ($e) {
	$message .= $e;
}
$return .= "&message=" . $message;

?>