<?php

##
## dothelp configuration file (integrated in dotproject)
##

require_once( "../../../includes/config.php" );

$dbprefix = "dhlp_";

{$AppUI->cfg['root_dir']} = "{$AppUI->cfg['root_dir']}/modules/help/framed/";
$dbname = $db;

?>