<?php // $Id$

/*
* dotProject Installer
* @package dotProject
* @Copyright (c) 2004, The dotProject Development Team sf.net/projects/dotproject
* @ All rights reserved
* @ dotProject is Free Software, released under BSD License
* @subpackage Installer
* @ This Installer is released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ Major Parts are based on Code from Mambo Open Source www.mamboserver.com
* @version $Revision$
*/


#
# Define some default values
#

$defDbHost = "localhost";
$defDbName = "dotproject";
$defDbPort = "3306";
$defDbType = "mysql";
$defSqlFilePath = "../db/dotproject.sql" ;        // default Path to the File with SQL Database Structure for dotProject;



#
# function to return a default value if a variable is not set
#

function defVal($var, $def) {
	return (isset($var) && $var > null) ? $var : $def;
}

/**
* Utility function to return a value from a named array or a specified default
*/
function dPgetParam( &$arr, $name, $def=null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}


// Get and prepare dotProject Version Information for Output
require_once("../includes/version.php");
function dPgetVersion() {
        global $dp_version_major, $dp_version_minor, $dp_version_patch, $dp_version_prepatch;
        $patchdot = $dp_version_patch > null ? '.' : '';
        $dp_version = $dp_version_major.".".$dp_version_minor.$patchdot.$dp_version_patch."&nbsp;".$dp_version_prepatch;
        return $dp_version;
}

function changeMode($object, $value) {

	return @chmod($object, $value) ? true : false;
}

?>