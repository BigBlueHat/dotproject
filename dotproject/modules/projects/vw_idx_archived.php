<?php
GLOBAL $projects, $company_id;
?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
	<th nowrap>
		<a href="?m=projects&orderby=project_name" class="hdr"><?php echo $AppUI->_('Name');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=total_tasks%20desc" class="hdr"><?php echo $AppUI->_('Tasks');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_end_date" class="hdr"><?php echo $AppUI->_('Finished');?></a>
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
		<A href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>"><?php echo $row["project_name"];?></A>
	</td>
	<td nowrap="nowrap"><?php echo $row["user_username"];?></td>
	<td align="center" nowrap="nowrap"><?php echo $row["total_tasks"];?></td>
	<td align="right" nowrap="nowrap"><?php echo $row["proj_end_date"];?></td>
</tr>
<?php }?>
<tr>
	<td colspan="6">&nbsp;</td>
</tr>
</table>
