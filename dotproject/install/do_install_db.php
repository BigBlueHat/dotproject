<?php // $Id$

if ($_POST['mode'] == 'install' && is_file( "../includes/config.php" ) )
	die("Security Check: dotProject seems to be already configured. Communication broken for Security Reasons!");

#
# function to output a message
# currently just outputs it expecting there to be a pre block.
# but could be changed to format it better - and only needs to be done here.
# The flush is called so that the user gets progress as it occurs. It depends
# upon the webserver/browser combination though.
#
function dPmsg($msg)
{
	echo $msg . "\n";
	flush();
}

#
# function to return a default value if a variable is not set
#

function InstallDefVal($var, $def) {
	return isset($var) ? $var : $def;
}

/**
* Utility function to return a value from a named array or a specified default
*/
function dPInstallGetParam( &$arr, $name, $def=null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

/**
* Utility function to get last updated dates/versions for the
* system.  The default is to 
*/
function InstallGetVersion($mode, $db) {
	$result = array(
		'last_db_update' => '',
		'last_code_update' => '',
		'code_version' => '1.0.2',
		'db_version' => '1'
	);
	if ($mode == 'upgrade') {
		$res = $db->Execute('SELECT * FROM dpversion LIMIT 1');
		if ($res && $res->RecordCount() > 0) {
			$row = $res->FetchRow();
			$result['last_db_update'] = str_replace('-', '', $row['last_db_update']);
			$result['last_code_update'] = str_replace('-', '', $row['last_code_update']);
			$result['code_version'] = $row['code_version'] ? $row['code_version'] : '1.0.2';
			$result['db_version'] = $row['db_version'] ? $row['db_version'] : '1';
		}
	}
	return $result;

}

/*
* Utility function to split given SQL-Code
* @param $sql string SQL-Code
* @param $last_update string last update that has been installed
*/
function InstallSplitSql($sql, $last_update) {
	global $lastDBUpdate;

	$buffer = array();
	$ret = array();

	$sql = trim($sql);

	if ($last_update && $last_update != '00000000') {
		// Find the first occurrance of an update that is
		// greater than the last_update number.
		dPmsg("Checking for previous updates");
		if (preg_match_all('/\n#\s*(\d{8})\b/', $sql, $matches)) {
			$len = count($matches[0]);
			for ($i = 0; $i < $len; $i++) {
				if ((int)$last_update < (int)$matches[1][$i]) {
					// Remove the SQL up to the point found
					$match = '/^.*' . trim($matches[0][$i]) . '/Us';
					$sql = preg_replace($match, "", $sql);
					break;
				}
			}
			// Set the upgrade date - it may be they have an old CVS
			// so we don't allow it to default to today.
			$lastDBUpdate = $matches[1][$len-1];
			// If we run out of indicators, we need to debunk, otherwise we will reinstall
			if ($i == $len)
				return $ret;
		}
	}
	$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

	$in_string = false;

	for($i=0; $i<strlen($sql)-1; $i++) {
		if($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}

		if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		}
		elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}
		if(isset($buffer[1])) {
			$buffer[0] = $buffer[1];
		}
		$buffer[1] = $sql[$i];
	}

	if(!empty($sql)) {
		$ret[] = $sql;
	}
	return($ret);
}
######################################################################################################################

$baseDir = dirname(dirname(__FILE__));
$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname(dirname($_SERVER['SCRIPT_NAME'])) : dirname(dirname(getenv('SCRIPT_NAME')));

$dbMsg = "";
$cFileMsg = "Not Created";
$dbErr = false;
$cFileErr = false;

$dbtype = trim( dPInstallGetParam( $_POST, 'dbtype', 'mysql' ) );
$dbhost = trim( dPInstallGetParam( $_POST, 'dbhost', '' ) );
$dbname = trim( dPInstallGetParam( $_POST, 'dbname', '' ) );
$dbuser = trim( dPInstallGetParam( $_POST, 'dbuser', '' ) );
$dbpass = trim( dPInstallGetParam( $_POST, 'dbpass', '' ) );
$dbdrop = dPInstallGetParam( $_POST, 'dbdrop', false );
$mode = dPInstallGetParam( $_POST, 'mode', 'upgrade' );
$dbpersist = dPInstallGetParam( $_POST, 'dbpersist', false );
$dobackup = isset($_POST['dobackup']);
$do_db = isset($_POST['do_db']);
$do_db_cfg = isset($_POST['do_db_cfg']);
$do_cfg = isset($_POST['do_cfg']);

