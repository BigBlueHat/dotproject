<?php /* CLASSDEFS $Id$ */
##
##	Application User Interface class
##
require_once( "./classdefs/date.php" );

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
// active project
	var $project_id;
// a selected date
	var $day_selected;
// localisation
	var $user_locale;
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english
// warn when a translation is not found
	var $locale_warn = false;
// the string appended to untranslated string or unfound keys
	var $locale_alert = '^';
// theming
	var $styles = array();
// message handling
	var $msg = '';
	var $msgNo = '';
	var $defaultRedirect = '';
// configuration variable array
	var $cfg=null;

// CAppUI Constructor
	function CAppUI() {
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
		$this->user_type = 0;

		$this->project_id = 0;
		$this->day_selected = new CDate();

		$this->defaultRedirect = "";
// set up the default preferences
		$this->user_locale = $this->base_locale;
		$this->user_prefs = array();
	}

	function setConfig( &$cfg ) {
		$this->cfg = $cfg;
	}

	function checkStyle() {
		// check if default user's uistyle is installed
		$uistyle = $this->getPref("UISTYLE");

		if ($uistyle && !is_dir("{$this->cfg['root_dir']}/style/$uistyle")) {
			// fall back to host_style if user style is not installed
			$this->setPref( 'UISTYLE', $this->cfg['host_style'] );
		}
	}

	function readDirs( $path ) {
		$dirs = array();
		$d = dir( "{$this->cfg['root_dir']}/$path" );
		while (false !== ($name = $d->read())) {
			if(is_dir( "{$this->cfg['root_dir']}/$path/$name" ) && $name != "." && $name != ".." && $name != "CVS") {
				$dirs[$name] = $name;
			}
		}
		$d->close();
		return $dirs;
	}

// localisation
	function setUserLocale( $loc='' ) {
		if ($loc) {
			$this->user_locale = $loc;
		} else {
			$this->user_locale = @$this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : $this->cfg['host_locale'];
		}
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
		$str = trim($str);
		if (empty( $str )) {
			return '';
		}
		$x = @$GLOBALS['translate'][$str];
		if ($x) {
			$str = $x;
		} else if ($this->locale_warn) {
			if ($this->base_locale != $this->user_locale ||
				($this->base_locale == $this->user_locale && !in_array( $str, @$GLOBALS['translate'] )) ) {
				$str .= $this->locale_alert;
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
// set the display of warning for untranslated strings
	function setWarning( $state=true ) {
		$temp = $this->locale_warn;
		$this->locale_warn = $state;
		return $temp;
	}
// Save the current url query string
	function savePlace( $query='' ) {
		$this->state['SAVEDPLACE-1'] = @$this->state['SAVEDPLACE'];
		$this->state['SAVEDPLACE'] = $query ? $query : @$_SERVER['QUERY_STRING'];
	}
	function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
// Get the saved place (usually one that could contain an edit button)
	function getPlace() {
		return @$this->state['SAVEDPLACE'];
	}
// redirects to a new page
// (usually to prevent nasties from doing a browser refresh after a db update)
	function redirect( $params='', $hist='' ) {
		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state["SAVEDPLACE$hist"]) ? $this->state["SAVEDPLACE$hist"] : $this->defaultRedirect;
		}
		echo "<script language=\"javascript\">window.location='index.php?$params'</script>";
		exit();
	}

// Set the page message (displayed on page construction)
	function setMsg( $msg, $msgNo=0, $append=false ) {
		$msg = $this->_( $msg );
		$this->msg = $append ? $this->msg.$msg : $msg;
		$this->msgNo = $msgNo;
	}
// Display the message, format and display icon
	function getMsg( $reset=true ) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch( $this->msgNo ) {
		case UI_MSG_OK:
			$img = '<img src="./images/obj/tick.gif" width="15" height="15" border="0" alt="" />';
			$class = "message";
			break;
		case UI_MSG_ALERT:
			$img = '<img src="./images/obj/alert.gif" width="16" height="11" border="0" alt="" />';
			$class = "message";
			break;
		case UI_MSG_WARNING:
			$img = '<img src="./images/obj/warning.gif" width="14" height="14" border="0" alt="" />';
			$class = "warning";
			break;
		case UI_MSG_ERROR:
			$img = '<img src="./images/obj/error.gif" width="14" height="14" border="0" alt="" />';
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
		return $msg ? "$img<span class=\"$class\">$msg</span>" : '';
	}

	function setState( $label, $tab ) {
		$this->state[$label] = $tab;
	}

	function getState( $label ) {
		return array_key_exists( $label, $this->state) ? $this->state[$label] : NULL;
	}

	function login( $username, $password ) {
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
		$this->loadPrefs( $this->user_id );
		$this->setUserLocale();
		$this->checkStyle();
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

	function setPref( $name, $val ) {
		$this->user_prefs[$name] = $val;
	}

	function loadPrefs( $uid=0 ) {
		$sql = "SELECT pref_name, pref_value FROM user_preferences WHERE pref_user = $uid";
		//writeDebug( $sql, "Preferences for user $uid, SQL", __FILE__, __LINE__ );
		$prefs = db_loadHashList( $sql );
		$this->user_prefs = array_merge( $this->user_prefs, db_loadHashList( $sql ) );
	}

	function getProject() {
		return $this->project_id;
	}

	function setProject( $id=0 ) {
		$this->project_id = $id;
	}

	function getDaySelected() {
		return $this->day_selected->getTimestamp();
	}

	function setDaySelected( $ts=0 ) {
		$this->day_selected->setTimestamp( $ts );
	// zero the time so that 'days' can be compared
		$this->day_selected->setTime( 0, 0, 0 );
	}
// --- Module connectors
	function getInstalledModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name
		FROM modules
		ORDER BY mod_directory
		";
		return (db_loadHashList( $sql ));
	}

	function getActiveModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name
		FROM modules
		WHERE mod_active > 0
		ORDER BY mod_directory
		";
		return (db_loadHashList( $sql ));
	}

	function getMenuModules() {
		$sql = "
		SELECT mod_directory, mod_ui_name, mod_ui_icon
		FROM modules
		WHERE mod_active > 0 AND mod_ui_active > 0
		ORDER BY mod_ui_order
		";
		return (db_loadList( $sql ));
	}
}
/*
	Tabbed box core class
	The show function may be overrided by the style
*/
class CTabBox_core {
	var $tabs=NULL;
	var $active=NULL;
	var $baseHRef=NULL;
	var $baseInc;

