<?php /* CLASSES $Id: ui.class.php,v 1.141 2007/10/11 12:44:40 cyberhorse Exp $ */

/** @defgroup uimessagetypes UI message type constants 
 * @brief Used with CAppUI::msg() method
 */
/*@{*/
/** @enum UI_MSG_OK Operation successful message */
/** @enum UI_MSG_ALERT Alert message */
/** @enum UI_MSG_WARNING Warning message */
/** @enum UI_MSG_ERROR Critical error message */
define( 'UI_MSG_OK', 1 ); 
define( 'UI_MSG_ALERT', 2 ); 
define( 'UI_MSG_WARNING', 3 ); 
define( 'UI_MSG_ERROR', 4 ); 
/*@}*/

/** @defgroup uitranslationcasetypes UI translation case types
 * @brief Used with the CAppUI::_() method
 */
/*@{*/
/** @enum UI_CASE_MASK Case bitmask */
/** @enum UI_CASE_UPPER String converted to upper case */
/** @enum UI_CASE_LOWER String converted to lower case */
/** @enum UI_CASE_UPPERFIRST String converted to uppercase first letters (CamelCase) */
define( "UI_CASE_MASK", 0x0F );
define( "UI_CASE_UPPER", 1 );
define( "UI_CASE_LOWER", 2 );
define( "UI_CASE_UPPERFIRST", 3 );
/*@}*/

/** @defgroup uioutputtypes UI output types
 * @brief Used with the CAppUI::_() method to format character output for the intended medium
 */
/*@{*/
/** @enum UI_OUTPUT_MASK Output bitmask */
/** @enum UI_OUTPUT_HTML Format for HTML output */
/** @enum UI_OUTPUT_JS Format for Javascript output */
/** @enum UI_OUTPUT_RAW Do not process output */
define ('UI_OUTPUT_MASK', 0xF0);
define ('UI_OUTPUT_HTML', 0);
define ('UI_OUTPUT_JS', 	0x10);
define ('UI_OUTPUT_RAW',	0x20);
/*@}*/

/**
 * The Application User Interface Class.
 *
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 * @version $Revision: 1.141 $
 */
class DP_AppUI {
	/** 
	 * Instance of DP_AppUI
	 * @var DP_AppUI
	 */
	protected static $_instance = null;
	/** generic array for holding the state of anything */
	protected $state=null;
	/** current user's ID */
	public $user_id=null;
	/** current user's first name */
	public $user_first_name=null;
	/** current user's last name */
	public $user_last_name=null;
	/** current user's company */
	public $user_company=null;
	/** current user's department */
	public $user_department=null;
	/** current user's email */
	public $user_email=null;
	/** current user's type */
	public $user_type=null;
	/** current user's username */
	public $user_username=null;
	/** current user's preferences */
	public $user_prefs=null;
	/** Unix time stamp */
	public $day_selected=null;

	/** message string stored from CAppUI::setMsg() */
	public $msg = '';
	/** message number */
	public $msgNo = '';
	/** Default page for a redirect call*/
	public $defaultRedirect = '';

	/** Configuration variables as array */
	public $cfg = null;

	/** Version major */
	public $version_major = null;

	/** Version minor */
	public $version_minor = null;

	/** Version patch level */
	public $version_patch = null;

	/** Version string */
	public $version_string = null;

	/** integer for register log ID */
	public static $last_insert_id = null;	
	
	protected $db = null;
	protected $config = null;
	
	protected $uistyle = null;
	protected $iconstyle = null;

	/**
	* DP_AppUI Constructor
	*/
	private function __construct()
	{
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
		$this->user_type = 0;
		$this->user_username = '';

		// cfg['locale_warn'] is the only cfgVariable stored in session data (for security reasons)
		// this guarants the functionality of this->setWarning
		// $this->cfg['locale_warn'] = $this->getConfig('locale_warn');
		
		$this->project_id = 0;

		$this->defaultRedirect = "";

		$this->user_prefs = array();
	}

	/**
	 *  Instantiate ourselves only once as a singleton and return a reference
	 * @return DP_AppUI
	 */
	public static function getInstance()
	{
		if (isset($_SESSION['AppUI'])) {
			$GLOBALS['AppUI'] =& $_SESSION['AppUI'];
		} else {
			$GLOBALS['AppUI'] = new self();
			$_SESSION['AppUI'] =& $GLOBALS['AppUI'];
		}
		return $GLOBALS['AppUI'];
	}

	public function __destruct()
	{
		unset($GLOBALS['AppUI']);
	}