$lastDBUpdate = '';

require_once( "$baseDir/lib/adodb/adodb.inc.php" );
@include_once "$baseDir/includes/version.php";

$db = NewADOConnection($dbtype);

if(!empty($db)) {
		$dbc = $db->Connect($dbost,$dbuser,$dbpass,$dbname);
} else { $dbc = false; }

$current_version = $dp_version_major . '.' . $dp_version_minor;
$current_version .= isset($dp_version_patch) ? ".$dp_version_patch" : '';
$current_version .= isset($dp_version_prepatch) ? "-$dp_version_prepatch" : '';

if ($dobackup){

	if( $dbc ) {
		require_once( "$baseDir/lib/adodb/adodb-xmlschema.inc.php" );

		$schema = new adoSchema( $db );

		$sql = $schema->ExtractSchema($content);

		header('Content-Disposition: attachment; filename="dPdbBackup'.date("Ymd").date("His").'.xml"');
		header('Content-Type: text/xml');
		echo $sql;
	} else {
		$msg = "ERROR: No Database Connection available!";
		header('Content-Disposition: attachment; filename="dPdbBackup'.date("Ymd").date("His").'.xml"');
		header('Content-Type: text/xml');
		echo $msg;
	}
}

?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Description" content="dotProject Installer">
 	<link rel="stylesheet" type="text/css" href="../style/default/main.css">
</head>
<body>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;dotProject Installer</h1>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="left">
<tr class='title'><td>Progress:</td></tr>
<tr><td><pre>
<?php

if ($dobackup)
	dPmsg("Backup completed");

if ($do_db || $do_db_cfg) {

	if ($mode == 'install') {
		if ($dbdrop) { 
			dPmsg("Dropping previous database");
			$db->Execute("DROP DATABASE IF EXISTS ".$dbname); 
		}

		dPmsg("Creating new Database");
		$db->Execute("CREATE DATABASE ".$dbname);
        	$dbError = $db->ErrorNo();
	
        	if ($dbError <> 0 && $dbError <> 1007) {
                	$dbErr = true;
              		$dbMsg .= "A Database Error occurred. Database has not been created! The provided database details are probably not correct.<br>".$db->ErrorMsg()."<br>";

        	}
	}

	$db->Execute("USE " . $dbname);

	$db_version = InstallGetVersion($mode, $db);

	$mqr = @get_magic_quotes_runtime();
	@set_magic_quotes_runtime(0);
	$sqlfile = null;
	if ($mode == 'upgrade') {
		dPmsg("Applying database updates");
		$last_version = $db_version['code_version'];
		// Convert the code version to a version string.
		$from_version = str_replace('.', '', $last_version);
		$from_version = str_replace('-', '', $from_version);
		$to_version = $dp_version_major . $dp_version_minor . $dp_version_patch . $dp_version_prepatch;
		if (file_exists('../db/upgrade_latest.sql')) {
			// CVS upgrade
			$sqlfile = "../db/upgrade_latest.sql";
		} else {
			// Check to see if the database has been upgraded first.
			// We don't want to double up on this.
			if ($from_version != $to_version) {
				// Look for the from and to version
				$upgrade_sql = "../db/upgrade_{$from_version}_to_{$to_version}.sql";
				if (file_exists($upgrade_sql)) {
					$sqlfile = $upgrade_sql;
				} else {
					die("There appears to be no upgrade path from $last_version to $current_version\nYou will need to manually upgrade");
				}
			}
		}
	} else {
		dPmsg("Installing database");
		$sqlfile = "../db/dotproject.sql";
	}

	$pieces = array();
	if ($sqlfile) {
		$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
		$pieces  = InstallSplitSql($query, $db_version['last_db_update']);
	}
	@set_magic_quotes_runtime($mqr);
	$errors = array();

	for ($i=0; $i<count($pieces); $i++) {
		$pieces[$i] = trim($pieces[$i]);
		if(!empty($pieces[$i]) && $pieces[$i] != "#") {
			if (!$result = $db->Execute($pieces[$i])) {
				//$errors[] = array ( $db->ErrorMsg(), $pieces[$i] );
				$dbErr = true;
				$dbMsg .= $db->ErrorMsg().'<br>';
			}
		}
	}

        if ($dbError <> 0 && $dbError <> 1007) {
		$dbErr = true;
                $dbMsg .= "A Database Error occurred. Database has probably not been populated completely!<br>".$db->ErrorMsg()."<br>";
        }
	if ($dbErr) {
		$dbMsg = "DB setup incomplete - the following errors occured:<br>".$dbMsg;
	} else {
		$dbMsg = "Database successfully setup<br>";
	}

	$code_updated = '';
	if ($mode == 'upgrade') {
		dPmsg("Applying data modifications");
		// Check for an upgrade script and run it if necessary.
		if (file_exists("$baseDir/db/upgrade_latest.php")) {
			include_once "$baseDir/db/upgrade_latest.php";
			$code_updated = dPupgrade($db_version['code_version'], $current_version, $db_version['last_code_update']);
		} else if (file_exists("$baseDir/db/upgrade_{$from_version}_to_{$to_version}.php")) {
			include_once "$baseDir/db/upgrade_{$from_version}_to_{$to_version}.php";
			$code_updated = dPupgrade($db_version['code_version'], $current_version, $db_version['last_code_update']);
		}
	}

	dPmsg("Updating version information");
	// No matter what occurs we should update the database version in the dpversion table.
	$sql = "UPDATE dpversion
	SET db_version = '$dp_version_major',
	last_db_update = '$lastDBUpdate',
	code_version = '$current_version',
	last_code_update = '$code_updated'
	WHERE 1";
	$db->Execute($sql);

} else {
$dbMsg = "Not Created";
}

