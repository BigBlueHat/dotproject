<?php /* $Id$ */
##
## Global General Purpose Functions
##

##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor( $bg, $lt='#ffffff', $dk='#000000' ) {
// cross-over color = x
	$x = 128;
	$r = hexdec( substr( $bg, 0, 2 ) );
	$g = hexdec( substr( $bg, 2, 2 ) );
	$b = hexdec( substr( $bg, 4, 2 ) );

	if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
		return $lt;
	} else {
		return $dk;
	}
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelect( &$arr, $select_name, $select_attribs, $selected, $translate=false ) {
	GLOBAL $AppUI;
	reset( $arr );
	$s = "<select name=\"$select_name\" $select_attribs>";
	foreach ($arr as $k => $v ) {
		if ($translate) {
			$v = @$AppUI->_( $v );
		}
		$s .= '<option value="'.$k.'"'.($k == $selected ? ' selected' : '').'>' . $v . "</option>";
	}
	$s .= '</select>';
	return $s;
}

##
## Merges arrays maintaining/overwriting shared numeric indicees
##
function arrayMerge( $a1, $a2 ) {
	foreach ($a2 as $k => $v) {
		$a1[$k] = $v;
	}
	return $a1;
}

##
## breadCrumbs - show a colon separated list of bread crumbs
## array is in the form url => title
##
function breadCrumbs( &$arr ) {
	GLOBAL $AppUI;
	$crumbs = array();
	foreach ($arr as $k => $v) {
		$crumbs[] = "<a href=\"$k\">".$AppUI->_( $v )."</a>";
	}
	return implode( ' <strong>:</strong> ', $crumbs );
}
##
## generate link for context help
##
function contextHelp( $title, $link='' ) {
	$dothelpURL = "./modules/help/framed/";

	return "<a href=\"#$link\" onClick=\"javascript:window.open('$dothelpURL?entry_link=$link', 'contexthelp', 'width=700, height=400, left=20, top=20, resizable=yes')\">$title</a>";
}

##
## displays the configuration array of a module for informational purposes
##
function dPshowModuleConfig( $config ) {
	GLOBAL $AppUI;
	$s = '<table cellspacing="2" cellpadding="2" border="0" class="std" width="50%">';
	$s .= '<tr><th colspan="2">'.$AppUI->_( 'Module Configuration' ).'</th></tr>';
	foreach ($config as $k => $v) {
		$s .= '<tr><td width="50%">'.$AppUI->_( $k ).'</td><td width="50%" class="hilite">'.$AppUI->_( $v ).'</td></tr>';
	}
	$s .= '</table>';
	return ($s);
}
##
## gets the list of active modules for a navigation menu
##
function dPgetMenuModules() {
	$sql = "
	SELECT mod_directory, mod_ui_name, mod_ui_icon
	FROM modules
	WHERE mod_active > 0 AND mod_ui_active > 0
	ORDER BY mod_ui_order
	";
	return (db_loadList( $sql ));
}
##
## function to recussively find an image in a number of places
##
function dPfindImage( $name, $module ) {
// uistyle must be declared globally
	global $AppUI, $uistyle;

	if (file_exists( "{$AppUI->cfg['root_dir']}/style/$uistyle/images/$name" )) {
		return "./style/$uistyle/images/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/modules/$module/images/$name" )) {
		return "./modules/$module/images/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/icons/$name" )) {
		return "./images/icons/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/obj/$name" )) {
		return "./images/obj/$name";
	} else {
		return "./images/$name";
	}
}

#
# function to return a default value if a variable is not set
# (should we use something like this to clean up the code?)
#  	
function defVal(&$var, $def) {
	return isset($var) ? $var : $def;
}

#
# defVal version for arrays
#

function defValArr(&$arr, $name, $def) {
	return isset($arr[$name]) ? $arr[$name] : $def;	
}

?>
