<?php
##
##	Companies: View Projects sub-table
##
GLOBAL $company_id; 

$psql = "
select projects.*, users.user_first_name,users.user_last_name
from projects
left join users on users.user_id = projects.project_owner
where project_company = $company_id
order by project_name
";
$prc = mysql_query($psql);
$nums = mysql_num_rows($prc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array( $prc, MYSQL_ASSOC );
}
?>
<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
<TR style="border: outset #eeeeee 2px;">
	<TD class="mboxhdr">Name</td>
	<TD class="mboxhdr">Owner</td>
</tr>

<?php
for ($x =0; $x < $nums; $x++){
	if ($tarr[$x]["project_active"] == 0) {
?>
<TR bgcolor="#f4efe3">
	<TD>
		<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
			<?php echo $tarr[$x]["project_name"];?>
		</a>
	<td><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
</tr>
<?php
	}
}
?>
</TABLE>

