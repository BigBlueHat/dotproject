<?php
require_once( "./includes/config.php" );
require_once( "{$AppUI->cfg['root_dir']}/classes/ui.class.php" );

$AppUI = new CAppUI;

if (isset($_GET['entry_lang'])) {
	$AppUI->user_locale = $_GET['entry_lang'];
}

$m = 'viewer';
@include_once( "{$AppUI->cfg['root_dir']}/locales/core.php" );
?>
<html>
<head>
	<title>dothelp top frame</title>
	<link rel="stylesheet" href="help.css" type="text/css">
</head>

<body bgcolor=#CCCCFF topmargin=0 leftmargin=0 marginwidth=0 marginheight=0 vspace=0>

<table class=banner cellPadding=2 cellSpacing=0 width="100%" height="100%">
<tr>
	<form>
	<td noWrap>
		<input type="button" name="toc" value="<?php echo $AppUI->_( 'Contents' );?>" class=tab onClick="top.TOCFrame.window.location='toc.php<?php echo $entry_lang ? "?entry_lang=$entry_lang" : '';?>'">
		<input type="button" name="toc" value="<?php echo $AppUI->_( 'Index' );?>" class=tab onClick="top.TOCFrame.window.location='keywords.php<?php echo $entry_lang ? "?entry_lang=$entry_lang" : '';?>'">
		<input type="button" name="toc" value="<?php echo $AppUI->_( 'Search' );?>" class=tab onClick="top.TOCFrame.window.location='search.php<?php echo $entry_lang ? "?entry_lang=$entry_lang" : '';?>'">
	</td>
	</form>
	<td noWrap align="center" width="100%"><?php echo $AppUI->_( 'page_title' );?></td>
	<td align=right height=20 width="20">
		<img src="./images/print_1.gif" width="16" height="16" border=0 alt="Print this page" onMouseDown="window.print()" vspace=0 hspace=5>
	</td>
</tr>
</table>

</body>
</html>