	function CTabBox( $baseHRef='', $baseInc='', $active=0 ) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? "$baseHRef&" : "?");
		$this->baseInc = $baseInc;
	}

	function getTabName( $idx ) {
		return $this->tabs[$idx][1];
	}

	function add( $file, $title ) {
		$this->tabs[] = array( $file, $title );
	}

	function show( $extra='' ) {
		GLOBAL $AppUI;
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		if (@$AppUI->getPref( 'TABVIEW' ) == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
			$s .= '<a href="'.$this->baseHRef.'tab=0">'.$AppUI->_('tabbed').'</a> : ';
			$s .= '<a href="'.$this->baseHRef.'tab=-1">'.$AppUI->_('flat').'</a>';
			$s .= '</td>'.$extra.'</tr></table>';
			echo $s;
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>'.$extra.'</tr></table>';
			} else {
				echo '<img src="./images/shim.gif" height="10" width="1" />';
			}
		}

		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) {
		// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $v) {
				echo '<tr><td><strong>'.$AppUI->_($v[1]).'</strong></td></tr>';
				echo '<tr><td>';
				include $this->baseInc.$v[0].".php";
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
		// tabbed view
			$s = '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr>';
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= '<td width="1%" nowrap="nowrap" class="tabsp"><img src="./images/shim.gif" height="1" width="1" alt="" /></td>';
				$s .= '<td width="1%" nowrap="nowrap" class="'.$class.'"><a href="'.$this->baseHRef.'tab='.$k.'">'.$AppUI->_($v[1]).'</a></td>';
			}
			$s .= '<td nowrap="nowrap" class="tabsp">&nbsp;</td>';
			$s .= '</tr><tr><td width="100%" colspan="'.(count($this->tabs)*2 + 1).'" class="tabox">';
			echo $s;
			require $this->baseInc.$this->tabs[$this->active][0].'.php';
			echo '</td></tr></table>';
		}
	}
}

class CTitleBlock_core {
	var $title='';
	var $icon='';
	var $module='';
	var $cells=null;
	var $helpref='';

	function CTitleBlock_core( $title, $icon='', $module='', $helpref='' ) {
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
		$this->showhelp = !getDenyRead( 'help' );
	}

	function addCell( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells1[] = array( $attribs, $data, $prefix, $suffix );
	}

	function addCrumb( $link, $label, $icon='' ) {
		$this->crumbs[$link] = array( $label, $icon );
	}

	function addCrumbRight( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells2[] = array( $attribs, $data, $prefix, $suffix );
	}

	function show() {
		global $AppUI;
		$CR = "\n";
		$CT = "\n\t";
		$s = $CR . '<table width="100%" border="0" cellpadding="1" cellspacing="1">';
		$s .= $CR . '<tr>';
		if ($this->icon) {
			$s .= $CR . '<td width="36"><img src="' . dPFindImage( $this->icon, $this->module ) . '" height="36" alt="" border="0" /></td>';
		}
		$s .= $CR . '<td align="left" width="100%" nowrap="nowrap"><h1>' . $AppUI->_($this->title) . '</h1></td>';
		foreach ($this->cells1 as $c) {
			$s .= $c[2] ? $CR . $c[2] : '';
			$s .= $CR . '<td align="right" nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
			$s .= $c[1] ? $CT . $c[1] : '&nbsp;';
			$s .= $CR . '</td>';
			$s .= $c[3] ? $CR . $c[3] : '';
		}
		if ($this->showhelp) {
			$s .= '<td nowrap="nowrap" width="20" align="right">';
			$s .= $CT . contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', $this->helpref );
			$s .= $CR . '</td>';
		}
		$s .= $CR . '</tr>';
		$s .= $CR . '</table>';

		if (count( $this->crumbs ) || count( $this->cells2 )) {
			$crumbs = array();
			foreach ($this->crumbs as $k => $v) {
				$t = $v[1] ? '<img src="' . dPfindImage( $v[1], $this->module ) . '" border="" alt="" />&nbsp;' : '';
				$t .= $AppUI->_( $v[0] );
				$crumbs[] = "<a href=\"$k\">$t</a>";
			}
			$s .= $CR . '<table border="0" cellpadding="4" cellspacing="0" width="100%">';
			$s .= $CR . '<tr>';
			$s .= $CR . '<td nowrap="nowrap">';
			$s .= $CT . implode( ' <strong>:</strong> ', $crumbs );
			$s .= $CR . '</td>';

			foreach ($this->cells2 as $c) {
				$s .= $c[2] ? $CR . $c[2] : '';
				$s .= $CR . '<td align="right" nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
				$s .= $c[1] ? $CT . $c[1] : '&nbsp;';
				$s .= $CR . '</td>';
				$s .= $c[3] ? $CR . $c[3] : '';
			}

			$s .= '</tr></table>';
		}
		echo "$s";
	}
}
?>
