<?php
//Config File

//db access information
$dbhost = "localhost";
$db 	= "dotproject";
$dbuser = "dotproject";
$dbpass = "yourpassword";


//localization file.  Needs to be fleshed out
$language_file = "english.php";


$root_dir = "/wwwroot/demo.dotmarketing.org";				//filesystem web root
$company_name = "Dotproject";		
$page_title ="dotproject";
$base_url = "http://www.dotproject.net";
$site_domain = "dotproject.net";

// Date format, can be one of:
// 0 = international or yyyy-mm-dd
// 1 = UK and Australia or dd/mm/yyyy
// 2 = US or mm/dd/yyyy
$date_format = 1;


//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";

/*
	WARNING!
	To harden the security, the secret.php file should be moved outside of the
	web servers tree and be referenced by it's full path.
*/
require "secret.php";
?>
