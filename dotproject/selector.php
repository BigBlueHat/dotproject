<?php
$debug = false;
$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$table = isset( $_GET['table'] ) ? $_GET['table'] : 0;

$ok = $callback & $table;

$title = "Generic Selector";
$select = '';
$from = $table;
$where = '';
$order = '';

switch ($table) {
case 'companies':
	$title = 'Company';
	$select = 'company_id,company_name';
	$order = 'company_name';
	break;
case 'departments':
	$title = 'Department';
	$company_id = isset( $_GET['company_id'] ) ? $_GET['company_id'] : '0';
	//$ok &= $company_id;  // Is it safe to delete this line ??? [kobudo 13 Feb 2003]

	$select = 'dept_id,dept_name';
	$where = $company_id ? "dept_company = $company_id" : '';
	$order = 'dept_name';
	break;
case 'forums':
	$title = 'Forum';
	$select = 'forum_id,forum_name';
	$order = 'forum_name';
	break;
case 'projects':
	$title = 'Project';
	$select = 'project_id,project_name';
	$order = 'project_name';
	$where = $project_company ? "project_company = $project_company" : '';
	break;
case 'tasks':
	$title = 'Task';
	$select = 'task_id,task_name';
	$order = 'task_name';
	$where = $task_project ? "task_project = $task_project" : '';
	break;
default:
	$ok = false;
	break;
}

if (!$ok) {
	echo "</head>\n";
	echo "<body bgcolor=\"#ffffff\" text=\"#ff0000\" onload=\"this.focus();\">\n";
	echo "Incorrect parameters passed\n";
	if ($debug) {
		echo "<br />callback = $callback \n";
		echo "<br />table = $table \n";
		echo "<br />ok = $ok \n";
	}
} else {
	require_once './includes/config.php';
	require_once( "./classdefs/ui.php" );
	
	session_name( 'dotproject' );
	session_start();
	session_register( 'AppUI' );	
	$AppUI =& $_SESSION['AppUI'];
	
	require_once './includes/db_connect.php';

	require_once './includes/main_functions.php';

	$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
	@include_once( "{$AppUI->cfg['root_dir']}/locales/core.php" );

	$sql = "SELECT $select FROM $table";
	$sql .= $where ? " WHERE $where" : ''; 
	$sql .= $order ? " ORDER BY $order" : '';
	$list = arrayMerge( array( 0=>''), db_loadHashList( $sql ) );
	echo db_error();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link rel="stylesheet" href="./style/<?php echo $uistyle;?>/main.css" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<script language="javascript">
	function setClose(){
		var list = document.frmSelector.list;
		var key = list.options[list.selectedIndex].value;
		var val = list.options[list.selectedIndex].text;
		window.opener.<?php echo $callback;?>(key,val);
		window.close();
	}
	</script>
	<title><?php echo $title;?>Selector</title>
</head>

<body bgcolor="#529c9c" text="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" onload="this.focus();document.frmSelector.list.focus();">

<table cellspacing="0" cellpadding="3" border="0">
<form name="frmSelector">
<tr>
	<td colspan="2">
<?php
	if (count( $list ) > 1) {
		echo $AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':<br />';
		echo arraySelect( $list, 'list', ' size="8"', 0 );
?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
<?php 
	} else {
		echo $AppUI->_( "no$table" );
	}
?>
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'Select', UI_CASE_LOWER );?>" onclick="setClose()" />
	</td>
</tr>
</form>
</table>

<?php } ?>
</body>
</html>
