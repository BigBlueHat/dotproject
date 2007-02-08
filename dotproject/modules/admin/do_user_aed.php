<?php /* ADMIN $Id$ */

include_once( $AppUI->getModuleClass('contacts') );
$del = dPgetParam($_REQUEST, 'del', false);
$user_current_company = $_REQUEST['user_current_company'];

$obj = new CUser();
$contact = new CContact();

if (!$obj->bind($_POST)) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
if (!$contact->bind($_POST)) {
	$AppUI->setMsg( $contact->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
$obj->user_username = strtolower($obj->user_username);

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'User' );

// !User's contact information not deleted - left for history.
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
	return;
}

$isNewUser = !($_REQUEST['user_id']);
if ( $isNewUser ) {
	// check if a user with the param Username already exists
	$userEx = false;

	//pull a list of existing usernames
	$q  = new DBQuery;
	$q->addTable('users','u');
	$q->addQuery('user_username');
	$users = $q->loadList();

	// Iterate the above userNameExistenceCheck for each user
	foreach ( $users as $usrs ) {
		$usrLst = array_map( 'userExistence', $usrs );
	}
	// If userName already exists quit with error and do nothing
	if ( $userEx == true ) {
		$AppUI->setMsg('already exists. Try another username.', UI_MSG_ERROR, true);
		$AppUI->redirect();
	}

	$contact->contact_owner = $AppUI->user_id;
}
if ($contact->contact_company != $user_current_company) {
	$contact->contact_department = null;
}
if (!is_numeric($contact->contact_id)) {
	$contact->store();
}
$q = new DBQuery;
$q->addUpdate('contact_department', $contact->contact_department);
$q->addTable('contacts');
$q->addWhere('contact_id = ' . $contact->contact_id);
$q->exec();
$obj->user_contact = $contact->contact_id;
$msg = $obj->store();
if ($msg)
	$AppUI->setMsg($msg, UI_MSG_ERROR);
else
	$AppUI->setMsg($isNewUser ? 'added - please setup roles and permissions now.  User must have at least one role to log in.' : 'updated', UI_MSG_OK, true);

if ($isNewUser)
	$AppUI->redirect('m=admin&a=viewuser&user_id='. $obj->user_id . '&tab=2');
else
	$AppUI->redirect();

/**
 * Function to check if a username already exists.
 * 
 * @param string $userName the username to check against
 * @return boolean username available or not
 */		
function userExistence( $userName ) {
	global $obj, $userEx;
	
	if ($userName == $obj->user_username) {
		$userEx = true;
	}
}
?>