<?php
##
## Main Functions
##

function ptranslate($word){
	global $language_file,$root_dir;
	require "./includes/" . $language_file;
	if (empty( $pt[$word] )) {
		return $word;
	} else {
		return $pt[$word];
	}
}

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
function arraySelect( &$arr, $select_name, $select_attribs, $selected ) {
	reset( $arr );
	$s = "<select name=\"$select_name\" $select_attribs>";
	while (list( $k, $v ) = each( $arr)) {
		$s .= '<option value="'.$k.'"'.($k == $selected ? ' selected' : '').'>'.$v;
	}
	$s .= '</select>';
	return $s;
}

//return Duration returns an array that
function returnDur( $x ){
	if ($x > 24) {
		$value= ($x / 24);
		$mulitpule = 24;
		if ($value > 1) {
			$type = "days";
		} else {
			$type = "day";
		}
	} else {
		$value = ($x);
		$mulitpule = 1;
		if($value > 1){
			$type = "hours";
		} else {
			$type = "hour";
		}
	}
	return array( "value" => $value, "mulitpule" => $mulitpule, "type" => $type );
}

// Take the date entered, parse it to the correct value
function toDate( $date ) {
	global $date_format;

	switch ($date_format) {
		case 1:
			list( $day, $mon, $yr ) = explode( '/', $date );
			return $yr . "-" . $mon . "-" . $day;
		case 2:
			list( $mon, $day, $yr ) = explode( '/', $date );
			return $yr . "-" . $mon . "-" . $day;
		default:
			return $date;
	}
}

function fromDate($date) {
	global $date_format;

	$parts = preg_split("/[-: ]+/", $date);

	switch ($date_format) {
		case 1:
			$retstring = $parts[2] . "/" . $parts[1] . "/" . $parts[0];
			break;
		case 2:
			$retstring = $parts[1] . "/" . $parts[2] . "/" . $parts[0];
			break;
		default:
			$retstring =  $parts[0] . "-" . $parts[1] . "-" . $parts[2];
			break;
	}
	if (count( $parts ) > 3)
		$retstring .= " " . $parts[3] . ":" . $parts[4] . ":" . $parts[5];
	return $retstring;
}

// Return the format required for date entry, for user information display
function dateFormat() {
	global $date_format;

	switch ($date_format) {
		case 1:
			return "dd/mm/yyyy";
			break;
		case 2:
			return "mm/dd/yyyy";
			break;
		default:
			return "yyyy-mm-dd";
			break;
	}
}

function JScalendarDate( $field ) {
	global $date_format;

	echo "dar = eval(\"document." . $field . ".\" + x + \".value.split('";
	if ($date_format > 0) {
		echo "/";
	} else {
		echo "-";
	}
	echo "')\");\n";
	echo "if(eval(\"document." . $field . ".\" + x + \".value.length\") > 9) {\n";
	echo "if(dar.length == 3)\n";
	echo "  {\n";
	switch ($date_format) {
		case 1:
			$dd = 0;
			$mm = 1;
			$yy = 2;
			break;
		case 2:
			$mm = 0;
			$dd = 1;
			$yy = 2;
			break;
		default:
			$yy = 0;
			$mm = 1;
			$dd = 2;
			break;
	}
	echo "  yy = parseInt(dar[" . $yy . "], 10);\n";
	echo "  mm = parseInt(dar[" . $mm . "], 10);\n";
	echo "  dd = parseInt(dar[" . $dd . "], 10);\n";
	echo "  }\n}\n";
}
?>
