<html>
<head>
	<title>dothelp viewer table of contents</title>
	<link rel="stylesheet" href="help.css" type="text/css">
</head>

<body bgcolor="#f0f0f0" topmargin="0px" leftmargin="0px" rightmargin="0px" marginwidth="0px" vspace="0">

<?php
require_once( "./includes/config.php" );
require_once( "{$AppUI->cfg['root_dir']}/includes/db_connect.php" );
require_once( "{$AppUI->cfg['root_dir']}/classes/ui.class.php" );

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

$AppUI = new CAppUI;

$AppUI->setProject( $project_id );

if (isset($_GET['entry_lang'])) {
	$AppUI->user_locale = $_GET['entry_lang'];
}

$m = 'viewer';
@include_once( "{$AppUI->cfg['root_dir']}/locales/core.php" );
db_connect( $AppUI->project_dbhost, $AppUI->project_dbname, $AppUI->project_dbuser, $AppUI->project_dbpass );

$esql = "
SELECT {$AppUI->project_dbprefix}entries.*, page_title, label_title, class_title, method_name
FROM {$AppUI->project_dbprefix}entries
LEFT JOIN {$AppUI->project_dbprefix}xpages on page_entry = entry_id and page_lang = '$AppUI->user_locale'
LEFT JOIN {$AppUI->project_dbprefix}xlabels on label_entry = entry_id and label_lang = '$AppUI->user_locale'
LEFT JOIN {$AppUI->project_dbprefix}xclasses on class_entry = entry_id and class_lang = '$AppUI->user_locale'
LEFT JOIN {$AppUI->project_dbprefix}xmethods on method_entry = entry_id and method_lang = '$AppUI->user_locale'
";

if(!($erc = db_exec( $esql ))) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
##echo "<pre>$esql</pre>".db_error();##

$entries = array();
$n = db_num_rows( $erc );
while ($row = db_fetch_assoc( $erc )) {
	$title = $row['page_title'].'<i>'.$row['label_title'].'</i>'.$row['class_title'].$row['method_name'];
	$row['title'] = $title;
	$entries[$row['entry_id']] = $row;
}
##print_r($entries);##

?>
<table cellspacing="1" cellpadding="1" border="0">
<tr>
	<td>
<?php
$n = count( $entries );
$k = 0;
if ($n) {
	while (list( $k, $v ) = each( $entries )) {
		if ($v['entry_prev'] == 0) {
			break;
		}
	}

	for ($i=0; $i < $n; $i++) {
		$v = $entries[$k];
		$buf = '<td>';
		for ($i=0; $i < $v["entry_indent"]; $i++) {
			$buf .= '&nbsp;&nbsp;';
		}

		$buf .= ($v['entry_type'] == 'label' ? $v['title']
			: '<a target=MainFrame href="./main.php?project_id='.$project_id.'&entry_lang='.$AppUI->user_locale.'&entry_type='.$v['entry_type'].'&entry_id='.$v['entry_id'].'">'.$v['title'].'</a></td>');
		
		echo "<tr bgcolor=#f0f0f0>$buf</tr>";

		$k = $entries[$k]['entry_next'];
		if (!$k) {
			break;
		}
	}
}
?>
</table>
</body>
</html>
