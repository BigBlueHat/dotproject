<?php
$tempUserName = dPgetParam( $_POST, 'temp_user_name', '' );
$permission_user = dPgetParam( $_POST, 'permission_user', '' );

// pull user_id for unique user_username (templateUser)
$sql = "SELECT user_id FROM users WHERE user_username = '$tempUserName'";
$res = db_loadList( $sql );
$tempUserId = $res[0]['user_id'];


// check if 'delete existing permissions'-checkbox is checked
if ( isset( $_POST['delPerms'] ) && isset( $_POST['permission_user'] ))
{
	// delete existing permissions
	if ($msg = db_delete( 'permissions', 'permission_user', $permission_user )) {
		$AppUI->setMsg( 'Permission' );
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
}


// pull permissions from user to copy from
$sql = "SELECT * FROM permissions WHERE permission_user = $tempUserId";
$obj = new CPermission();
$objList = db_loadObjectList( $sql, $obj );

// _copy_ permissions from template to target user
foreach($objList as $permObj) {

	// convert object to a new one, an Id of Zero is inserted as new row by SQL
	$permObj->permission_id = 0;

	// rename from template to target user
	$permObj->permission_user = $permission_user;

	// store _new_ permission object in database
	$permObj->store();
}

$AppUI->setMsg( 'Permissions' );
$AppUI->setMsg( 'copied from template', UI_MSG_OK, true );
$AppUI->redirect();
?>