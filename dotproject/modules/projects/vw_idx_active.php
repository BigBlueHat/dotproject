<?php
GLOBAL $AppUI, $projects, $company_id;
$df = $AppUI->getPref('SHDATEFORMAT');
?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;sort by:&nbsp;</td>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_name" class="hdr"><?php echo $AppUI->_('Name');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=total_tasks%20desc" class="hdr"><?php echo $AppUI->_('Tasks');?></a>
		<a href="?m=projects&orderby=my_tasks%20desc" class="hdr">(<?php echo $AppUI->_('My');?>)</a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_end_date" class="hdr">Due Date:</a>
	</th>
</tr>

<?php
foreach ($projects as $row) {
	$end_date = $row["project_end_date"] ? new CDate( db_dateTime2unix( $row["project_end_date"] ) ) : null;
?>
<tr>
	<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#<?php echo $row["project_color_identifier"];?>">
<?php
	echo '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
		. sprintf( "%.1f%%", $row["project_precent_complete"] )
		. '</font>';
	?></td>
	<td width="100%">
		<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>"><?php echo $row["project_name"];?></A>
	</td>
	<td nowrap="nowrap"><?php echo $row["user_username"];?></td>
	<td align="center" nowrap>
		<?php echo $row["total_tasks"].($row["my_tasks"] ? ' ('.$row["my_tasks"].')' : '');?>
		</td>
	<td align="right" nowrap="nowrap"><?php echo $end_date ? $end_date->toString( $df ) : '-';?></td>
</tr>
<?php }?>
<tr>
	<td colspan="6">&nbsp;</td>
</tr>
</table>
