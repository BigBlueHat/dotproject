<?php
function smarty_modifier_dPhighlight($string, $key)
{
	global $locale_char_set;
	
	return eregi_replace('('.quotemeta($key).')', '<span style="background: yellow">\\1</span>', $string);
}

/* vim: set expandtab: */
?>