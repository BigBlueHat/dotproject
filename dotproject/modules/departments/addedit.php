<?php
// Add / Edit Company
$dept_id = isset($HTTP_GET_VARS['dept_id']) ? $HTTP_GET_VARS['dept_id'] : 0;
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m, $dept_id );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull data for this department
$sql = "
SELECT departments.*, company_name
FROM departments
LEFT JOIN companies ON company_id = dept_company
WHERE dept_id = $dept_id
";
$drow = array();
$rc = mysql_query($sql);
if (mysql_num_rows($rc) < 1) {
	$sql = "SELECT company_name FROM companies WHERE company_id = $company_id";
##echo "<p>$sql";##
	if (!($rc = mysql_query($sql))) {
		echo '<script language="javascript">
		window.location="./index.php?m=companies&message=You must have an active company to add a department.";
		</script>
		';
	}
	$row = mysql_fetch_row($rc);
	$company_name = $row[0];
} else {
	$drow = mysql_fetch_array( $rc, MYSQL_ASSOC );
##echo $sql.mysql_error();##
	$company_name = $drow['company_name'];
	$company_id = $drow['dept_company'];
}

// collect all the departments in the company
$depts = array(0 => '');
if ($company_id) {
	$sql = "SELECT dept_id,dept_name FROM departments WHERE dept_company = $company_id AND dept_id <> $dept_id";
	$rc = mysql_query($sql);
##echo $sql.mysql_error();##
	while ($row = mysql_fetch_array( $rc, MYSQL_NUM )) {
		$depts[$row[0]] = $row[1];
	}
}
?>

<SCRIPT language="javascript">
function submitIt() {
	var form = document.changeform;
	if (form.dept_name.value.length < 3) {
		alert( "Please enter a valid Company name" );
		form.dept_name.focus();
	} else {
		form.submit();
	}
}

function delIt() {
	if (confirm( "Are you sure you would like\nto delete this company?" )) {
		var form = document.changeform;
		form.del.value=1;
		form.submit();
	}
}
</script>

<TABLE width="90%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/users.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Company Department</span></td>
		<TD align="right" width="100%">&nbsp;</td>
	</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=companies">Companies List</a>
		<b>:</b> <a href="./index.php?m=companies&a=view&company_id=<?php echo $company_id;?>">View this Company</a>
		<b>:</b> <a href="./index.php?m=departments&a=view&dept_id=<?php echo $dept_id;?>">View this Department</a>
		</td>
		<TD width="50%" align="right">
			<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this comapny" border="0">delete department</a>
		</td>
	</TR>
</table>

<TABLE width="95%" border=0 bgcolor="#f4efe3" cellpadding="0" cellspacing=1>
<form name="changeform" action="?m=departments&a=dosql" method="post">
<input name="del" type="hidden" value="0">
<input type="hidden" name="dept_id" value="<?php echo $dept_id;?>">
<input type="hidden" name="dept_company" value="<?php echo $company_id;?>">

<TR height="20">
	<TD bgcolor="#878676" valign="top" colspan=2>
		<b><i><?php if($dept_id == 0){echo "Add";}else{echo "Edit";}?> Department for <?php echo $company_name;?> </i></b>
	</td>
</tr>
<tr>
	<TD align="right">Department Name:</td>
	<TD>
		<input type="text" class="text" name="dept_name" value="<?php echo @$drow["dept_name"];?>" size=50 maxlength="255"> <span class="smallNorm">(required)</span>
	</td>
</tr>
<tr>
	<TD align="right">Phone:</td>
	<TD>
		<input type="text" class="text" name="dept_phone" value="<?php echo @$drow["dept_phone1"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<TD align="right">Fax:</td>
	<TD>
		<input type="text" class="text" name="dept_fax" value="<?php echo @$drow["dept_fax"];?>" maxlength="30">
	</td>
</tr>

<?php
if (count( $depts )) {
?>
<tr>
	<TD align="right">Department Parent:</td>
	<TD>
<?php
	echo arraySelect( $depts, 'dept_parent', 'class=text size=1', @$drow["dept_parent"] );
?>
	</td>
</tr>
<?php } else {
	echo '<input type="hidden" name="dept_parent" value="0">';
} 
?>
<TR><TD align="right">Description:</td><td>&nbsp; </td></tr>
<TR><TD colspan=2 align="center">
<textarea cols="70" rows="10" class="textareaclass" name="dept_description">
<?php echo @$drow["dept_description"];?>
</textarea>
</td></tr>

<TR><TD><input type="button" value="back" class="button" onClick="javascript:history.back(-1);"></td><TD align="right"><input type="button" value="submit" class="button" onClick="submitIt()"></td></tr>
</form>
</TABLE>
&nbsp;<br>&nbsp;<br>&nbsp;

</body>
</html>
