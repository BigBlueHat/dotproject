<?php /* PROJECTS $Id$ */
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
$CR = "\n";
$CT = "\n\t";
$none = true;
foreach ($projects as $row) {
	if ($row["project_active"] > 0) {
		$none = false;
		$ts = db_dateTime2unix( $row["project_end_date"] );
		$end_date = $ts < 0 ? null : new CDate( $ts );
		$s = '<tr>';
		$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#'
			. $row["project_color_identifier"] . '">';
		$s .= $CT . '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. sprintf( "%.1f%%", $row["project_precent_complete"] )
			. '</font>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td width="100%">';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row["project_id"] . '">' . $row["project_name"] . '</a>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap">' . $row["user_username"] . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row["total_tasks"] . ($row["my_tasks"] ? ' ('.$row["my_tasks"].')' : '');
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="right" nowrap="nowrap">';
		$s .= $CT . ($end_date ? $end_date->toString( $df ) : '-');
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
	<td colspan="6">&nbsp;</td>
</tr>
</table>
