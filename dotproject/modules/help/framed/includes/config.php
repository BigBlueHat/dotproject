<?php /* HELP $Id$ */
error_reporting( E_ALL );	// this only for development testing

##
## dothelp configuration file (integrated in dotproject)
##

require_once( "../../../includes/config.php" );

$dHconfig['root_dir'] = "{$dPconfig['root_dir']}/modules/help/framed/";

$dHconfig['dbtype'] = $dPconfig['dbtype'];
$dHconfig['dbhost'] = $dPconfig['dbhost'];
$dHconfig['dbname'] = $dPconfig['dbname'];
$dHconfig['dbuser'] = $dPconfig['dbuser'];
$dHconfig['dbpass'] = $dPconfig['dbpass'];

$dHconfig['dbprefix'] = "dhlp_";
?>