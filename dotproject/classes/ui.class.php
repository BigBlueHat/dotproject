<?php /* CLASSES $Id$ */
/**
 *	@package dotproject
 *	@subpackage core
 *	@license http://opensource.org/licenses/bsd-license.php BSD License
*/

require_once( "./classes/date.class.php" );

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

/**
 *	The Application User Interface Class.
 *
 *	@author Andrew Eddie
 *	@version $Revision$
 */
class CAppUI {
/**
 *	generic array for holding the state of anything
 *	@var array
 */
	var $state;
/** @var int */
	var $user_id;
/** @var string */
	var $user_first_name;
/** @var string */
	var $user_last_name;
/** @var string */
	var $user_company;
/** @var int */
	var $user_department;
/** @var string */
	var $user_email;
/** @var int */
	var $user_type;
/** @var array */
	var $user_prefs;
/** @var int Unix time stamp */
	var $day_selected;

// localisation
/** @var string */
	var $user_locale;
/** @var string */
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english

/** @var string Message string*/
	var $msg = '';
/** @var string */
	var $msgNo = '';
/** @var string Default page for a redirect call*/
	var $defaultRedirect = '';

/** @var array Configuration variable array*/
	var $cfg=null;

/**
 * CAppUI Constructor
 */
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
/**
 *	loads a php class file from the system classes directory
 *	@param string $name class name
 */
	function getSystemClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/classes/$name.class.php";
			}
		}
	}

/**
 *	Loads a php class file from the PEAR classes directory
 *	@param string $name class name
 */
	function getPearClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/lib/PEAR/$name.php";
			}
		}
	}

/**
 *	loads a php class file from the module directory
 *	@param string $name class name
 */
	function getModuleClass( $name=null ) {
		if ($name) {
			if ($root = $this->getConfig( 'root_dir' )) {
				return "$root/modules/$name/$name.class.php";
			}
		}
	}

/**
 *	loads a php class file from the module directory
 *	@param array A named array of configuration variables (usually from config.php)
 */
	function setConfig( &$cfg ) {
		$this->cfg = $cfg;
	}

/**
 *	@param string The name of a configuration setting
 *	@return The value of the setting, otherwise null if the key is not found in the configuration array
 */
	function getConfig( $key ) {
		if (array_key_exists( $key, $this->cfg )) {
			return $this->cfg[$key];
		} else {
			return null;
		}
	}

	function checkStyle() {
		// check if default user's uistyle is installed
		$uistyle = $this->getPref("UISTYLE");

		if ($uistyle && !is_dir("{$this->cfg['root_dir']}/style/$uistyle")) {
			// fall back to host_style if user style is not installed
			$this->setPref( 'UISTYLE', $this->cfg['host_style'] );
		}
	}

/**
 *	Utility function to read the 'directories' under 'path'
 *
 *	This function is used to read the modules or locales installed on the file system.
 *	@param string The path to read.
 *	@return array A named array of the directories (the key and value are identical).
 */
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

/**
 *	Sets the user locale.
 *
 *	Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
 *	@param string Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
 */
	function setUserLocale( $loc='' ) {
		if ($loc) {
			$this->user_locale = $loc;
		} else {
			$this->user_locale = @$this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : $this->cfg['host_locale'];
		}
	}
