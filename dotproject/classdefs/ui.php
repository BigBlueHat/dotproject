<?php
##
##	Application User Interface class
##

// Message No Constants
define( 'UI_MSG_OK', 1 );
define( 'UI_MSG_ALERT', 2 );
define( 'UI_MSG_WARNING', 3 );
define( 'UI_MSG_ERROR', 4 );

// global variable holding the translation array
$GLOBALS['translate'] = array();

define( "UI_CASE_UPPER", 1 );
define( "UI_CASE_LOWER", 2 );
define( "UI_CASE_UPPERFIRST", 3 );

class CAppUI {
	var $state;		// generic array for holding the state of anything
// current user parameters
	var $user_id;
	var $user_first_name;
	var $user_last_name;
	var $user_company;
	var $user_department;
	var $user_type;
	var $user_prefs;
// localisation
	var $user_locale;
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english
// supported languages
	var $locales = array(
		'cs' => 'Czech',
		'de' => 'German',
		'en' => 'English',
		'es' => 'Spanish',
		'fr' => 'French',
		'pt_br' => 'Portugese-Brazilian'
	);
	var $locale_warn = true;	// warn when a translation is not found
// theming
	var $styles = array(
		'default' => 'Classic dotproject',
		'demo1' => 'A demo style'
	);
// message handling
	var $msg = "";
	var $msgNo = "";
	var $defaultRedirect;

// CAppUI Constructor
	function CAppUI() {
		GLOBAL $debug;
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
		$this->user_type = 0;

		$this->defaultRedirect = "";
// set up the default preferences
		$sql = "
		SELECT pref_name, pref_value
		FROM user_preferences
		WHERE pref_user = 0
		";
		writeDebug( $sql, 'Default Preferences SQL', __FILE__, __LINE__ );

		$this->user_locale = $this->base_locale;
		$this->user_prefs = db_loadHashList( $sql );
	}
// localisation
	function setUserLocale( $loc ) {
		$this->user_locale = $loc;
	}
/*
	Translate string to the local language [same form as the gettext abbreviation]
	This is the order of precedence:
	If the key exists in the lang array, return the value of the key
	If no key exists and the base lang is the same as the local lang, just return the string
	If this is not the base lang, then return string with a red star appended to show
	that a translation is required.
*/
	function _( $str, $case=0 ) {
		if (empty( $str )) {
			return '';
		}
		$x = @$GLOBALS['translate'][$str];
		if ($x) {
			$str = $x;
		} else if ($this->locale_warn) {
			if ($this->base_locale != $this->user_locale ||
				($this->base_locale == $this->user_locale && !in_array( $str, @$GLOBALS['translate'] )) ) {
				$str .= '<span class="no_">*</span>';
			}
		}
		switch ($case) {
			case UI_CASE_UPPER:
				$str = strtoupper( $str );
				break;
			case UI_CASE_LOWER:
				$str = strtolower( $str );
				break;
			case UI_CASE_UPPERFIRST:
				break;
		}
		return $str;
	}
// Save the current url query string
	function savePlace( $query='' ) {
		$this->state['SAVEDPLACE'] = $query ? $query : $_SERVER['QUERY_STRING'];
	}
	function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
// Get the saved place (usually one that could contain an edit button)
	function getPlace() {
		return $this->state['SAVEDPLACE'];
	}
// redirects to a new page
// (usually to prevent nasties from doing a browser refresh after a db update)
	function redirect( $params='' ) {
		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state['SAVEDPLACE']) ? $this->state['SAVEDPLACE'] : $this->defaultRedirect;
		}
		echo "<script language=\"javascript\">"
		. "window.location='?$params'"
		. "</script>";
	}

// Set the page message (displayed on page construction
	function setMsg( $msg, $msgNo=0, $append=false ) {
		$this->msg = $append ? $msg : $this->msg.$msg;
		$this->msgNo = $msgNo;
	}
