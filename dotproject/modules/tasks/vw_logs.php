<?php /* TASKS $Id$ */
global $AppUI, $task_id, $df, $canEdit;
?>
<script language="JavaScript">
function delIt(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task Log').'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<form name="frmDelete2" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_updatetask">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>

<tr>
	<th></th>
	<th><?php echo $AppUI->_('Date');?></th>
	<th width="100"><?php echo $AppUI->_('Summary');?></th>
	<th width="100"><?php echo $AppUI->_('User');?></th>
	<th width="100"><?php echo $AppUI->_('Hours');?></th>
	<th width="100"><?php echo $AppUI->_('Cost Code');?></th>
	<th width="100%"><?php echo $AppUI->_('Comments');?></th>
	<th></th>
</tr>
<?php
// Pull the task comments
$sql = "
SELECT task_log.*, user_username
FROM task_log
LEFT JOIN users ON user_id = task_log_creator
WHERE task_log_task = $task_id
ORDER BY task_log_date
";
$logs = db_loadList( $sql );

$s = '';
$hrs = 0;
foreach ($logs as $row) {
	$task_log_date = intval( $row['task_log_date'] ) ? new CDate( $row['task_log_date'] ) : null;

	$s .= '<tr bgcolor="white" valign="top">';
	$s .= "\n\t<td>";
	if ($canEdit) {
		$s .= "\n\t\t<a href=\"?m=tasks&a=view&task_id=$task_id&tab=1&task_log_id=".@$row['task_log_id']."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '<td nowrap="nowrap">'.($task_log_date ? $task_log_date->format( $df ) : '-').'</td>';
	$s .= '<td width="30%">'.@$row["task_log_name"].'</td>';
	$s .= '<td width="100">'.$row["user_username"].'</td>';
	$s .= '<td width="100" align="right">'.sprintf( "%.2f", $row["task_log_hours"] ) . '</td>';
	$s .= '<td width="100">'.$row["task_log_costcode"].'</td>';
	$s .= '<td>'.str_replace(chr(10), "<br />",$row["task_log_description"]).'</td>';
	$s .= "\n\t<td>";
	if ($canEdit) {
		$s .= "\n\t\t<a href=\"javascript:delIt({$row['task_log_id']});\" title=\"".$AppUI->_('delete log')."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '</tr>';
	$hrs += (float)$row["task_log_hours"];
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="3" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= '<td align="right">' . sprintf( "%.2f", $hrs ) . '</td>';
$s .= '</tr>';
echo $s;
?>
</table>