// always create the config file content

	dPmsg("Creating config");
	$config = "<?php \n";
	$config .= "### Copyright (c) 2004, The dotProject Development Team dotproject.net and sf.net/projects/dotproject ###\n";
	$config .= "### All rights reserved. Released under BSD License. For further Information see ./includes/config-dist.php ###\n";
	$config .= "\n";
	$config .= "### CONFIGURATION FILE AUTOMATICALLY GENERATED BY THE DOTPROJECT INSTALLER ###\n";
	$config .= "### FOR INFORMATION ON MANUAL CONFIGURATION AND FOR DOCUMENTATION SEE ./includes/config-dist.php ###\n";
	$config .= "\n";
	$config .= "\$dPconfig['dbtype'] = \"$dbtype\";\n";
	$config .= "\$dPconfig['dbhost'] = \"$dbhost\";\n";
	$config .= "\$dPconfig['dbname'] = \"$dbname\";\n";
	$config .= "\$dPconfig['dbuser'] = \"$dbuser\";\n";
	$config .= "\$dPconfig['dbpass'] = \"$dbpass\";\n";
	$config .= "\$dPconfig['dbpersist'] = " . ($dbpersist ? 'true' : 'false') . ";\n";
	$config .= "\$dPconfig['root_dir'] = \$baseDir;\n";
	$config .= "\$dPconfig['base_url'] = \$baseUrl;\n";
	$config .= "?>";
	$config = trim($config);

if ($do_cfg || $do_db_cfg){
	if (is_writable("../includes/config.php") && ($fp = fopen("../includes/config.php", "w"))) {
		fputs( $fp, $config, strlen( $config ) );
		fclose( $fp );
		$cFileMsg = "Config file written successfully\n";
	} else {
		$cFileErr = true;
		$cFileMsg = "Config file could not be written\n";
	}
}

//echo $msg;
?>
</pre></td></tr>
</table><br/>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="left">
        <tr>
            <td class="title" valign="top">Database Installation Feedback:</td>
	    <td class="item"><b style="color:<?php echo $dbErr ? 'red' : 'green'; ?>"><?php echo $dbMsg; ?></b></td>
         <tr>
	 <tr>
            <td class="title">Config File Creation Feedback:</td>
	    <td class="item" align="left"><b style="color:<?php echo $cFileErr ? 'red' : 'green'; ?>"><?php echo $cFileMsg; ?></b></td>
	 </tr>
<?php if(!(($do_cfg || $do_db_cfg) && $cFileErr)){ ?>
	<tr>
	    <td class="item" align="left" colspan="2">The following Content should go to ./includes/config.php. Create that text file manually and copy the following lines in by hand.
		This file should be readable by the webserver.</td>
	 </tr>
         <tr>
            <td align="center" colspan="2"><textarea class="button" name="dbhost" cols="100" rows="20" title="Content of config.php for manual creation." /><?php echo $msg.$config; ?></textarea></td>
         </tr>
<?php } ?>
	<tr>
	    <td class="item" align="center" colspan="2"><br/><b><a href="<?php echo $baseUrl.'/index.php?m=system&a=systemconfig';?>">Login and Configure the dotProject System Environment</a></b></td>
	 </tr>
        </table>
</body>
</html>