// Display the message, format and display icon
	function getMsg( $reset=true ) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch( $this->msgNo ) {
		case UI_MSG_OK:
			$img = '<img src="./images/obj/tick.gif" width="15" height="15" border="0" alt="">';
			$class = "message";
			break;
		case UI_MSG_ALERT:
			$img = '<img src="./images/obj/alert.gif" width="16" height="11" border="0" alt="">';
			$class = "message";
			break;
		case UI_MSG_WARNING:
			$img = '<img src="./images/obj/warning.gif" width="14" height="14" border="0" alt="">';
			$class = "warning";
			break;
		case UI_MSG_ERROR:
			$img = '<img src="./images/obj/error.gif" width="14" height="14" border="0" alt="">';
			$class = "error";
			break;
		default:
			$class = "message";
			break;
		}
		if ($reset) {
			$this->msg = '';
			$this->msgNo = 0;
		}
		return $msg ? "$img<span class=\"$class\">".$this->_( $msg )."</span>" : '';
	}

	function setState( $label, $tab ) {
		$this->state[$label] = $tab;
	}

	function getState( $label ) {
		return array_key_exists( $label, $this->state) ? $this->state[$label] : NULL;
	}

	function login( $username, $password ) {
		GLOBAL $secret, $debug, $host_locale;
		$sql = "
		SELECT
			user_id, user_first_name, user_last_name, user_company, user_department, user_type
		FROM users, permissions
		WHERE user_username = '$username'
			AND user_password = password('$password')
			AND users.user_id = permissions.permission_user
			AND permission_value <> 0
		";
		
		writeDebug( $sql, 'Login SQL', __FILE__, __LINE__ );

		if( !db_loadObject( $sql, $this ) ) {
			return false;
		}
// load the user preferences
		$sql = "
		SELECT pref_name, pref_value
		FROM user_preferences
		WHERE pref_user = $this->user_id
		";
		writeDebug( $sql, 'User Preferences SQL', __FILE__, __LINE__ );

		$prefs = db_loadHashList( $sql );
		$this->user_prefs = array_merge( $this->user_prefs, db_loadHashList( $sql ) );
		$this->user_locale = @$this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : $host_locale;

		$this->secret = md5( $this->user_first_name.$secret.$this->user_last_name );

		$this->logout();
		return true;
	}

	function logout() {
	}

	function doLogin() {
		return ($this->user_id < 0) ? true : false;
	}

	function getPref( $name ) {
		return @$this->user_prefs[$name];
	}
}
/*
	Tabbed box class
*/
class CTabBox {
	var $tabs=NULL;
	var $active=NULL;
	var $baseHRef=NULL;
	var $baseInc;

	function CTabBox( $baseHRef='', $baseInc='.', $active=0 ) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? "$baseHRef&" : "?");
		$this->baseInc = $baseInc;
	}

	function add( $file, $title ) {
		$this->tabs[] = array( $file, $title );
	}

	function show( $extra='' ) {
		GLOBAL $AppUI, $root_dir;
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		if (@$AppUI->getPref( 'TABVIEW' ) == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="98%"><tr><td nowrap="nowrap">';
			$s .= '<a href="'.$this->baseHRef.'tab=0">'.$AppUI->_('tabbed').'</a> : ';
			$s .= '<a href="'.$this->baseHRef.'tab=-1">'.$AppUI->_('flat').'</a>';
			$s .= '</td>'.$extra.'</tr></table>';
			echo $s;
		} else {
			echo '<img src="./images/shim.gif" height="10" width="1">';
		}

		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) {
		// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="98%">';
			foreach ($this->tabs as $v) {
				echo '<tr><td><b>'.$AppUI->_($v[1]).'</b></td></tr>';
				echo '<tr><td>';
				include "$root_dir/$this->baseInc/$v[0].php";
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
		// tabbed view
			$s = '<table width="98%" border=0 cellpadding="3" cellspacing=0><tr>';
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= '<td nowrap="nowrap" class="tabsp"><img src="./images/shim.gif" height=1 width=1></td>';
				$s .= '<td nowrap="nowrap" class="'.$class.'"><a href="'.$this->baseHRef.'tab='.$k.'">'.$AppUI->_($v[1]).'</a></td>';
			}
			$s .= '<td nowrap="nowrap" class="tabsp" width="100%">&nbsp;</td>';
			$s .= '</tr><tr><td width="100%" colspan="99" class="tabox">';
			echo $s;
			require $this->baseInc.'/'.$this->tabs[$this->active][0].'.php';
			echo '</td></tr></table>';
		}
	}
}

?>