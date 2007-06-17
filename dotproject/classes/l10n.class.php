<?php
/**
 * Class to handle all localisation issues.
 * 
 * Created on 30/05/2007
 */
class CLocalisation
{
	var $charset;
	
	/** base locale - always 'en' */
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english
	
	/** variable holding the translation array */
	var $translate = array();
	
	/** Current language */
	var $lang;
	
	/** Current user's locale */
	var $locale;
	
	/**
	 * Default constructor.
	 */
	function CLocalisation()
	{
		global $locale_char_set;
		
		if (isset($locale_char_set)) {
			$this->charset = $locale_char_set;
		} else {
			$this->charset = 'utf-8';
		}
		
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($this->charset);
		}
		
		// set up the default preferences
		$this->setUserLocale($this->base_locale);
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
	 * @param $str The string to translate
	 * @param $flags Option flags, can be case handling or'd with output formats and cases, see also UI case and output types.
	 * @see uitranslationcasetypes
	 * @see uioutputtypes
	 * @return Translated and formatted string
	 */
	function _($str, $flags = 0)
	{
		if (is_array($str)) {
			$translated = array();
			foreach ($str as $s) {
				$translated[] = $this->__($s, $flags);
			}
			
			return implode(' ', $translated);
		} else {
			return $this->__($str, $flags);
		}
	}
	
	/** @internal
	 * Called by CAppUI::_() to do the actual translation
 	 */
	function __( $str, $flags = 0)
	{
		$str = trim($str);
		if (empty( $str )) {
			return '';
		}

		$x = @$this->translate[$str];
		if (!$x)
			$x = @$this->translate[strtolower($str)];
		
		if ($x) {
			$str = $x;
		} else if (dPgetConfig('locale_warn')) {
			if ($this->base_locale != $this->locale ||
				($this->base_locale == $this->locale && !in_array($str, @$this->translate)) ) {
				$str .= dPgetConfig('locale_alert');
			}
		}
		switch ($flags & UI_CASE_MASK) {
			case UI_CASE_UPPER:
				$str = strtoupper( $str );
				break;
			case UI_CASE_LOWER:
				$str = strtolower( $str );
				break;
			case UI_CASE_UPPERFIRST:
				$str = ucwords( $str );
				break;
		}
		/* Altered to support multiple styles of output, to fix
		 * bugs where the same output style cannot be used succesfully
		 * for both javascript and HTML output.
		 * PLEASE NOTE: The default is currently UI_OUTPUT_HTML,
		 * which is different to the previous version (which was
		 * effectively UI_OUTPUT_RAW).  If this causes problems,
		 * and they are localised, then use UI_OUTPUT_RAW in the
		 * offending call. 
		 */
		switch ($flags & UI_OUTPUT_MASK) {
			case UI_OUTPUT_HTML:
				$str = htmlentities(stripslashes($str), ENT_COMPAT, $this->charset);
				break;
			case UI_OUTPUT_JS:
				$str = addslashes(stripslashes($str));
				break;
			case UI_OUTPUT_RAW: 
				$str = stripslashes($str);
				break;
		}
		return $str;
	}
	
	/** Load the known language codes for loaded locales
	 *
	 * @return Array of known language codes
	 */
	function loadLanguages()
	{
		global $AppUI;
		
		if ( isset($_SESSION['LANGUAGES'])) {
			$LANGUAGES =& $_SESSION['LANGUAGES'];
		} else {
			$LANGUAGES = array();
			$langs = $AppUI->readDirs('locales');
			foreach ($langs as $lang) {
				if (file_exists(DP_BASE_DIR."/locales/$lang/lang.php")) {
					include_once DP_BASE_DIR."/locales/$lang/lang.php";
				}
			}
			@$_SESSION['LANGUAGES'] =& $LANGUAGES;
		}
		
		return $LANGUAGES;
	}
	
