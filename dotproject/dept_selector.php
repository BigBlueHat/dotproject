<html>
<head>
	<link rel="stylesheet" href="<?php echo TEMPLATE . "/styles/main.css" ?>" type="text/css">
<?php
$form = isset( $_GET['form'] ) ? $_GET['form'] : 'all';
$company_id = isset( $_GET['company_id'] ) ? $_GET['company_id'] : '0';
$dept_id = isset( $_GET['dept_id'] ) ? $_GET['dept_id'] : '0';

if (!$form || !$company_id) {
	echo '</head><body bgcolor="#ffffff" text="#ff0000" onload="this.focus();">Incorrect parameters passed';
} else { 
	require_once './includes/config.php';
	require_once './includes/db_connect.php';
	require_once './includes/main_functions.php';

	$sql = "SELECT dept_id,dept_name FROM departments WHERE dept_company=$company_id ORDER BY dept_name";
	$rc = mysql_query( $sql );
	echo mysql_error();
	$depts = array( 0=>'');
	while ($row = mysql_fetch_array( $rc, MYSQL_NUM )) {
		$depts[$row[0]] = $row[1];
	}
?>
	<script language="javascript">
	function setClose(){
		var o = window.opener.document.<?php echo $form;?>;
		var f = document.depts.dept_id;
		o.user_department.value = f.options[f.selectedIndex].value;
		o.dept_name.value = f.options[f.selectedIndex].text;
		window.close();
	}
	</script>
<title>Deptartment Selector</title>
</head>

<body bgcolor="#529c9c" text="#ffffff" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" onload="this.focus();document.depts.dept_id.focus();">

<table cellspacing="0" cellpadding="3" border="0">
<form name="depts">
<tr>
	<td colspan="2">
<?php
	if (count( $depts ) > 1) {
		echo 'SELECT DEPARTMENT:<br>';
		echo arraySelect( $depts, 'dept_id', ' size="8"', $dept_id );
?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" class="button" value="select" onclick="setClose()">
	</td>
	<td align="right">
		<input type="button" class="button" value="cancel" onclick="window.close()">
<?php 
	} else {
		echo "There are no deptarments for this company.";
	}
?>
	</td>
</tr>
</form>
</table>

<?php } ?>
</body>
</html>
