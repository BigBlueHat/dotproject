<?php
/**
* @package dotproject
* @subpackage core
* @license http://opensource.org/licenses/bsd-license.php BSD License
*/

//
// New password code based oncode from Mambo Open Source Core
// www.mamboserver.com | mosforge.net
//
function sendNewPass() {
	global $AppUI;

	$_live_site = $AppUI->cfg['base_url'];
	$_sitename = $AppUI->cfg['company_name'];

	// ensure no malicous sql gets past
	$checkusername = trim( dPgetParam( $_POST, 'checkusername', '') );
	$checkusername = db_escape( $checkusername );
	$confirmEmail = trim( dPgetParam( $_POST, 'checkemail', '') );
	$confirmEmail = strtolower( db_escape( $confirmEmail ) );

	$query = "SELECT user_id FROM users"
	. "\nWHERE user_username='$checkusername' AND LOWER(user_email)='$confirmEmail'"
	;
	if (!($user_id = db_loadResult($query)) || !$checkusername || !$confirmEmail) {
		$AppUI->setMsg( 'Invalid username or email.' );
		$AppUI->redirect();
	}
	
	$newpass = makePass();
	$message = "The user account $checkusername has this email associated with it.\n"
		."A web user from $_live_site has just requested that a new password be sent.\n\n"
		." Your New Password is: $newpass\n\nIf you didn't ask for this, don't worry."
		." You are seeing this message, not them. If this was an error just login with your"
		." new password and then change your password to what you would like it to be.";
	$subject = "$_sitename :: New password for - $checkusername";
	$headers = "";
	$headers .= "From: dotproject\r\n";
	//$headers .= "Reply-To: <".$adminEmail.">\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-MSMail-Priority: Low\r\n";
	$headers .= "X-Mailer: dotproject\r\n";
	mail( $confirmEmail, $subject, $message, $headers );

	$newpass = md5( $newpass );
	$sql = "UPDATE users SET user_password='$newpass' WHERE user_id='$user_id'";
	$cur = db_exec( $sql );
	if (!$cur) {
		die("SQL error" . $database->stderr(true));
	} else {
		$AppUI->setMsg( 'New User Password created and emailed to you' );
		$AppUI->redirect();
	}
}

function makePass(){
	$makepass="";
	$salt = "abchefghjkmnpqrstuvwxyz0123456789";
	srand((double)microtime()*1000000);
	$i = 0;
	while ($i <= 7) {
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$makepass = $makepass . $tmp;
		$i++;
	}
	return ($makepass);
}
?>
