<?php /* TASKS $Id$ */
	global $AppUI, $project_id, $df, $canEdit, $m, $tab;

	$sql = "SELECT user_id, concat(user_first_name,' ',user_last_name)  FROM users ORDER BY user_last_name,user_first_name";
	$users = arrayMerge( array( '-1' => $AppUI->_('All Users') ), db_loadHashList( $sql ) );

	if (isset( $_GET['user_id'] )) {
		$AppUI->setState( 'ProjectsTaskLogsUserFilter', $_GET['user_id'] );
	}
	$user_id = $AppUI->getState( 'ProjectsTaskLogsUserFilter' ) ? $AppUI->getState( 'ProjectsTaskLogsUserFilter' ) : $AppUI->user_id;
?>
<script language="JavaScript">
function delIt2(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task Log').'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
</script>
<table border="0" cellpadding="2" cellspacing="1" width="100%" class="std">
<form name="frmFilter" action="./index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>" method="get">
<input type="hidden" name="m" value="projects"/>
<input type="hidden" name="a" value="view"/>
<input type="hidden" name="project_id" value="<?=$project_id?>"/>
<input type="hidden" name="tab" value="<?=$tab?>"/>
<tr>
	<td width="98%">&nbsp;</td>
	<td width="1%" nowrap="nowrap"><?=$AppUI->_('User Filter')?></td>
	<td width="1%"><?=arraySelect( $users, 'user_id', 'size="1" class="text" id="medium" onchange="document.frmFilter.submit()"',
                          $user_id )?></td>
</tr>
</form>
</table>
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
SELECT task_log.*, user_username, task_id
FROM 
	task_log
	LEFT JOIN users ON user_id = task_log_creator
	LEFT JOIN tasks ON task_log_task = tasks.task_id
	LEFT JOIN projects ON task_project = project_id
WHERE 
	task_project = $project_id ".
	($user_id>0?" AND task_log_creator=$user_id ":'').
"ORDER BY task_log_date
";
//print "<pre>$sql</pre>";
$logs = db_loadList( $sql );

$s = '';
$hrs = 0;
foreach ($logs as $row) {
	$task_log_date = intval( $row['task_log_date'] ) ? new CDate( $row['task_log_date'] ) : null;

	$s .= '<tr bgcolor="white" valign="top">';
	$s .= "\n\t<td>";
	if (!getDenyEdit($m, $row['task_id']) ) {
		$s .= "\n\t\t<a href=\"?m=tasks&a=view&task_id=".$row['task_id']."&tab=1&task_log_id=".@$row['task_log_id']."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '<td nowrap="nowrap">'.($task_log_date ? $task_log_date->format( $df ) : '-').'</td>';
	$s .= '<td width="30%"><a href="?m=tasks&a=view&task_id='.$row['task_id'].'&tab=0">'.@$row["task_log_name"].'</a></td>';
	$s .= '<td width="100">'.$row["user_username"].'</td>';
	$s .= '<td width="100" align="right">'.sprintf( "%.2f", $row["task_log_hours"] ) . '</td>';
	$s .= '<td width="100">'.$row["task_log_costcode"].'</td>';
	$s .= '<td>';

// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
	$descrip = str_replace( "\n", "<br />", $row['task_log_description'] );
	$tranpos = strpos( $descrip, str_replace( "\n", "<br />", $transbrk ) );
	if ( $tranpos === false) $s .= $descrip;
	else
	{
		$descrip = substr( $descrip, 0, $tranpos );
		$tranpos = strpos( $row['task_log_description'], $transbrk );
		$transla = substr( $row['task_log_description'], $tranpos + strlen( $transbrk ) );
		$transla = trim( str_replace( "'", '"', $transla ) );
		$s .= $descrip."<div style='font-weight: bold; text-align: right'><a title='$transla' class='hilite'>[".$AppUI->_("translation")."]</a></div>";
	}
// end auto-translation code
			
	$s .= '</td>';
	$s .= "\n\t<td>";
	if ($canEdit) {
		$s .= "\n\t\t<a href=\"javascript:delIt2({$row['task_log_id']});\" title=\"".$AppUI->_('delete log')."\">"
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
