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

// collect all the users for the department owner list
$owners = array( '0'=>'');
$osql = "SELECT user_id,user_first_name,user_last_name FROM users";
$orc = mysql_query($osql);
while ($orow = mysql_fetch_row( $orc )) {
	$owners[$orow[0]] = "$orow[1] $orow[2]";
}

?>

<SCRIPT language="javascript">
function testURL( x ) {
	var test = "document.changeform.dept_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}

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

<table width="98%" border=0 cellpadding="0" cellspacing=1>
	<tr>
	<td><img src="./images/icons/users.gif" alt="" border="0"></td>
		<td nowrap><span class="title">Company Department</span></td>
		<td align="right" width="100%">&nbsp;</td>
	</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap>
	<a href="./index.php?m=companies">companies list</a>
	<b>:</b> <a href="./index.php?m=companies&a=view&company_id=<?php echo $company_id;?>">view this Company</a>
	<b>:</b> <a href="./index.php?m=departments&a=view&dept_id=<?php echo $dept_id;?>">view this Department</a>
	</td>
	<td width="50%" align="right">
		<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this comapny" border="0">delete department</a>
	</td>
</tr>
</table>

<table cellspacing="0" cellpadding="2" border="0" width="98%" class="std">
<form name="changeform" action="?m=departments&a=dosql" method="post">
<input name="del" type="hidden" value="0">
<input type="hidden" name="dept_id" value="<?php echo $dept_id;?>">
<input type="hidden" name="dept_company" value="<?php echo $company_id;?>">

<tr>
	<th colspan=2>
		<?php if($dept_id == 0){echo "Add";}else{echo "Edit";}?> Department for <?php echo $company_name;?>
	</th>
</tr>
<tr>
	<td align="right" nowrap>Department Name:</td>
	<td>
		<input type="text" class="text" name="dept_name" value="<?php echo @$drow["dept_name"];?>" size=50 maxlength="255"> <span class="smallNorm">(required)</span>
	</td>
</tr>
<tr>
	<td align="right" nowrap>Phone:</td>
	<td>
		<input type="text" class="text" name="dept_phone" value="<?php echo @$drow["dept_phone1"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<td align="right" nowrap>Fax:</td>
	<td>
		<input type="text" class="text" name="dept_fax" value="<?php echo @$drow["dept_fax"];?>" maxlength="30">
	</td>
</tr>
<tr>
	<td align="right">Address1:</td>
	<td><input type="text" class="text" name="dept_address1" value="<?php echo @$crow["dept_address1"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right">Address2:</td>
	<td><input type="text" class="text" name="dept_address2" value="<?php echo @$crow["dept_address2"];?>" size=50 maxlength="255"></td>
</tr>
<tr>
	<td align="right">City:</td>
	<td><input type="text" class="text" name="dept_city" value="<?php echo @$crow["dept_city"];?>" size=50 maxlength="50"></td>
</tr>
<tr>
	<td align="right">State:</td>
	<td><input type="text" class="text" name="dept_state" value="<?php echo @$crow["dept_state"];?>" maxlength="50"></td>
</tr>
<tr>
	<td align="right">Zip:</td>
	<td><input type="text" class="text" name="dept_zip" value="<?php echo @$crow["dept_zip"];?>" maxlength="15"></td>
</tr>
<tr>
	<td align="right">URL http://<A name="x"></a></td>
	<td>
		<input type="text" class="text" value="<?php echo @$crow["dept_url"];?>" name="dept_url" size=50 maxlength="255">
		<a href="#x" onClick="testURL('CompanyURLOne')">[test]</a>
	</td>
</tr>

<?php
if (count( $depts )) {
?>
<tr>
	<td align="right" nowrap>Department Parent:</td>
	<td>
<?php
	echo arraySelect( $depts, 'dept_parent', 'class=text size=1', @$drow["dept_parent"] );
?>
	</td>
</tr>
<?php } else {
	echo '<input type="hidden" name="dept_parent" value="0">';
} 
?>
<tr>
	<td align="right">Owner:</td>
	<td>
<?php
	echo arraySelect( $owners, 'dept_owner', 'size="1" class="text"', $crow["dept_owner"] );
?>
	</td>
</tr>
<tr><td align="right" nowrap>Description:</td><td>&nbsp; </td></tr>
<tr><td colspan=2 align="center">
<textarea cols="70" rows="10" class="textarea" name="dept_description">
<?php echo @$drow["dept_description"];?>
</textarea>
</td></tr>

<tr><td><input type="button" value="back" class="button" onClick="javascript:history.back(-1);"></td><td align="right"><input type="button" value="submit" class="button" onClick="submitIt()"></td></tr>
</form>
</table>
&nbsp;<br>&nbsp;<br>&nbsp;

</body>
</html>
