<?php
/**
 * Class to handle all localisation issues.
 * 
 * Created on 30/05/2007
 */
class CLocalisation
{
	var $charset;
	
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
		global $AppUI;
		
		//TODO: Initialise $charset during construction!!!
		$charset = substr($AppUI->user_lang[0], strpos($AppUI->user_lang[0], '.') + 1);
		if (strtolower(substr($charset, 0, 3)) !== 'utf')
			return substr($string, $start, $end);
		
		if (function_exists('mb_substr')) {
			return mb_substr($string, $start, $end, $charset);
		} elseif (function_exists('iconv_substr')) {
			return iconv_substr($string, $start, $end, $charset);
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
		global $AppUI;
		
		//TODO: Initialise $charset during construction!!!
		$charset = substr($AppUI->user_lang[0], strpos($AppUI->user_lang[0], '.') + 1);
		if (strtolower(substr($charset, 0, 3)) !== 'utf')
			return strlen($string);
		
		if (function_exists('mb_strlen')) {
			return mb_strlen($string);
		} elseif (function_exists('iconv_strlen', $charset)) {
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