/**
 *	Translate string to the local language [same form as the gettext abbreviation]
 *
 *	This is the order of precedence:
 *	<ul>
 *	<li>If the key exists in the lang array, return the value of the key
 *	<li>If no key exists and the base lang is the same as the local lang, just return the string
 *	<li>If this is not the base lang, then return string with a red star appended to show
 *	that a translation is required.
 *	</ul>
 *	@param string The string to translate
 *	@param int Option to change the case of the string
 *	@return string
 */
	function _( $str, $case=0 ) {
		$str = trim($str);
		if (empty( $str )) {
			return '';
		}
		$x = @$GLOBALS['translate'][$str];
		if ($x) {
			$str = $x;
		} else if (@$this->cfg['locale_warn']) {
			if ($this->base_locale != $this->user_locale ||
				($this->base_locale == $this->user_locale && !in_array( $str, @$GLOBALS['translate'] )) ) {
				$str .= @$this->cfg['locale_alert'];
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
/**
 *	Set the display of warning for untranslated strings
 *	@param string
 */
	function setWarning( $state=true ) {
		$temp = @$this->cfg['locale_warn'];
		$this->cfg['locale_warn'] = $state;
		return $temp;
	}
/**
 *	Save the url query string
 *
 *	Also saves one level of history.  This is useful for returning from a delete operation where the record more not now exist.  Returning to a view page would be a nonsense in this case.
 *	@param string If not set then the current url query string is used
 */
	function savePlace( $query='' ) {
		if (!$query) {
			$query = @$_SERVER['QUERY_STRING'];
		}
		if ($query != @$this->state['SAVEDPLACE']) {
			$this->state['SAVEDPLACE-1'] = @$this->state['SAVEDPLACE'];
			$this->state['SAVEDPLACE'] = $query;
		}
	}
/**
 *	Resets the internal variable
 */
	function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
/**
 *	Get the saved place (usually one that could contain an edit button)
 *	@return string
 */
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
		header("Location: index.php?$params"); 
		//echo "<script language=\"javascript\">window.location='index.php?$params'</script>";  // old UNSAFE way!
		exit();	// stop the PHP execution
	}

// Set the page message (displayed on page construction)
	function setMsg( $msg, $msgNo=0, $append=false ) {
		$msg = $this->_( $msg );
		$this->msg = $append ? $this->msg.' '.$msg : $msg;
		$this->msgNo = $msgNo;
	}
// Display the message, format and display icon
	function getMsg( $reset=true ) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch( $this->msgNo ) {
		case UI_MSG_OK:
			$img = dPshowImage( dPfindImage( 'stock_ok-16.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_ALERT:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_WARNING:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "warning";
			break;
		case UI_MSG_ERROR:
			$img = dPshowImage( dPfindImage( 'stock_cancel-16.png' ), 16, 16, '' );
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
		return $msg ? '<table cellspacing="0" cellpadding="1" border="0"><tr>'
			. "<td>$img</td>"
			. "<td class=\"$class\">$msg</td>"
			. '</tr></table>'
			: '';
	}

	function setState( $label, $tab ) {
		$this->state[$label] = $tab;
	}

	function getState( $label ) {
		return array_key_exists( $label, $this->state) ? $this->state[$label] : NULL;
	}

	function login( $username, $password ) {
		$username = trim( db_escape( $username ) );
		$password = trim( db_escape( $password ) );

		$sql = "
		SELECT user_id, user_password AS pwd, password('$password') AS pwdpwd, md5('$password') AS pwdmd5
		FROM users, permissions
		WHERE user_username = '$username'
			AND users.user_id = permissions.permission_user
			AND permission_value <> 0
		";

		$row = null;
		if (!db_loadObject( $sql, $row )) {
			return false;
		}

		if (strcmp( $row->pwd, $row->pwdmd5 )) {
			if ($this->cfg['check_legacy_password']) {
			/* next check the legacy password */
				if (strcmp( $row->pwd, $row->pwdpwd )) {
					/* no match - failed login */
					return false;
				} else {
					/* valid legacy login - update the md5 password */
					$sql = "UPDATE users SET user_password=MD5('$password') WHERE user_id=$row->user_id";
					db_exec( $sql ) or die( "Password update failed." );
					$this->setMsg( 'Password updated', UI_MSG_ALERT );
				}
			} else {
				return false;
			}
		}

		$sql = "
		SELECT user_id, user_first_name, user_last_name, user_company, user_department, user_email, user_type
		FROM users
		WHERE user_id = $row->user_id AND user_username = '$username'
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
			$s .= $CR . '<td width="42">';
			$s .= dPshowImage( dPFindImage( $this->icon, $this->module ), '42', '42' );
			$s .= '</td>';
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
			//$s .= $CT . contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', $this->helpref );

			$s .= "\n\t<a href=\"#$this->helpref\" onClick=\"javascript:window.open('?m=help&dialog=1&hid=$this->helpref', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')\" title=\"".$AppUI->_( 'Help' )."\">";
			$s .= "\n\t\t" . dPshowImage( './images/icons/stock_help-16.png', '16', '16', $AppUI->_( 'Help' ) );
			$s .= "\n\t</a>";
			$s .= "\n</td>";
		}
		$s .= "\n</tr>";
		$s .= "\n</table>";

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