	/**
	 * Initialise application state. General RUN logic.
	 */
	function init()
	{
		// Instantiate the database object.
		$this->updateLastAction();
		
		// Load default preferences if not logged in
		if ($this->doLogin()) {
			$this->loadPrefs(0);
		} else {
			// load the user details.
			$this->loadUser($this->user_id, $this->user_username);
		}
		
		// Initialise localisation with user specific settings.
		DP_Localisation::getInstance()->setUserLocale();

		// Set the default ui style
		$this->uistyle = $this->getPref( 'UISTYLE' ) ? $this->getPref( 'UISTYLE' ) : $this->getConfig('host_style');
		$this->iconstyle = $this->getPref( 'ICONSTYLE' ) ? $this->getPref( 'ICONSTYLE' ) : 'default';
		
		/* Comment out until we figure out the best place for this.
		// write the HTML headers
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');	// Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');	// always modified
		header('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0');	 // HTTP/1.1
		header('Pragma: no-cache');	// HTTP/1.0
		*/
	}
	
	/**
	 * Config function, basically calls the static function from the DP_Config object
	 */
	public function getConfig($param = null, $default = null) 
	{
		return DP_Config::getConfig($param, $default);
	}

	/**
	 * Load page when user requests lost password.
	 */
	function initTypeLostpass()
	{
		global $uistyle;
		
		$uistyle = $this->getConfig('host_style');
		$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
		
		if (dPgetParam( $_REQUEST, 'sendpass', 0 )) {
			require(DP_BASE_DIR . '/includes/sendpass.php');
			sendNewPass();
		} else {
			$_GET['dialog'] = 1; // FIXME: Is that doing anything?
			$tpl->assign('redirect', $redirect);
			$tpl->displayHeader();
			$tpl->displayFile('lostpass', '.');
			$tpl->displayFile('footer', '.');
		}
		exit();
	}
	
	/**
	 * Handle login logic. On success, redirect to welcome page.
	 */
	function initTypeLogin()
	{
		if ($this->getConfig('auth_method') == 'http_ba') {
			$username = $_SERVER['REMOTE_USER'];
		} else {
			$username = dPgetParam( $_POST, 'username', '' );
		}
	
		$password = dPgetParam( $_POST, 'password', '' );
		$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
	
		$ok = $this->login( $username, $password );
		if (!$ok) {
			$this->setMsg( 'Login Failed');
			session_unset();
		} else {
			//Register login in user_acces_log
			$this->registerLogin();
		}
		$details['name'] = $this->user_first_name . ' ' . $this->user_last_name;
		addHistory('login', $this->user_id, 'login', $details);
	
		$this->redirect( $redirect );
	}
	
	/**
	 * Load page when user not logged in - login page. Usually, starting page of a session.
	 */
	function initTypeLoggedout()
	{
		$tpl = DP_Template::getInstance();
		
		$redirect = $_SERVER['QUERY_STRING']?strip_tags($_SERVER['QUERY_STRING']):'';
		if (strpos( $redirect, 'logout' ) !== false)
			$redirect = '';
	
		$l10n = DP_Localisation::getInstance();
		if (isset( $l10n->charset ))
			header('Content-type: text/html;charset='.$l10n->charset);
	
		//  Display the login page unless the authentication method is HTTP Basic Auth
		if ($this->getConfig('auth_method') == 'http_ba' )
			$this->redirect( 'login=http_ba&redirect='.$redirect );
		else
		{
			$_GET['dialog'] = 1;
	
			$tpl->assign('phpversion', phpversion());
			$tpl->assign('mysql', function_exists('mysql_pconnect'));
			$tpl->assign('redirect', $redirect);
	
			$tpl->displayHeader();
			$tpl->displayFile('login', '.');
			$tpl->displayFile('footer', '.');
		}
		
		// destroy the current session and output login page
		session_unset();
		session_destroy();
		exit;
	}
	
	/**
	 * File streaming/download page. 
	 */
	function initTypeDownload()
	{
		$perms =& $this->acl();
		$canRead = $perms->checkModule('files', 'view');
		if (!$canRead) {
			$this->redirect('m=public&a=access_denied');
		}
		
		$file_id = dPgetParam($_GET, 'file_id', 0);
		
		if ($file_id) {
			// projects that are denied access
			require_once($this->getModuleClass('projects'));
			require_once($this->getModuleClass('files'));
			$project =& new CProject;
			$allowedProjects = $project->getAllowedRecords($this->user_id, 'project_id, project_name');
			$fileclass =& new CFile;
			$fileclass->load($file_id);
			$allowedFiles = $fileclass->getAllowedRecords($this->user_id, 'file_id, file_name');
			
			if (count($allowedFiles) && ! array_key_exists($file_id, $allowedFiles)) {
				$this->redirect('m=public&a=access_denied');
			}
			
			// TODO: check permissions and redirect before this
			$fileclass->streamFile($file_id);
		
		} else {
			$this->setMsg('fileIdError', UI_MSG_ERROR);
			$this->redirect();
		}
	}
	
