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
error_reporting(E_ALL & ~E_NOTICE);

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
// error_reporting( E_ALL );


/**
* @var int dPrunLevel Container for Information about available Services
* 0 = no config file available, no database available
* 1 = config file existing, but no db available
* 2 = cfg and db available
*/
$dPrunLevel = 0;
$loginFromPage = 'index.php';

$is_installer = false;
if ( isset($_GET['m']) && $_GET['m'] == 'install' ) {
	$is_installer = true;
}

if ( is_file( "./includes/config.php" ) ) {	// allow the install module to run without config file
	$dPrunLevel = 1;
} elseif (! $is_installer ) {
	die( "Fatal Error.  You haven't created a config file yet." );
}


// required includes for start-up
$dPconfig = array();
// allow the install module to run without config file
if ($dPrunLevel > 0) {
	require_once( "./includes/config.php" );
}

if ($is_installer) {
	include("./modules/install/install.inc.php");
}

if (! isset($GLOBALS['OS_WIN']))
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, "WIN") !== false);

// tweak for pathname consistence on windows machines
$functions_file = realpath('./includes/main_functions.php');
if ($GLOBALS['OS_WIN'])
	$functions_file = strtolower($functions_file);

require_once $functions_file;
require_once dpRealPath( "./classes/ui.class.php" );
require_once dpRealPath( "./includes/db_adodb.php" );
require_once dpRealPath( "./classes/permissions.class.php" );
require_once dpRealPath( "./includes/session.php" );

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = dPgetParam( $_GET, 'suppressHeaders', false );

// manage the session variable(s)
dPsessionStart(array('AppUI', 'Installer'));

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

// Check that the user has correctly set the root directory
// If not found, try guessing it.
$config_file = "{$dPconfig['root_dir']}/includes/config.php";
$config_msg = false;
if (! is_file($config_file) && !$is_installer ) {
	// First check that we aren't looking at old data.
	// We don't do this first as it has performance implications.
	clearstatcache();
	if (! is_file($config_file)) {
		// Still no good, set it to where we are,
		$dPconfig['root_dir'] = dirname(__FILE__);
		$config_msg = "Root directory in configuration file probably incorrect";
	}
}


// allow the install module to run without config file
// load the db handler
if ($dPrunLevel > 0) {
	require_once dpRealPath( "./includes/db_connect.php" );
}
// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id))
    {
        $AppUI =& $_SESSION['AppUI'];
	$user_id = $AppUI->user_id;
        addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
    }

	$_SESSION['AppUI'] = $is_installer ? new IAppUI : new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];
$last_insert_id =$AppUI->last_insert_id;

$AppUI->checkStyle();
if ($config_msg) {
	$AppUI->setMsg($config_msg, UI_MSG_WARNING);
}

// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getSystemClass( 'query' ) );

require_once dPRealPath( "./misc/debug.php" );

//Function for update lost action in user_access_log
$AppUI->updateLastAction($last_insert_id);
// load default preferences if not logged in
if ($AppUI->doLogin()) {
	if ( !($is_installer && $dPrunLevel < 2 ) ) {	// allow the install module to run without db
    		$AppUI->loadPrefs( 0 );
	}
}

//Function register logout in user_acces_log
if (isset($user_id) && isset($_GET['logout'])){
    $AppUI->registerLogout($user_id);
}

// check is the user needs a new password
if (dPgetParam( $_POST, 'lostpass', 0 )) {
	$uistyle = $dPconfig['host_style'];
	$AppUI->setUserLocale();
	@include_once dPRealPath( "./locales/$AppUI->user_locale/locales.php" );
	@include_once dPRealPath( "./locales/core.php" );
	setlocale( LC_TIME, $AppUI->user_lang );
	if (dPgetParam( $_REQUEST, 'sendpass', 0 )) {
		require  dPRealPath("./includes/sendpass.php");
		sendNewPass();
	} else {
		require  dPRealPath("./style/$uistyle/lostpass.php");
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
		@include_once dPRealPath( "./locales/core.php" );
		$AppUI->setMsg( 'Login Failed' );
	} else {
	           //Register login in user_acces_log
	           $AppUI->registerLogin();
	}
        addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
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
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );
	setlocale( LC_TIME, $AppUI->user_lang );
	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false) {
		$redirect = '';
	}

	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}

	require dPrealPath("./style/$uistyle/login.php");
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}
$AppUI->setUserLocale();

if ( !( $is_installer && $dPrunLevel < 2 ) ) {	// allow the install module to run without db
	// bring in the rest of the support and localisation files
	require_once dPrealPath( "./includes/permissions.php" );
}