 /**
	* Sets the user locale.
	*
	* Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
	* @param $loc Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
	* @param $set Defaults to true, set the current users locale to the one specified. If false just return the locale that would be used.
	* @return The locale, if $set is false. Otherwise return NULL
	*/
	function setUserLocale($loc = '', $set = true)
	{
		global $locale_char_set, $AppUI;

		$LANGUAGES = $this->loadLanguages();

		if (! $loc) {
			$loc = @$AppUI->user_prefs['LOCALE'] ? $AppUI->user_prefs['LOCALE'] : dPgetConfig('host_locale');
		}

		if (isset($LANGUAGES[$loc])) {
			$lang = $LANGUAGES[$loc];
		} else {
			// Need to try and find the language the user is using, find the first one
			// that has this as the language part
			if (strlen($loc) > 2) {
				list ($l, $c) = explode('_', $loc);
				$loc = $this->findLanguage($l, $c);
			} else {
				$loc = $this->findLanguage($loc);
			}
			$lang = $LANGUAGES[$loc];
		}
		list($base_locale, $english_string, $native_string, $default_language, $lcs) = $lang;
		if (! isset($lcs))
			$lcs = (isset($locale_char_set)) ? $locale_char_set : 'utf-8';

		if (version_compare(phpversion(), '4.3.0', 'ge'))
			$user_lang = array( $loc . '.' . $lcs, $default_language, $loc, $base_locale);
		else {
			if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
				$user_lang = $default_language;
			} else {
				$user_lang = $loc . '.' . $lcs;
			}
		}
		if ($set) {
			$this->locale = $base_locale;
			//TODO: Remove - deprecated.
			$AppUI->user_locale = $base_locale;
			$this->lang = $user_lang;
			$locale_char_set = $lcs;
			
			//setlocale(LC_TIME, $user_lang);
			setlocale(LC_ALL, $this->lang);
			// load module based locale settings
			@include_once(DP_BASE_DIR . '/locales/'.$this->locale.'/locales.php');
			$this->loadTranslation();
			@include_once(DP_BASE_DIR . '/locales/core.php');
		} else {
			return $user_lang;
		}
	}
	
	function loadTranslation($locale = null)
	{
		global $all_tabs, $m;
		
		$this->translate = array();
		if (!isset($locale))
			$locale = $this->locale;

		$translations = file(DP_BASE_DIR . '/locales/'.$locale.'/common.inc');
		
		// language files for specific locales and specific modules (for external modules) should be 
		// put in modules/[the-module]/locales/[the-locale]/[the-module].inc
		// this allows for module specific translations to be distributed with the module
		if (file_exists(DP_BASE_DIR . "/modules/$m/locales/$locale.inc")) {
			$translations = array_merge($translations, file(DP_BASE_DIR . "/modules/$m/locales/$locale.inc"));
		} elseif (file_exists(DP_BASE_DIR . "/locales/$locale/$m.inc")) {
			$translations = array_merge($translations, file(DP_BASE_DIR . "/locales/$locale/$m.inc"));
		}
	
		//$all_tabs =& $_SESSION['all_tabs'][$m];
		if ($all_tabs)
		{
		foreach($all_tabs as $key => $tab)
		{
			if (is_int($key))
				$extra_modules[$tab['module']] = true;
			else 
				foreach($tab as $child_tab)
					$extra_modules[$child_tab['module']] = true;
		}
		
		foreach($extra_modules as $extra_module => $k)
		{
			if (file_exists(DP_BASE_DIR . "/modules/$extra_module/locales/$locale.inc")) {
	    	$translations = array_merge($translations, file(DP_BASE_DIR . "/modules/$extra_module/locales/$locale.inc"));
			} elseif (file_exists(DP_BASE_DIR . "/locales/$locale/$extra_module.inc")) {
				$translations = array_merge($translations, file(DP_BASE_DIR . "/locales/$locale/$extra_module.inc"));
			}
		}
		}
	
		// Handle exceptions to the general rule.
		switch ($m) {
		case 'departments':
			$translations = array_merge($translations, file(DP_BASE_DIR . "/locales/$locale/companies.inc" ));
			break;
		case 'system':
			$translations = array_merge($translations, file(DP_BASE_DIR . '/locales/'.dPgetConfig('host_locale') . '/styles.inc'));
			break;
		}
		
		foreach ($translations as $line) {
			if (substr($line, 0, 1) == '#')
				continue;
			$line = substr($line, 0, -2);
			list($word, $translation) = explode('=>', $line);
			$word = trim($word);
			if ($translation) {
				$this->translate[substr($word, 1, -1)] = substr(trim($translation), 1, -1);
			}	else {
				$this->translate[substr($word, 1, -1)] = substr($word, 1, -1);
			}
		}
	}

	/** 
	 * Find a valid language
	 * 
	 * @param $language The desired language
	 * @param $country Defaults to false. The desired country code
	 * @return First valid language matching the desired language code or country code
	 */
	function findLanguage($language, $country = false)
	{
		$LANGUAGES = $this->loadLanguages();
		$language = strtolower($language);
		if ($country) {
			$country = strtoupper($country);
			// Try constructing the code again
			$code = $language . '_' . $country;
			if (isset($LANGUAGES[$code]))
				return $code;
		}

		// Just use the country code and try and find it in the
		// languages list.
		$first_entry = null;
		foreach ($LANGUAGES as $lang => $info) {
			list($l, $c) = explode('_', $lang);
			if ($l == $language) {
				if (! $first_entry)
					$first_entry = $lang;
				if ($country && $c == $country)
					return $lang;
			}
		}
		return $first_entry;
	}
	
	/**
	 * Returns a substring of the original string, 
	 * limited by begin and end markers.
	 * Unicode safe method.
	 * 
	 * @param string $string The string to be manipulated.
	 * @param int $start where to start from
	 * @param int $end the length of the substring to return.
	 * 
	 * @return the desired substring.
	 */
	function substr($string, $start, $end = 0)
	{
		if (strtolower(substr($this->charset, 0, 3)) !== 'utf')
			return substr($string, $start, $end);
		
		if (function_exists('mb_substr')) {
			return mb_substr($string, $start, $end, $this->charset);
		} elseif (function_exists('iconv_substr')) {
			return iconv_substr($string, $start, $end, $this->charset);
		} else {
			if ($end < 0)
				preg_match('/^.{'.$start.'}(.*).{'.$end.'}$/u', $string, $matches);
			else
				preg_match('/^.{'.$start.'}(.{'.$end.'}).*$/u', $string, $matches);
				
			return $matches[1];
		}
	}
	
	/**
	 * Returns the length (in characters, not bytes) of a string.
	 * 
	 * @param string $string The string to be investigated
	 * 
	 * @return int length of characters of the string 
	 */
	function strlen($string)
	{
		if (strtolower(substr($this->charset, 0, 3)) !== 'utf')
			return strlen($string);
		
		if (function_exists('mb_strlen')) {
			return mb_strlen($string);
		} elseif (function_exists('iconv_strlen', $this->charset)) {
			return iconv_strlen($string);
		} else {
			strlen(utf8_decode($string));
		}
	}
	
	function truncate($string, $length, $padding = '')
	{
		if ($this->strlen($string) > $length) {
			return $this->substr($string, 0, $length - $this->strlen($padding)) . $padding;
		} else {
			return $string;
		}
	}
}
?>