	/**
	 * Handle display of all normal dotProject pages.
	 */
	function initTypePage()
	{
		// Global systemwide variables
		global $m, $a, $u, $tab, $tpl, $time, $AppUI, $all_tabs, $perms;
		// Global variables, used by some pages
		global $filters, $orderby, $orderdir, $df;
		// Permissions
		global $canAccess, $canRead, $canEdit, $canAuthor, $canDelete;
		
		// Don't output anything. Usefull for fileviewer.php, gantt.php, etc.
		$suppressHeaders = dPgetParam($_GET, 'suppressHeaders', false);
		$dialog = dPgetParam($_GET, 'dialog', false);
		$perms = & $this->acl();
		
		$l10n = DP_Localisation::getInstance();
		// TODO: canRead/Edit assignements should be moved into each file
		
		// check overall module permissions
		// these can be further modified by the included action files
		$canAccess 	= $perms->checkModule($m, 'access');
		$canRead 	= $perms->checkModule($m, 'view');
		$canEdit 	= $perms->checkModule($m, 'edit');
		$canAuthor 	= $perms->checkModule($m, 'add');
		$canDelete 	= $perms->checkModule($m, 'delete');
		if (!$canAccess) {
			$this->redirect('m=public&a=access_denied');
		}
		
		$all_tabs = & $this->initTabs($m);	
		
		// All settings set. Initialise template (set global variables)
		$tpl->init();
		
		
		$m_config = $this->getConfig($m);
		@include_once(DP_BASE_DIR.'/functions/'.$m.'_func.php');
		
		if (!$suppressHeaders) {
			// output the character set header
			if (isset($l10n->charset)) {
				header('Content-type: text/html;charset='.$l10n->charset);
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
		$modclass = $this->getModuleClass($m);
		if (file_exists($modclass)) {
			include_once($modclass);
		}
		if ($u && file_exists(DP_BASE_DIR."/modules/$m/$u/$u.class.php")) {
			include_once(DP_BASE_DIR."/modules/$m/$u/$u.class.php");
		}
		
		// do some db work if dosql is set
		if (isset($_REQUEST['dosql'])) {
			require(DP_BASE_DIR."/modules/$m/" . ($u ? "$u/" : "") . $this->checkFileName($_REQUEST['dosql']) . '.php');
		}
		
		// start output proper
		$tpl->loadOverrides();
		ob_start();
		if(!$suppressHeaders)
			$tpl->displayHeader();
		
		$setuptime = (array_sum(explode(' ',microtime())) - $time);
		$module_file = DP_BASE_DIR."/modules/$m/" . ($u ? "$u/" : "") . $a . '.php';
		if (file_exists($module_file)) {
			require $module_file;
		} else {
		// TODO: make this part of the public module? 
		// TODO: internationalise the string.
			$titleBlock = new CTitleBlock('Warning', 'log-error.gif');
			$titleBlock->show();
		
			echo $l10n->_('Missing file ('.$module_file.'). Possible Module "'.$m.'" missing!');
		}
		
		if (!$suppressHeaders && !$dialog) {
			// iframe for doing multithreaded work - handle additional requests.
			echo '<iframe name="thread" src="' . DP_BASE_URL . '/modules/index.html" width="0" height="0" frameborder="0"></iframe>';
			
			if ($this->getConfig('debug') > 0) {
				global $acltime, $dbtime, $dbqueries, $db, $memory_marker;

				$tpl->assign('dp_version', $this->getVersion());
				$tpl->assign('php_version', phpversion());
				$tpl->assign('sql_version', $db->ServerInfo());
			
				$tpl->assign('page_time', sprintf('%.3f', (array_sum(explode(' ',microtime())) - $time)));
				$tpl->assign('time_limit', ini_get('max_execution_time'));
				$tpl->assign('setup_time', sprintf('%.3f seconds.', $setuptime));
				$tpl->assign('acl_time', sprintf('%.3f seconds.', $acltime));
				$tpl->assign('db_time', sprintf('%.3f seconds.', $dbtime));
				$tpl->assign('db_queries', $dbqueries);
				
				if (function_exists('memory_get_usage')) {
					$tpl->assign('memory_usage', sprintf('%01.2f Mb', memory_get_usage() / pow(1024, 2)));
					$tpl->assign('memory_delta', sprintf('%01d Kb', (memory_get_usage() - $memory_marker) / 1024));
				}
				if (function_exists('memory_get_peak_usage')) {
					$tpl->assign('memory_delta_peak', sprintf('%01d Kb', (memory_get_peak_usage() - $memory_marker) / 1024));
				}
				$tpl->assign('memory_limit', str_replace('M', ' Mb', ini_get('memory_limit')));

				$tpl->displayFile('debug', '.');
			}
			
			$tpl->assign('msg', $AppUI->getMsg());
			$tpl->displayFile('footer', '.');
		}
		ob_end_flush();
	}
	
	function initTabs($m)
	{
		if (!isset($_SESSION['all_tabs'][$m])) {
			$perms =& $this->acl();
			// For some reason on some systems if you don't set this up
			// first you get recursive pointers to the all_tabs array, creating
			// phantom tabs.
			if (!isset($_SESSION['all_tabs']))
				$_SESSION['all_tabs'] = array();
		
			$_SESSION['all_tabs'][$m] = array();
			$all_tabs =& $_SESSION['all_tabs'][$m];
			foreach ($this->getActiveModules() as $dir => $module) {
				if (!$perms->checkModule($dir, 'access')) {
					continue;
				}
		
				$modules_tabs = $this->readFiles(DP_BASE_DIR."/modules/$dir/", '^' . $m . '_tab.*\.php');
				foreach ($modules_tabs as $mtab) {
					// Get the name as the subextension
					// cut the module_tab. and the .php parts of the filename 
					// (begining and end)
					$nameparts = explode('.', $mtab);
					$filename = substr($mtab, 0, -4);
					if (count($nameparts) > 3) {
						$file = $nameparts[1];
						if (!isset($all_tabs[$file]))
							$all_tabs[$file] = array();
		
						$arr =& $all_tabs[$file];
						$name = $nameparts[2];
					} else {
						$arr =& $all_tabs;
						$name = $nameparts[1];
					}
					$arr[] = array(
						'name' => ucfirst(str_replace('_', ' ', $name)),
						'file' => DP_BASE_DIR . '/modules/' . $dir . '/' . $filename,
						'module' => $dir);
		
					// Don't forget to unset $arr again! $arr is likely to be used in the sequel declaring
					// any temporary array. This may lead to strange bugs with disappearing tabs (cf. #1767).
					unset($arr); 
				}
			}
		} else {
			$all_tabs =& $_SESSION['all_tabs'][$m];
		}
		
		return $all_tabs;
	}
	
 /**
	* Get the dotProject version string.
	* @return String value indicating the current dotproject version
	*/
	function getVersion()
	{
		global $dp_version_major, $dp_version_minor, $dp_version_patch;
		
		if ( ! isset($this->version_major)) {
			include_once DP_BASE_CODE . '/version.php';
			$this->version_major = $dp_version_major;
			$this->version_minor = $dp_version_minor;
			$this->version_patch = $dp_version_patch;
			$this->version_string = $this->version_major . "." . $this->version_minor;
			if (isset($this->version_patch))
			  $this->version_string .= "." . $this->version_patch;
			if (isset($dp_version_prepatch))
			  $this->version_string .= "-" . $dp_version_prepatch;
		}
		return $this->version_string;
	}

	/** Checks that the current user preferred style is valid/exists.*/
	function checkStyle()
	{
		// check if default user's uistyle is installed
		$uistyle = $this->getPref("UISTYLE");

		if ($uistyle && !is_dir(DP_BASE_CODE."/style/$uistyle")) {
			// fall back to host_style if user style is not installed
			$this->setPref('UISTYLE', $this->getConfig('host_style'));
		}
		$this->uistyle = $this->getPref('UISTYLE');
	}

 /**
	* Utility function to read the 'directories' under 'path'
	*
	* This function is used to read the modules or locales installed on the file system.
	* @param $path The path to read.
	* @param $default add a default entry at the top (empty)
	* @return A named array of the directories (the key and value are identical).
	*/
	function readDirs( $path, $default = null)
	{
		$dirs = array();
		if ($default != null)
			$dirs[$default] = $default;

		$d = dir( DP_BASE_CODE."/$path" );
		if (empty($d)) {
			dprint(__FILE__, __LINE__, 1, 'no directory ' . DP_BASE_CODE."/$path");
			return $dirs;
		}
		$ignore = array('.', '_');
		while (false !== ($name = $d->read())) {
			if(is_dir( DP_BASE_CODE."/$path/$name" ) && !in_array($name[0], $ignore) && $name != 'CVS') {
				$dirs[$name] = $name;
			}
		}
		$d->close();
		return $dirs;
	}

 /**
	* Utility function to read the 'files' under 'path'
	* @param $path The path to read.
	* @param $filter A regular expression to filter by.
	* @return array A named array of the files (the key and value are identical).
	*/
	function readFiles( $path, $filter='.' )
	{
		$files = array();

		if (is_dir($path) && ($handle = opendir( $path )) ) {
			while (false !== ($file = readdir( $handle ))) {
				if ($file != "." && $file != ".." && preg_match( "/$filter/", $file )) { 
					$files[$file] = $file; 
				} 
			}
			closedir($handle); 
		}
		return $files;
	}

 /**
	* Utility function to check whether a file name is 'safe'
	*
	* Prevents from access to relative directories (eg ../../dealyfile.php);
	* @param $file The file name.
	* @return array A named array of the files (the key and value are identical).
	*/
	function checkFileName( $file )
	{
		global $AppUI;

		// define bad characters and their replacement
		$bad_chars = ";/\\";
		$bad_replace = "...."; // Needs the same number of chars as $bad_chars

		// check whether the filename contained bad characters
		if ( strpos( strtr( $file, $bad_chars, $bad_replace), '.') !== false ) {
			$AppUI->redirect( 'm=public&a=access_denied' );
		}
		else {
			return $file;
		}
	}

 /**
	* Utility function to make a file name 'safe'
	*
	* Strips out mallicious insertion of relative directories (eg ../../dealyfile.php);
	* @param $file The file name.
	* @return array A named array of the files (the key and value are identical).
	*/
	function makeFileNameSafe( $file )
	{
		$file = str_replace( '../', '', $file );
		$file = str_replace( '..\\', '', $file );
		return $file;
	}

	/** 
	 * Translate a string to the local language [same form as the gettext abbreviation]
	 *
	 * This is the order of precedence:
	 * <ul>
	 * <li>If the key exists in the lang array, return the value of the key
	 * <li>If no key exists and the base lang is the same as the local lang, just return the string
	 * <li>If this is not the base lang, then return string with a red star appended to show
	 * that a translation is required.
	 * </ul>
	 * 
	 * @deprecated 3.0 - 16/06/2007 in favour of CLocalisation::_()
	 * 
	 * @param $str The string to translate
	 * @param $flags Option flags, can be case handling or'd with output formats and cases, see also UI case and output types.
	 * @see uitranslationcasetypes
	 * @see uioutputtypes
	 * @return Translated and formatted string
	 */
	function _($str, $flags = 0)
	{
		return DP_Localisation::getInstance()->_($str, $flags);
	}

 /**
	* Set the display of warning for untranslated strings
	* @param $state Boolean, true by default
	*/
	function setWarning( $state=true )
	{
		$temp = @$this->cfg['locale_warn'];
		$this->cfg['locale_warn'] = $state;
		
		return $temp;
	}

 /**
	* Save the url query string
	*
	* Also saves one level of history.  This is useful for returning from a delete
	* operation where the record more not now exist.  Returning to a view page
	* would be a nonsense in this case.
	* @param $query If not set then the current url query string is used
	*/
	function savePlace( $query='' )
	{
		if (!$query) {
			$query = @$_SERVER['QUERY_STRING'];
		}
		if ($query != @$this->state['SAVEDPLACE']) {
			$this->state['SAVEDPLACE-1'] = @$this->state['SAVEDPLACE'];
			$this->state['SAVEDPLACE'] = $query;
		}
	}
 
 /**
	* Resets the internal saved place variable
	*/
	function resetPlace()
	{
		$this->state['SAVEDPLACE'] = '';
	}
 
 /**
	* Get the saved place (usually one that could contain an edit button)
	* @return Query string
	*/
	function getPlace()
	{
		return @$this->state['SAVEDPLACE'];
	}
	
 /**
	* Redirects the browser to a new page.
	*
	* Mostly used in conjunction with the savePlace method. It is generally used
	* to prevent nasties from doing a browser refresh after a db update.  The
	* method deliberately does not use javascript to effect the redirect.
	*
	* @param $params The URL query string to append to the URL
	* @param $hist A marker for a historic 'place, only -1 or an empty string is valid.
	*/
	function redirect( $params='', $hist='' )
	{
		$session_id = SID;

		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state["SAVEDPLACE$hist"]) ? $this->state["SAVEDPLACE$hist"] : $this->defaultRedirect;
		}
		// Fix to handle cookieless sessions
		if ($session_id != "") {
		  if (!$params)
		    $params = $session_id;
		  else
		    $params .= "&amp;" . $session_id;
		}
		ob_implicit_flush(); // Ensure any buffering is disabled.
		header( "Location: index.php?$params" );
		exit();	// stop the PHP execution
	}

