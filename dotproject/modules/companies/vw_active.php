<?php
##
##	Companies: View Projects sub-table
##
GLOBAL $company_id; 

$psql = "
SELECT project_name, project_start_date, project_status, project_target_budget,
	DATE_FORMAT(project_start_date, '%d-%b-%Y' ) project_start_date,
	users.user_first_name, users.user_last_name
from projects
left join users on users.user_id = projects.project_owner
where project_company = $company_id
	and project_active <> 0
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
	<TD class="mboxhdr">Started</td>
	<TD class="mboxhdr">Status</td>
	<TD class="mboxhdr">Budget</td>
</tr>

<?php 
for ($x =0; $x < $nums; $x++){
	?>
	<TR bgcolor="#f4efe3">
		<TD width="100%">
			<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
				<?php echo $tarr[$x]["project_name"];?>
			</a>
		<td nowrap><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
		<td nowrap><?php echo $tarr[$x]["project_start_date"]; ?></td>
		<td nowrap><?php echo $pstatus[$tarr[$x]["project_status"]]; ?></td>
		<td nowrap align=right>$ <?php echo $tarr[$x]["project_target_budget"]; ?></td>
	</tr>
<?php
}
?>
</TABLE>
