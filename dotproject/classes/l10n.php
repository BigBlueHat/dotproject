<?php
/*
 * Created on 30/05/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CLocalisation
{
	function substr($string, $start, $end = 0)
	{
		global $AppUI;
		
		//TODO: Initialise $charset during construction!!!
		$charset = substr($AppUI->user_lang[0], strpos($AppUI->user_lang[0], '.') + 1);
		
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
}
?>