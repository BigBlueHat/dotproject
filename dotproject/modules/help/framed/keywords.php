<html>
<head>
	<title>dothelp viewer search form</title>
	<link rel="stylesheet" href="help.css" type="text/css">
</head>

<body bgcolor=#f0f0f0 topmargin=0px leftmargin=0px rightmargin=0px marginwidth=0px vspace=0>

<?php
require_once( "./includes/config.php" );
require_once( "$root_dir/includes/db_connect.php" );
require_once( "$root_dir/classes/ui.class.php" );

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

$AppUI = new CAppUI;

$AppUI->setProject( $project_id );

if (isset($_GET['entry_lang'])) {
	$AppUI->user_locale = $_GET['entry_lang'];
}

$m = 'viewer';
@include_once( "$root_dir/locales/core.php" );

db_connect( $AppUI->project_dbhost, $AppUI->project_dbname, $AppUI->project_dbuser, $AppUI->project_dbpass );

$esql = "
SELECT {$AppUI->project_dbprefix}entries.*, page_keywords
FROM {$AppUI->project_dbprefix}entries
LEFT JOIN {$AppUI->project_dbprefix}xpages on page_entry = entry_id and page_lang = '$AppUI->user_locale'
";

$keywords = array();
if(!($erc = db_exec( $esql ))) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
##echo "<pre>$esql</pre>".mysql_error();##

while ($row = db_fetch_assoc( $erc )) {
	if ($row['page_keywords']) {
		$words = explode( "\n", $row['page_keywords'] );
		foreach ($words as $v) {
			$keywords[$v] = array( $row['entry_id'], $row['entry_type'] );
		}

	}
}
ksort( $keywords );
reset( $keywords );
##print_r($keywords);##

?>

<table cellSpacing=1 cellpadding=1 border=0>

<?php
while (list( $k, $v ) = each( $keywords )) {
	$buf = '<a target=MainFrame href="./main.php?entry_lang=' .$AppUI->user_locale .'&entry_type=' .$v[1] .'&entry_id=' .$v[0].'">' .$k.'</a>';
	
	echo "<tr bgcolor=#f0f0f0><td>$buf</td></tr>";
}
?>
</table>
</body>
</html>