$def_a = 'index';
if ( $is_installer && $dPrunLevel < 2 ) {	// allow the install module to run without db
	$m = 'install';
} else if (! isset($_GET['m']) && !empty($dPconfig['default_view_m'])) {
  	$m = $dPconfig['default_view_m'];
	$def_a = !empty($dPconfig['default_view_a']) ? $dPconfig['default_view_a'] : $def_a;
	$tab = $dPconfig['default_view_tab'];
} else {
	// set the module from the url
	$m = $AppUI->checkFileName(dPgetParam( $_GET, 'm', getReadableModule() ));
}
// set the action from the url
$a = $AppUI->checkFileName(dPgetParam( $_GET, 'a', $def_a));

/* This check for $u implies that a file located in a subdirectory of higher depth than 1
 * in relation to the module base can't be executed. So it would'nt be possible to
 * run for example the file module/directory1/directory2/file.php
 * Also it won't be possible to run modules/module/abc.zyz.class.php for that dots are
 * not allowed in the request parameters.
*/

$u = $AppUI->checkFileName(dPgetParam( $_GET, 'u', '' ));

// load module based locale settings
@include_once dPrealPath( "./locales/$AppUI->user_locale/locales.php" );
@include_once dPrealPath( "./locales/core.php" );

setlocale( LC_TIME, $AppUI->user_lang );

@include_once dPrealPath( "./functions/" . $m . "_func.php" );

if ( ( $is_installer && $dPrunLevel < 2 ) ) {	// allow the install module to run without db
	// present some trivial permission functions
	function getDenyRead( $m ){ return false; }
	function getDenyEdit( $m ){ return false; }
}



// TODO: canRead/Edit assignements should be moved into each file

// check overall module permissions
// these can be further modified by the included action files
$perms =& $AppUI->acl();
$canAccess = $perms->checkModule($m, 'access');
$canRead = $perms->checkModule($m, 'view');
$canEdit = $perms->checkModule($m, 'edit');
$canAuthor = $perms->checkModule($m, 'add');
$canDelete = $perms->checkModule($m, 'delete');

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

// include the module class file - we use file_exists instead of @ so
// that any parse errors in the file are reported, rather than errors
// further down the track.
$modclass = $AppUI->getModuleClass($m);
if (file_exists($modclass))
	include_once( $modclass );
if ($u && file_exists("./modules/$m/$u/$u.class.php"))
	include_once dPrealPath( "./modules/$m/$u/$u.class.php" );

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    //require("./dosql/" . $_REQUEST["dosql"] . ".php");
    require  dPrealPath("./modules/$m/" . ($u ? "$u/" : "") . $AppUI->checkFileName($_REQUEST["dosql"]) . ".php");
}

// start output proper
include  dPrealPath("./style/$uistyle/overrides.php");
ob_start();
if(!$suppressHeaders) {
	require dPrealPath("./style/$uistyle/header.php");
}

if (! isset($_SESSION['all_tabs'][$m]) && !( $is_installer && $dPrunLevel < 2 )) {
	// For some reason on some systems if you don't set this up
	// first you get recursive pointers to the all_tabs array, creating
	// phantom tabs.
	if (! isset($_SESSION['all_tabs']))
		$_SESSION['all_tabs'] = array();
	$_SESSION['all_tabs'][$m] = array();
	$all_tabs =& $_SESSION['all_tabs'][$m];
	foreach ($AppUI->getActiveModules() as $dir => $module)
	{
		$modules_tabs = $AppUI->readFiles('./modules/'.$dir.'/', '^' . $m . '_tab.*\.php');
		foreach($modules_tabs as $tab)
		{
			// Get the name as the subextension
			// cut the module_tab. and the .php parts of the filename 
			// (begining and end)
			$nameparts = explode('.', $tab);
			$filename = substr($tab, 0, -4);
			if (count($nameparts) > 3) {
				$file = $nameparts[1];
				if (! isset($all_tabs[$file]))
					$all_tabs[$file] = array();
				$arr =& $all_tabs[$file];
				$name = $nameparts[2];
			} else {
				$arr =& $all_tabs;
				$name = $nameparts[1];
			}
			$arr[] = array(
				'name' => ucfirst(str_replace('_', ' ', $name)),
				'file' => $dPconfig['root_dir'] . '/modules/' . $dir . '/' . $filename,
				'module' => $dir);
		}
	}
} else {
	$all_tabs =& $_SESSION['all_tabs'][$m];
}

$module_file = "./modules/$m/" . ($u ? "$u/" : "") . "$a.php";
if (file_exists($module_file))
  require dPrealPath($module_file);
else
{
// TODO: make this part of the public module? 
// TODO: internationalise the string.
  $titleBlock = new CTitleBlock('Warning', 'log-error.gif');
  $titleBlock->show();

  echo $AppUI->_("Missing file. Possible Module \"$m\" missing!");
}
if(!$suppressHeaders) {
	require dPrealPath("./style/$uistyle/footer.php");
}
echo '<iframe name="thread" src="about:blank" width="0" height="0" frameborder="0"></iframe>';
ob_end_flush();
?>
