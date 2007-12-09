<?php
function smarty_modifier_dPescape($string)
{
	global $locale_char_set;
	
	return htmlentities($string, ENT_COMPAT, $locale_char_set);
}

/* vim: set expandtab: */
?>