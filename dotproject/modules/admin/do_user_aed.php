<?php /* ADMIN $Id$ */
$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;

$obj = new CUser();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'User' );

//pull a list of existing usernames
$sql = "SELECT user_username FROM users";
$users = db_loadList( $sql );

// check if a user with the param Username already exists
$userEx = FALSE;
function userExistence( $userName ) {
	global $obj, $userEx;
	if ( $userName == $obj->user_username ) {
		$userEx = TRUE;
	}
}

if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( '', -1 );
	}
} else {
	// Iterate the above userNameExistenceCheck for each user
	foreach ( $users as $usrs ) {
		$usrLst = array_map( "userExistence", $usrs );
	}

	// If userName already exists quit with error and do nothing
	if ( $userEx == TRUE ) {
		$AppUI->setMsg( "already exists. Try another username.", UI_MSG_ERROR, true );
		$AppUI->redirect( );
	}

	$isNotNew = @$_REQUEST['user_id'];
	if (!$isNotNew) {
		$obj->user_owner = $AppUI->user_id;
	}
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>
