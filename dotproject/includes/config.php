<?
//Config File

//db access information
$dbhost = "localhost";
$db = "demodotproject";
$dbuser = "demodotproject";
$dbpass = "yourpassword";


//localization file.  Needs to be fleshed out
$language_file = "english.php";


$root_dir = "/wwwroot/demo.dotmarketing.org";				//filesystem web root
$company_name = "dotmarketing, Inc.";		
$page_title ="dotproject <dotmarketing inc>";


//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";

?>