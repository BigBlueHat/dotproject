<?php /* INCLUDES $Id$ */
##
## Global General Purpose Functions
##

$CR = "\n";

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
#

function defVal($var, $def) {
	return isset($var) ? $var : $def;
}

#
# defVal version for arrays
#

function dPgetParam( &$arr, $name, $def ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

#
# add history entries for tracking changes
#

function addHistory( $description, $project_id = 0, $module_id = 0) {
	global $AppUI;
	/*
	 * TODO:
	 * 1) description should be something like:
	 * 		command(arg1, arg2...)
	 *  for example:
	 * 		new_forum('Forum Name', 'URL')
	 *
	 * This way, the history module will be able to display descriptions
	 * using locale definitions:
	 * 		"new_forum" -> "New forum '%s' was created" -> "Se ha creado un nuevo foro llamado '%s'"
	 *
	 * 2) project_id and module_id should be provided in order to filter history entries
	 *
	 */
	if(!$AppUI->cfg['log_changes']) return;
	$description = str_replace("'", "\'", $description);
	$psql =	"INSERT INTO history " .
			"( history_description, history_user, history_date ) " .
	  		" VALUES ( '$description', " . $AppUI->user_id . ", now() )";
	db_exec($psql);
	echo db_error();
}

##
## Looks up a value from the SYSVALS table
##
function dPgetSysVal( $title ) {
	$sql = "
	SELECT syskey_type, syskey_sep1, syskey_sep2, sysval_value
	FROM sysvals,syskeys
	WHERE sysval_title = '$title'
		AND syskey_id = sysval_key_id
	";
	db_loadHash( $sql, $row );
// type 0 = list
	$sep1 = $row['syskey_sep1'];	// item separator
	$sep2 = $row['syskey_sep2'];	// alias separator

	$temp = explode( $sep1, $row['sysval_value'] );
	$arr = array();
	foreach ($temp as $item) {
		$temp2 = explode( $sep2, $item );
		if (isset( $temp2[1] )) {
			$arr[$temp2[0]] = $temp2[1];
		} else {
			$arr[$temp2[0]] = $temp2[0];
		}
	}
	return $arr;
}

?>
