<?php /* HELP $Id$ */ ?>
<html>
<head>
	<title>dothelp viewer search form</title>
	<link rel="stylesheet" href="help.css" type="text/css">
</head>

<body bgcolor=#f0f0f0 topmargin=0px leftmargin=0px rightmargin=0px marginwidth=0px vspace=0>

<?php
require_once( "./includes/config.php" );
require_once( "./includes/db_connect.php" );
require_once( "./classes/ui.class.php" );

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

$AppUI = new CAppUI;
$AppUI->setConfig( $dHconfig );

$AppUI->setProject( $project_id );

if (isset($_GET['entry_lang'])) {
	$AppUI->user_locale = $_GET['entry_lang'];
}

$m = 'viewer';
@include_once( "./locales/core.php" );

db_connect( $AppUI->project_dbhost, $AppUI->project_dbname, $AppUI->project_dbuser, $AppUI->project_dbpass );

$search_text = isset($_POST['search_text']) ? $_POST['search_text'] : '';

$esql = "
SELECT {$AppUI->project_dbprefix}entries.*, page_title
FROM {$AppUI->project_dbprefix}entries
LEFT JOIN {$AppUI->project_dbprefix}xpages on page_entry = entry_id and page_lang = '$AppUI->user_locale'
WHERE page_content LIKE '%$search_text%'
";

$entries = array();
$n = 0;
if ($search_text) {
	if(!($erc = db_exec( $esql ))) {
		echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
	}
##echo "<pre>$esql</pre>".db_error();##

	$n = db_num_rows( $erc );
	while ($row = db_fetch_assoc( $erc, MYSQL_ASSOC )) {
		$row['title'] = $row['page_title'];
		$entries[] = $row;
	}
##print_r($entries);##
}

?>

<table cellSpacing=1 cellpadding=1 border=0>
<form name="searchForm" method=POST action="<?php $_SERVER['PHP_SELF'];?>">
<tr>
	<td nowrap>Search Text:
		<br /><input type="text" name="search_text" class=text value="<?php echo $search_text;?>">
		<input type="submit" value="go" class=tab>
	</td>
</tr>
</form>

<?php
if ($n) {
	echo "<tr><td>$n items found</td></tr>";

	while (list( $k, $v ) = each( $entries )) {
		$buf = '<a target=MainFrame href="./main.php?entry_lang=' .$AppUI->user_locale .'&entry_type=' .$v['entry_type'] .'&entry_id=' .$v['entry_id'].'">' .$v['title'].'</a>';
		
		echo "<tr bgcolor=#f0f0f0><td>$buf</td></tr>";
	}
}
?>
</table>
</body>
</html>
