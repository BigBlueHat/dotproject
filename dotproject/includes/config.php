<?
//Config File

//db access information
$dbhost = "localhost";
$db = "demodotproject";
$dbuser = "demodotproject";
$dbpass = "yourpassword";

//localization file.  Needs to be fleshed out
$language_file = "english.php";

// Web site specific information
$root_dir = "/wwwroot/demo.dotmarketing.org";				//filesystem web root
$company_name = "dotmarketing, Inc.";		
$page_title ="dotproject <dotmarketing inc>";
$base_url = "http://www.dotmarketing.org/dotproject";

// Admin email used in From address of Task update notifications
$admin_email = "admin@yourdomain.com";

// Date format, can be one of:
// 0 = international or yyyy-mm-dd
// 1 = UK and Australia or dd/mm/yyyy
// 2 = US or mm/dd/yyyy
$date_format = 0;


//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";

?>
