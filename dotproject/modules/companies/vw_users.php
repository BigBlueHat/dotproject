<?php
##
##	Companies: View User sub-table
##
GLOBAL $company_id; 

$usql = "
select user_id, user_username, user_first_name, user_last_name
from users
where user_company = $company_id
";

$urc = mysql_query($usql);
$nums = mysql_num_rows($urc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array( $urc, MYSQL_ASSOC );
}
?>
<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
<TR style="border: outset #eeeeee 2px;">
	<TD class="mboxhdr">Login Name</td>
	<TD class="mboxhdr">User Name</td>
</tr>

<?php
	for ($x =0; $x < $nums; $x++){
?>
<TR bgcolor="#f4efe3">
	<TD>
		<A href="./index.php?m=admin&a=viewuser&user_id=<?php echo $tarr[$x]["user_id"];?>">
			<?php echo $tarr[$x]["user_username"];?>
		</a>
	<td><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
</tr>
<?php
	}
?>
</TABLE>
