<?php /* PROJECTS $Id$ */
GLOBAL $AppUI, $projects, $company_id, $pstatus;
$df = $AppUI->getPref('SHDATEFORMAT');

	// Let's check if the user submited the change status form

?>

<form action='./index.php' method='get'>


<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
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
		<?php echo $AppUI->_('Selection'); ?>
	</th>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
$none = true;
foreach ($projects as $row) {
	if ($row["project_active"] > 0 && $row["project_status"] == 1) {
		$none = false;
		$end_date = intval( @$row["project_end_date"] ) ? new CDate( $row["project_end_date"] ) : null;

		$s = '<tr>';
		$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#'
			. $row["project_color_identifier"] . '">';
		$s .= $CT . '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. sprintf( "%.1f%%", $row["project_percent_complete"] )
			. '</font>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td width="100%">';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row["project_id"] . '" title="' . $row["project_description"] . '">' . $row["project_name"] . '</a>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap">' . $row["user_username"] . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row["total_tasks"] . ($row["my_tasks"] ? ' ('.$row["my_tasks"].')' : '');
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="center">';
		$s .= $CT . '<input type="checkbox" name="project_id[]" value="'.$row["project_id"].'" />';
		$s .= $CR . '</td>';
		$s .= $CR . '</tr>';
		echo $s;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="6">' . $AppUI->_( 'No projects available' ) . '</td></tr>';
}
?>
<tr>
	<td colspan="6" align="right">
		<?php
			echo "<input type='submit' class='button' value='".$AppUI->_('Update projects status')."' />";
			echo "<input type='hidden' name='update_project_status' value='1' />";
			echo "<input type='hidden' name='m' value='projects' />";
			echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', 2, false );
			                                                                // 2 will be the next step
		?>
	</td>
</tr>
</table>
</form>
