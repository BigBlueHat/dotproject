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
// state parameters
	var $project_id=0;
	var $project_name='';

	var $project_dbhost='';
	var $project_dbname='';
	var $project_dbuser='';
	var $project_dbpass='';
	var $project_dbprefix='';
// localisation
	var $user_locale;
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english
// supported languages
	var $locales = array(
		'de' => 'German',
		'en' => 'English',
		'es' => 'Spanish',
		'fr' => 'French',
		'pt_br' => 'Portugese-Brazilian'
	);
	var $locale_warn = true;	// warn when a translation is not found

// message handling
	var $msg;
	var $msgNo;
	var $defaultRedirect;

	var $cfg=null;

// CAppUI Constructor
	function CAppUI() {
		GLOBAL $debug, $page_title, $dbhost, $dbname, $dbuser, $dbpass, $dbprefix;
		$this->state = array();
		$this->user_locale = $this->base_locale;
		$this->defaultRedirect = "";
	}

	function setConfig( &$cfg ) {
		$this->cfg = $cfg;
	// project initially inherits system defaults
		$this->project_dbhost = $this->cfg['dbhost'];
		$this->project_dbname = $this->cfg['dbname'];
		$this->project_dbuser = $this->cfg['dbuser'];
		$this->project_dbpass = $this->cfg['dbpass'];
		$this->project_dbprefix = $this->cfg['dbprefix'];
	}

	function setProject( $id ) {
		if (!$id) {
			return;
		}
		$dbconn = db_connect( $this->cfg['dbhost'], $this->cfg['dbname'], $this->cfg['dbuser'], $this->cfg['dbpass'] );

		$sql = "
		SELECT *
		FROM {$this->cfg['dbprefix']}projects 
		WHERE project_id = $id
		";
		//echo "<pre>$sql</pre>".db_error();
		if (!($res = db_exec( $sql, $dbconn ))) {
			$this->setMsg = db_error();
			return false;
		}
		if ($row = db_fetch_assoc( $res )) {
			if (!$row['project_dbhost']) {
				$row['project_dbhost'] = $this->cfg['dbhost'];
			}
			if (!$row['project_dbname']) {
				$row['project_dbname'] = $this->cfg['dbname'];
			}
			if (!$row['project_dbprefix']) {
				$row['project_dbprefix'] = $this->cfg['dbprefix'];
			}
			if (!$row['project_dbuser']) {
				$row['project_dbuser'] = $this->cfg['dbuser'];
			}
			if (!$row['project_dbpass']) {
				$row['project_dbpass'] = $this->cfg['dbpass'];
			}
			bindHashToObject( $row, $this );
			return true;
		} else {
			return false;
		}
	}

	function setDB( $type, $host, $name, $user, $pass ) {
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
		return array_key_exists( $label, $this->state) ? $this->state[$label] : 0;
	}
}
?>