<?php
##
##	Companies: View Projects sub-table
##
GLOBAL $company_id, $denyEdit;

$sql = "
SELECT departments.*, COUNT(user_department) dept_users
FROM departments
LEFT JOIN users ON user_department = dept_id
WHERE dept_company = $company_id
GROUP BY dept_id
ORDER BY dept_parent
";
##echo $sql;
$rc = mysql_query($sql);
$nums = mysql_num_rows($rc);

//pull the departments into an temp array
$tarr = array();
for ($x=0;$x<$nums;$x++) {
	$tarr[$x] = mysql_fetch_array( $rc, MYSQL_ASSOC );
}

function showchild( &$a, $level=0 ) {
	global $done;
	$done[] = $a['task_id']; ?>
<tr>
	<td>
		<A href="./index.php?m=departments&a=addedit&dept_id=<?php echo $a["dept_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>
	</td>
	<td>
	<?php 
		for ($y=0; $y < $level; $y++) {
			if ($y+1 == $level) {
				echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
			} else {
				echo "<img src=./images/shim.gif width=16 height=12  border=0>";
			}
		}
	?>

		<a href="./index.php?m=departments&a=view&dept_id=<?php echo $a["dept_id"];?>"><?php echo $a["dept_name"];?></a>
	</td>
	<td align="center"><?php echo $a["dept_users"] ? $a["dept_users"] : '';?></td>
</tr>
<?php }

function findchild( &$tarr, $parent, $level=0 ){
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["dept_parent"] == $parent && $tarr[$x]["dept_parent"] != $tarr[$x]["dept_id"]){
			showchild( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["dept_id"], $level);
		}
	}
}


?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th width="100%">Name</th>
	<th>Users</th>
	<td nowrap rowspan="99" align="right" valign="top" style="background-color:#ffffff">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new department" onClick="javascript:window.location='./index.php?m=departments&a=addedit&company_id=<?php echo $company_id;?>';">
	<?php } ?>
	</td>
</tr>

<?php

$tnums = count($tarr);
for ($i=0; $i < $tnums; $i++) {
	$d = $tarr[$i];
	if ($d["dept_parent"] == 0) {
		showchild( $d );
		findchild( $tarr, $d["dept_id"] );
	}
}
?>
</table>
