<?php
$entry_lang = isset($_GET['entry_lang']) ? $_GET['entry_lang'] : '';
$entry_link = isset($_GET['entry_link']) ? $_GET['entry_link'] : '';
$entry_type = isset($_GET['entry_type']) ? $_GET['entry_type'] : '';
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

$query = "?project_id=$project_id";
$query .= $entry_lang ? "&entry_lang=$entry_lang" : "";
$query .= $entry_link ? "&entry_link=$entry_link" : "";
$query .= $entry_type ? "&entry_type=$entry_type" : "";
?>
<html>
<head>
	<title>dothelp</title>
</head>
<frameset rows="25,*,25" frameborder="0" framespacing="0" border="0" border="no">
	<frame SRC="top.php<?php echo "$query";?>" name="TitleFrame" frameborder="0" scrolling="no" noresize marginwidth="0" marginheight="0" framespacing="0">
	<frameset cols="200,*" frameborder="0" framespacing="0" border="0" border="no">
		<frame src="toc.php<?php echo $query;?>" name="TOCFrame" frameborder="0" scrolling="yes" noresize marginwidth="0" marginheight="0" framespacing="0">
		<frame src="main.php<?php echo $query;?>" name="MainFrame" frameborder="0" scrolling="yes" noresize marginwidth="0" marginheight="0" framespacing="0">
	</frameset>
	<frame src="footer.html" name="TitleFrame" frameborder="0" scrolling="no" noresize marginwidth="0" marginheight="0" framespacing="0">
</frameset>

<noframes>
<body>
</body>
</noframes>

</html>