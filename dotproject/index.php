<?php /* $Id$ */

/**  BSD LICENSE  **

Copyright (c) 2003, The dotProject Development Team sf.net/projects/dotproject
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.
* Neither the name of the dotproject development team (past or present) nor the
  names of its contributors may be used to endorse or promote products derived
  from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

**/

ini_set('display_errors', 1); // Ensure errors get to the user.
error_reporting( E_ALL & ~E_NOTICE);

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
//error_reporting( E_ALL );


/**
* @var int dPrunLevel Container for Information about available Services
* 0 = no config file available, no database available
* 1 = config file existing, but no db available
* 2 = cfg and db available
*/
$dPrunLevel = 0;

if ( is_file( "./includes/config.php" ) ) {	// allow the install module to run without config file
	$dPrunLevel = 1;
} elseif (! ($_GET['m'] == 'install') ) {
	die( "Fatal Error.  You haven't created a config file yet." );
}


// required includes for start-up
$dPconfig = array();
// allow the install module to run without config file
if ($dPrunLevel > 0) {
	require_once( "./includes/config.php" );
}

if ( ($_GET['m'] == 'install') ) {
	include("./modules/install/install.inc.php");
}

require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = dPgetParam( $_GET, 'suppressHeaders', false );

// manage the session variable(s)
session_name( 'dotproject' );
if (ini_get( 'session.auto_start' ) > 0) {
	session_write_close();
}
session_start();
session_register( 'AppUI' );
session_register( 'Installer' );

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0, false");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

// Check that the user has correctly set the root directory
// If not found, try guessing it.
$config_file = "{$dPconfig['root_dir']}/includes/config.php";
$config_msg = false;
if (! is_file($config_file) && !( $_GET['m'] == 'install') ) {
	// First check that we aren't looking at old data.
	// We don't do this first as it has performance implications.
	clearstatcache();
	if (! is_file($config_file)) {
		// Still no good, set it to where we are,
		$dPconfig['root_dir'] = dirname(__FILE__);
		$config_msg = "Root directory in configuration file probably incorrect";
	}
}
// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = !( $_GET['m'] == 'install' ) ? new CAppUI() : new IAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->checkStyle();
if ($config_msg) {
	$AppUI->setMsg($config_msg, UI_MSG_WARNING);
}

// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );

// load the db handler
// allow the install module to run without config file
if ($dPrunLevel > 0) {
	require_once( "./includes/db_connect.php" );
}
require_once( "./misc/debug.php" );

// load default preferences if not logged in
if ($AppUI->doLogin()) {
	if ( !( $_GET['m'] == 'install' && $dPrunLevel < 2 ) ) {	// allow the install module to run without db
    		$AppUI->loadPrefs( 0 );
	}
}

// check is the user needs a new password
if (dPgetParam( $_POST, 'lostpass', 0 )) {
	$uistyle = $dPconfig['host_style'];
	$AppUI->setUserLocale();
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );
	setlocale( LC_TIME, $AppUI->user_locale );
	if (dPgetParam( $_REQUEST, 'sendpass', 0 )) {
		require "./includes/sendpass.php";
		sendNewPass();
	} else {
		require "./style/$uistyle/lostpass.php";
	}
	exit();
}


// check if the user is trying to log in
if (isset($_POST['login'])) {

	$username = dPgetParam( $_POST, 'username', '' );
	$password = dPgetParam( $_POST, 'password', '' );
	$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
	$ok = $AppUI->login( $username, $password );
	if (!$ok) {
		@include_once( "./locales/core.php" );
		$AppUI->setMsg( 'Login Failed' );
	}
	$AppUI->redirect( "$redirect" );
}


// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];



// clear out main url parameters
$m = '';
$a = '';
$u = '';

// check if we are logged in
if ($AppUI->doLogin()) {
    $AppUI->setUserLocale();
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );
	setlocale( LC_TIME, $AppUI->user_locale );

	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false) {
		$redirect = '';
	}

	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}

	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

if ( !( $_GET['m'] == 'install' && $dPrunLevel < 2 ) ) {	// allow the install module to run without db

	// bring in the rest of the support and localisation files
	require_once( "./includes/permissions.php" );

	// set the module from the url
	$m = $AppUI->checkFileName(dPgetParam( $_GET, 'm', getReadableModule() ));

} else {
	$m = 'install';
}

// set the action from the url
$a = $AppUI->checkFileName(dPgetParam( $_GET, 'a', 'index' ));

/* This check for $u implies that a file located in a subdirectory of higher depth than 1
 * in relation to the module base can't be executed. So it would'nt be possible to
 * run for example the file module/directory1/directory2/file.php
 * Also it won't be possible to run modules/module/abc.zyz.class.php for that dots are
 * not allowed in the request parameters.
*/

$u = $AppUI->checkFileName(dPgetParam( $_GET, 'u', '' ));

// load module based locale settings
@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );

$user_locale = $AppUI->user_locale;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // This is a server using Windows, locales screwed up, not ISO standard
    switch ($user_locale) {
    	case "es":
    		$user_locale = "sp";
    		break;
    }
}
setlocale( LC_TIME, $user_locale );

@include_once( "./functions/" . $m . "_func.php" );

if ( ( $_GET['m'] == 'install' && $dPrunLevel < 2 ) ) {	// allow the install module to run without db
	// present some trivial permission functions
	function getDenyRead( $m ){ return false; }
	function getDenyEdit( $m ){ return false; }
}



// TODO: canRead/Edit assignements should be moved into each file

// check overall module permissions
// these can be further modified by the included action files
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );
$canAuthor = $canEdit;
$canDelete = $canEdit;

if ( !$suppressHeaders ) {
	// output the character set header
	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}
}

/*
 *
 * TODO: Permissions should be handled by each file.
 * Denying access from index.php still doesn't asure
 * someone won't access directly skipping this security check.
 *
// bounce the user if they don't have at least read access
if (!(
	  // however, some modules are accessible by anyone
	  $m == 'public' ||
	  ($m == 'admin' && $a == 'viewuser')
	  )) {
	if (!$canRead) {
		$AppUI->redirect( "m=public&a=access_denied" );
	}
}
*/

// include the module class file
@include_once( $AppUI->getModuleClass( $m ) );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    //require("./dosql/" . $_REQUEST["dosql"] . ".php");
    require ("./modules/$m/" . $AppUI->checkFileName($_REQUEST["dosql"]) . ".php");
}

// start output proper
include "./style/$uistyle/overrides.php";
ob_start();
if(!$suppressHeaders) {
	require "./style/$uistyle/header.php";
}
$module_file = "./modules/$m/" . ($u ? "$u/" : "") . "$a.php";
if (file_exists($module_file))
  require $module_file;
else
{
// TODO: make this part of the public module? 
// TODO: internationalise the string.
  $titleBlock = new CTitleBlock('Warning', 'log-error.gif');
  $titleBlock->show();

  echo $AppUI->_("Missing file. Possible Module \"$m\" missing!");
}
if(!$suppressHeaders) {
	require "./style/$uistyle/footer.php";
}
ob_end_flush();
?>