 /**
	* Set the page message.
	*
	* The page message is displayed above the title block and then again
	* at the end of the page.
	*
	* IMPORTANT: Please note that append should not be used, since for some
	* languagues atomic-wise translation doesn't work. Append should be
	* deprecated.
	*
	* @param $msg The (untranslated) message
	* @param $msgNo The type of message, one of any UI message type
	* @param $append If true, $msg is appended to the current string otherwise
	* the existing message is overwritten with $msg.
	* @see uimessagetypes
	*/
	function setMsg( $msg, $msgNo=0, $append=false )
	{
		$msg = $this->_( $msg );
		$this->msg = $append ? $this->msg.' '.$msg : $msg;
		$this->msgNo = $msgNo;
	}
	
 /**
	* Display the formatted message and icon
	* @param $reset If true the current message state is cleared.
	*/
	function getMsg( $reset=true )
	{
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
	
 /**
	* Set the value of a temporary state variable.
	*
	* The state is only held for the duration of a session.  It is not stored in the database.
	* Also do not set the value if it is unset.
	* @param $label The label or key of the state variable
	* @param $value Value to assign to the label/key
	*/
	function setState( $label, $value = null)
	{
		if (isset($value))
			$this->state[$label] = $value;
	}
 
 /**
	* Get the value of a temporary state variable.
	* If a default value is supplied and no value is found, set the default.
	* @return The value of the state variable
	*/
	function getState( $label, $default_value = null )
	{
		if (array_key_exists( $label, $this->state)) {
			return $this->state[$label];
		} else if (isset($default_value)) {
			$this->setState($label, $default_value);
			return $default_value;
		} else  {
			return NULL;
		}
	}

	/** 
	 * Check for a value in the state variable and user preferences
	 *
	 * Get a desired value by specifying a state variable to check, a preference to check, and a default value to return
	 * If none of the others are available.
	 *
	 * @param $label Label of the variable to fetch
	 * @param $value Set the state variable to this value, set the result to this $value
	 * @param $prefname If defined, use this preference as the returned value
	 * @param $default_value If defined, use this preference as the returned value
	 * @return First available value from $value, $prefname and $default_value.
	 */
	function checkPrefState($label, $value, $prefname, $default_value = null)
	{
		// Check if we currently have it set
		if (isset($value)) {
			$result = $value;
			$this->state[$label] = $value;
		} else if (array_key_exists($label, $this->state)) {
			$result = $this->state[$label];
		} else if (($pref = $this->getPref($prefname)) !== null) {
			$this->state[$label] = $pref;
			$result = $pref;
		} else if (isset($default_value)) {
			$this->state[$label] = $default_value;
			$result = $default_value;
		} else {
			$result = null;
		}
		return $result;
	}
 
 /**
	* Login function
	*
	* A number of things are done in this method to prevent illegal entry:
	* <ul>
	* <li>The username and password are trimmed and escaped to prevent malicious
	*     SQL being executed
	* </ul>
	* The schema previously used the MySQL PASSWORD function for encryption.  This
	* Method has been deprecated in favour of PHP's MD5() function for database independance.
	* The check_legacy_password option is no longer valid
	*
	* Upon a successful username and password match, several fields from the user
	* table are loaded in this object for convenient reference.  The style, localces
	* and preferences are also loaded at this time.
	*
	* @param $username The user login name
	* @param $password The user password
	* @return boolean True if successful, false if not
	*/
	function login( $username, $password )
	{
		require_once 'DP/Auth.php';

		$auth = DP_Auth::getAuthenticator();

		if (!$auth->supported()) {
			die("The authentication method (".$auth->displayName().") is not supported by your server. Please contact
			your server administrator to correct this problem.");
		}
		$username = trim($username);
		$password = trim($password);

		if (!$auth->authenticate($username, $password)) {
			return false;
		}

		$user_id = $auth->user_id;
		$username = $auth->username; // Some authentication schemes may collect username in various ways.

		// Now that the password has been checked, see if they are allowed to
		// access the system
		if (! isset($GLOBALS['acl'])) {
		  $GLOBALS['acl'] =& new DP_Acl;
    	}
		if ( ! $GLOBALS['acl']->checkLogin($user_id)) {
		  dprint(__FILE__, __LINE__, 1, "Permission check failed");
		  //  Stop processing here if using HTTP Basic Auth or else enter a redirect loop.
			if ($this->getConfig('auth_method') == 'http_basic') {
				die($this->_('noAccount'));
			} 
 
		  return false;
		}

		return $this->loadUser($user_id, $username);
	}

	protected function loadUser($user_id, $username)
	{
		$q  = new DP_Query;
		$q->addTable('users');
		$q->addQuery('user_id, contact_first_name as user_first_name, contact_last_name as user_last_name, contact_company as user_company, contact_department as user_department, contact_email as user_email, user_type');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact');
		$q->addWhere("user_id = $user_id AND user_username = '$username'");
		if ( !$q->loadObject($this) ) {
			dprint(__FILE__, __LINE__, 1, "Failed to load user information");
			return false;
		}

		$this->user_username = $username;

// load the user preferences
		$this->loadPrefs( $this->user_id );
		DP_Localisation::getInstance()->setUserLocale();
		$this->checkStyle();
		return true;
	}

	/** Register the user's login event in the user_access_log table */
	function registerLogin()
	{
		$q  = new DP_Query;
		$q->addTable('user_access_log');
		$q->addInsert('user_id', "$this->user_id");
		$q->addInsert('date_time_in', 'now()', false, true);
		$q->addInsert('user_ip', $_SERVER['REMOTE_ADDR']);
		$q->exec();
		self::$last_insert_id = db_insert_id();
		$q->clear();
	}

	/** Register the user's last action in the user_access_log table */
	function updateLastAction()
	{
		if (self::$last_insert_id > 0) {
			DP_Config::getDb()->update('user_access_log', array('date_time_last_action' => date('Y-m-d H:i:s')), "user_access_log_id = " . self::$last_insert_id);
		}
	}

	/** @deprecated */
	function logout()
	{
	}
	
 /**
	* Checks whether there is any user logged in.
	*/
	function doLogin()
	{
		return ($this->user_id < 0) ? true : false;
	}
	
 /**
	* Gets the value of the specified user preference
	* @param $name Name of the preference
	* @return The value of the preference, or null if the preference does not exist.
	*/
	function getPref( $name )
	{
		$pref = @$this->user_prefs[$name];
		if ($pref == 'false')
				$pref = false;
	
		return $pref;
	}
	
 /**
	* Sets the value of a user preference specified by name
	* @param $name Name of the preference
	* @param $val The value of the preference
	*/
	function setPref( $name, $val )
	{
		$this->user_prefs[$name] = $val;
	}
	
 /**
	* Loads the stored user preferences from the database into the internal
	* preferences variable.
	* @param $uid User id number
	*/
	function loadPrefs( $uid=0 )
	{
		$db = DP_Config::getDb();
		$q = $db->select()
			->from('user_preferences', array('pref_name', 'pref_value'));
		if ($uid != 0) {
			$q->where('pref_user in (0, ?)', $uid);
		} else {
			$q->where('pref_user = 0');
		}
		$q->order('pref_user');
		$prefs = $db->fetchPairs($q);
		$db->closeConnection();
		$this->user_prefs = array_merge( $this->user_prefs, $prefs );
	}

// --- Module connectors

 /**
	* Get a list of the installed modules
	* @return array Named array list in the form 'module directory'=>'module name'
	*/
	function getInstalledModules()
	{
		$q  = new DP_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addOrder('mod_directory');
		return ($q->loadHashList());
	}
 
 /**
	* Get a list of the active modules
	* @return array Named array list in the form 'module directory'=>'module name'
	*/
	function getActiveModules()
	{
		$q  = new DP_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addWhere('mod_active > 0');
		$q->addOrder('mod_directory');
		return ($q->loadHashList());
	}
 
 /**
	* Get a list of the modules that should appear in the menu
	* @return array Named array list in the form
	* ['module directory', 'module name', 'module_icon']
	*/
	function getMenuModules()
	{
		$q  = new DP_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name, mod_ui_icon');
		$q->addWhere('mod_active > 0 AND mod_ui_active > 0 AND mod_directory <> \'public\'');
		$q->addWhere('mod_type != \'utility\'');
		$q->addOrder('mod_ui_order');
		$activeModules = $q->loadList();
		$perms = & $this->acl();
		foreach ($activeModules as $mod) {
			if ($perms->checkModule($mod['mod_directory'], 'view')) {
				$viewableModules[] = $mod;
			}
		}

		return $viewableModules;
	}

	/** Check a module to see if it is active
	 * @param $module Name of the module to check
	 * @return result row containing the module information
	 */
	function isActiveModule($module)
	{
		$q = new DP_Query();
		$q->addTable('modules');
		$q->addQuery('mod_active');
		$q->addWhere("mod_directory = '$module'");
		return $q->loadResult();
	}

	/**
	 * Get a reference to the global dPacl class
	 * @return A reference to the dPacl object
	 */
	function &acl()
	{
		if (! isset($GLOBALS['acl'])){
			$GLOBALS['acl'] =& new DP_Acl;
	  	}
	  	return $GLOBALS['acl'];
	}

	/** 
	 * Get Javascript to be assigned to the current template
	 * 
	 * @return String containing javascript or <script> elements referencing external files
	 */
	function loadJS()
	{
	  global $m, $a, $extra_js;
	  // Search for the javascript files to load.
	  if (! isset($m))
	    return;
	  $root = DP_BASE_DIR;
	  if (substr($root, -1) != '/')
	    $root .= '/';

	  $base = DP_BASE_URL;
	  if ( substr($base, -1) != '/')
	    $base .= '/';
	  // Load the basic javascript used by all modules.
	  $jsdir = dir("{$root}js");

	  $js_files = array();
	  while (($entry = $jsdir->read()) !== false) {
	    if (substr($entry, -3) == '.js'){
		    $js_files[] = $entry;
	    }
	  }
	  asort($js_files);

		$js = $extra_js;
		while(list(,$js_file_name) = each($js_files)) {
			$js .= '<script type="text/javascript" src="'.$base.'js/'.$js_file_name.'"></script>'."\n";
		}
		$js .= $this->getModuleJS($m, $a, true);
		
		return $js;
	}

	/** 
	 * Get Javascript specific to the specified module
	 * 
	 * Loads specified module specific javascript. Also searches the js/ subdirectory for files to add
	 * @param $module Module name
	 * @param $file Default is null, load a specific .js file from the module directory
	 * @param $load_all Default is false, If true then load $module.module.js
	 * @return String containing javascript and <script> elements
	 */
	function getModuleJS($module, $file = null, $load_all = false)
	{
		$root = DP_BASE_DIR;
		if (substr($root, -1) != '/');
			$root .= '/';
		$base = DP_BASE_URL;
		if (substr($base, -1) != '/') 
			$base .= '/';
			
		$js = '';
		if ($load_all || ! $file) {
			if (file_exists("{$root}modules/$module/$module.module.js"))
				$js .= "<script type=\"text/javascript\" src=\"{$base}modules/$module/$module.module.js\"></script>\n";
		}
		if (isset($file) && file_exists("{$root}modules/$module/$file.js"))
			$js .= "<script type=\"text/javascript\" src=\"{$base}modules/$module/$file.js\"></script>\n";
		
		$js_subdir = "{$root}modules/$module/js";	
		
		if (is_dir($js_subdir)) {
			$module_jsdir = dir($js_subdir);
			$module_js_files = array();
			
			while (($entry = $module_jsdir->read()) !== false) {
				if (substr($entry, -3) == '.js'){
				    $module_js_files[] = $entry;
			    }
			}
			
			asort($module_js_files);

			while(list(,$js_file_name) = each($module_js_files)){
				  $js .= "<script type=\"text/javascript\" src=\"{$base}modules/$module/js/$js_file_name\"></script>\n";
			}			
		}
		
		return $js;
	}

	public function &getStyleClass($module = 'default')
	{
		$uistyle = $this->getPref("UISTYLE");
		$search = array(
			DP_BASE_CODE.'/modules/'.$module.'/views/style/'.$uistyle,
			DP_BASE_CODE.'/style/'.$uistyle
		);
		foreach ($search as $path) {
			if (is_file($path . '/style.php')) {
				include_once $path . '/style.php';
				$class = 'DP_AppUI_'.ucfirst($uistyle);
				if (class_exists($class)) {
					return new $class();
				}
			}
		}
		return new DP_AppUI_Abstract();
	}

	public static function &tabBoxFactory($module, &$view)
	{
		$style =& self::getInstance()->getStyleClass($module);
		return $style->tabBox($view);
	}

	public static function &titleBlockFactory($module, &$view)
	{
		$style =& self::getInstance()->getStyleClass($module);
		return $style->titleBlock($view);
	}

	public function findImage($name, $module=null)
	{
		$search_paths = array();
		if (!empty($this->_iconstyle)) {
			if (!empty($module)) {
				$search_paths[] = "/img/_iconsets/{$this->iconstyle}/{$module}/{$name}";
			}
			$search_paths[] = "/img/_iconsets/{$this->iconstyle}/_icons/{$name}";
			$search_paths[] = "/img/_iconsets/{$this->iconstyle}/_obj/{$name}";
			$search_paths[] = "/img/_iconsets/{$this->iconstyle}/{$name}";
		}
		if (!empty($module)) {
			$search_paths[] = "/img/{$this->uistyle}/{$module}/{$name}";
		}
		$search_paths[] = "/img/{$this->uistyle}/$name";
		$search_paths[] = "/img/{$this->uistyle}/icons/$name";
		$search_paths[] = "/img/{$this->uistyle}/obj/$name";
		if (!empty($module)) {
			$search_paths[] = "/img/_icons/$module/$name";
		}
		$search_paths[] = "/img/_icons/$name";

		foreach ($search_paths as $path) {
			error_log( "Searching " . DP_BASE_WWW.$path);
			if (file_exists(DP_BASE_WWW.$path)) {
				return $path;
			}
		}
		error_log('Could not find image ' . $name);
	}

}

// !! Ensure there is no white space after this close php tag.
?>
