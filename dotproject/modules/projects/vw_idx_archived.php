<?php
GLOBAL $projects, $company_id;
?>

<table width="100%" border="0" bgcolor="#f4efe3" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap>&nbsp;sort by:&nbsp;</td>
	<th nowrap>
		<A href="index.php?m=projects&orderby=project_name&company_id=<?php echo $company_id;?>"><font color="white">Project Name</font></a>
	</th>
	<th nowrap>
		<A href="index.php?m=projects&orderby=user_username&company_id=<?php echo $company_id;?>"><font color="white">Owner</font></a>
	</th>
	<th nowrap>
		<A href="index.php?m=projects&orderby=total_tasks%20desc&company_id=<?php echo $company_id;?>"><font color="white">All Tasks</font></a>
	</th>
	<th nowrap>
		<A href="index.php?m=projects&orderby=project_end_date&company_id=<?php echo $company_id;?>"><font color="white">Finished</font></a>
	</th>
</tr>

<?php
foreach ($projects as $row) {
?>
<tr>
	<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#<?php echo $row["project_color_identifier"];?>">
<?php
	echo '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
		. sprintf( "%.1f%%", $row["project_precent_complete"] )
		. '</font>';
	?></td>
	<td width="100%">
		<A href="./index.php?m=projects&a=view&project_id=<?php echo $row["project_id"];?>"><?php echo $row["project_name"];?></A>
	</td>
	<td nowrap><?php echo $row["user_username"];?></td>
	<td align="center" nowrap><?php echo $row["total_tasks"];?></td>
	<td align="right" nowrap><?php echo $row["proj_end_date"];?></td>
</tr>
<?php }?>
<tr>
	<td colspan=6>&nbsp;</td>
</tr>
</table>
