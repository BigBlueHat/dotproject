<?php /* $Id$ */

/**  LICENSE  **

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

error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);

// required includes for start-up
$dPconfig = array();
require_once( "./classes/ui.class.php" );

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = @$_GET['suppressHeaders'];

// manage the session variable(s)
session_name( 'dotproject' );
session_start();
session_register( 'AppUI' ); 
  
// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

require_once( "./includes/config.php" );

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();
 
// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );

// load default preferences if not logged in
if ($AppUI->doLogin()) {
    $AppUI->loadPrefs( 0 );
}

// check if the user is trying to log in
if (isset($_POST['login'])) {
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	$ok = $AppUI->login( $username, $password );
	if (!$ok) {
		@include_once( "./locales/core.php" );
		$AppUI->setMsg( 'Login Failed' );
		$AppUI->redirect();
	}
}

// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

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

	// output the character set header
	if (isset( $locale_char_set )) {
		 header("Content-type: text/html;charset=$locale_char_set");
	}

	$AppUI->savePlace();
	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

// see if a unix timestamp has been passed in the url;
if (isset( $_REQUEST['uts'] )) {
    $AppUI->setDaySelected( $_REQUEST['uts'] );
}

// bring in the rest of the support and localisation files
require_once( "./includes/main_functions.php" );
require_once( "./includes/permissions.php" );

// set the module and action from the url
$m = isset( $_GET['m'] ) ? $_GET['m'] : getReadableModule();
$u = isset( $_GET['u'] ) ? $_GET['u'] : '';
$a = isset( $_GET['a'] )? $_GET['a'] : 'index';

@include_once( "./functions/" . $m . "_func.php" );
// check overall module permissions
// these can be further modified by the included action files
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );
$canAuthor = $canEdit;
$canDelete = $canEdit;
	// legacy support
	$denyRead = !$canRead;
	$denyEdit = !$canEdit;

// load module based locale settings
@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );
setlocale( LC_TIME, $AppUI->user_locale );

if ( !$suppressHeaders ) {
	// output the character set header
	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}
}

// bounce the user if they don't have at least read access
// however, the public module is accessible by anyone
if (!$canRead && $m != 'public') {
	$AppUI->redirect( "m=public&a=access_denied" );
}
// include the module class file
@include_once( "./modules/$m/$m.class.php" );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    //require("./dosql/" . $_REQUEST["dosql"] . ".php");
    require ("./modules/$m/" . $_REQUEST["dosql"] . ".php");
}

// start output proper
include "./style/$uistyle/overrides.php";
ob_start();
if(!$suppressHeaders) {
	require "./style/$uistyle/header.php";
}
require "./modules/$m/" . ($u ? "$u/" : "") . "$a.php";
if(!$suppressHeaders) {
	require "./style/$uistyle/footer.php";
}
ob_end_flush();
?>
