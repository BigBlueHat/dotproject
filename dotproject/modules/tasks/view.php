<?php /* $Id$ */
$task_id = isset( $_GET['task_id'] ) ? $_GET['task_id'] : 0;

$sql = "
SELECT tasks.*,
	project_name, project_color_identifier,
	u1.user_username as username
FROM tasks
LEFT JOIN users u1 ON u1.user_id = task_owner
LEFT JOIN projects ON project_id = task_project
WHERE task_id = $task_id
";

if (!db_loadHash( $sql, $task )) {
	$AppUI->setMsg( "Invalid Task ID", UI_MSG_ERROR );
	$AppUI->redirect();
}

// check permissions
$canRead = !getDenyRead( $m, $task_id );
$canEdit = !getDenyEdit( $m, $task_id );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$AppUI->savePlace();

// get the active tab
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

$df = $AppUI->getPref('SHDATEFORMAT');

$ts = db_dateTime2unix( $task["task_start_date"] );
$start_date = $ts < 0 ? null : new CDate( $ts, $df );

$ts = db_dateTime2unix( $task["task_end_date"] );
$end_date = $ts < 0 ? null : new CDate( $ts, $df );

//Pull users on this task
$sql = "
SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
FROM users u, user_tasks t
WHERE t.task_id =$task_id AND
	t.user_id = u.user_id
";
$users = db_loadList( $sql );

//Pull files on this task
$sql = "
SELECT file_id, file_name, file_size,file_type
FROM files
WHERE file_task = $task_id
	AND file_task <> 0
";
$files = db_loadList( $sql );

// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'tasks.gif', $m, "$m.$a" );
$titleBlock->addCell(
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project='.$task['task_project'].'&task_parent=' . $task_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id={$task['task_project']}", "view this project" );
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", "edit this task" );
}
if ($canDelete) {
	$titleBlock->addCrumbRight(
		'<a href="javascript:delIt()">'
			. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
			. $AppUI->_('delete task') . '</a>'
	);
}
$titleBlock->show();
?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.updateFrm.task_' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.updateFrm.task_' + calendarField );
	fld_fdate = eval( 'document.updateFrm.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}

function updateTask() {
	var f = document.updateFrm;
	if (f.task_log_description.value.length < 1) {
		alert( "<?php echo $AppUI->_('tasksComment');?>" );
		f.task_log_description.focus();
	} else if (isNaN( parseInt( f.task_percent_complete.value+0 ) )) {
		alert( "<?php echo $AppUI->_('tasksPercent');?>" );
		f.task_percent_complete.focus();
	} else if(f.task_percent_complete.value  < 0 || f.task_percent_complete.value > 100) {
		alert( "<?php echo $AppUI->_('tasksPercentValue');?>" );
		f.task_percent_complete.focus();
	} else {
		f.submit();
	}
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('taskDelete');?>\n" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_task_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
</form>

<tr valign="top">
	<td width="50%">
		<table width="100%" cellspacing="1" cellpadding="2">
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Details');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project');?>:</td>
			<td style="background-color:#<?php echo $task["project_color_identifier"];?>">
				<font color="<?php echo bestColor( $task["project_color_identifier"] ); ?>">
					<?php echo @$task["project_name"];?>
				</font>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
			<td class="hilite"><strong><?php echo @$task["task_name"];?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Creator');?>:</td>
			<td class="hilite"> <?php echo @$task["username"];?></td>
		</tr>				<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority');?>:</td>
			<td class="hilite">
		<?php
			if ($task["task_priority"] == 0) {
				echo $AppUI->_('normal');
			} else if ($task["task_priority"] < 0){
				echo $AppUI->_('low');
			} else {
				echo $AppUI->_('high');
			}
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_related_url"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone');?>:</td>
			<td class="hilite" width="300"><?php if($task["task_milestone"]){echo "Yes";}else{echo "No";}?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_percent_complete"];?>%</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked');?>:</td>
			<td class="hilite" width="300"><?php echo @$task["task_hours_worked"];?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Dates and Targets');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite" width="300"><?php echo $start_date ? $start_date->toString( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?>:</td>
			<td class="hilite" width="300"><?php echo $end_date ? $end_date->toString( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration');?>:</td>
			<td class="hilite" width="300"><?php echo $task["task_duration"].' '.$AppUI->_( $task["task_duration_type"] );?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?>:</td>
			<td class="hilite" width="300"><?php echo $task["task_target_budget"];?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Description');?></strong></td>
		</tr>
		<tr>
			<td valign="top" height="75" colspan="2" class="hilite">
				<?php $newstr = str_replace( chr(10), "<br />", $task["task_description"]);echo $newstr;?>
			</td>
		</tr>

		</table>
	</td>

	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Assigned Users');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = '';
				foreach($users as $row) {
					$s .= '<tr>';
					$s .= '<td class="hilite">'.$row["user_username"].'</td>';
					$s .= '<td class="hilite"><a href="mailto:'.$row["user_email"].'">'.$row["user_email"].'</a></td>';
					$s .= '</tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>
	<?php // check access to files module
		if (!getDenyRead( 'files' )) {
	?>
		<tr>
			<td><strong><?php echo $AppUI->_('Attached Files');?></strong></td>
			<td colspan="2" align="right">
			<?php if (!getDenyEdit( 'files' )) { ?>
				<a href="./index.php?m=files&a=addedit&project_id=<?php echo $task["task_project"];?>&file_task=<?php echo $task_id;?>"><?php echo $AppUI->_('Attach a file');?><img src="./images/icons/forum_folder.gif" align="absmiddle" width="20" height="20" alt="attach a file to this task" border="0" /></a>
			<?php } ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = count( $files ) == 0 ? "<tr><td bgcolor=#ffffff>none</td></tr>" : '';
				foreach ($files as $row) {
					$s .= '<tr>';
					$s .= '<td class="hilite"><a href="./fileviewer.php?file_id='.$row["file_id"].'">'.$row["file_name"].'</a></td>';
					$s .= '<td class="hilite">'.$row["file_type"].'</td>';
					$s .= '<td class="hilite">'.$row["file_size"].'</td>';
					$s .= '</tr>';
				}
				echo '<table width="100%" cellspacing="1" bgcolor="black">'.$s.'</table';
			?>
			</td>
		</tr>
	<?php } // end files access ?>
		</table>
	</td>
</tr>
</table>

<?php
$query_string = "?m=tasks&a=view&task_id=$task_id";
// tabbed information boxes
$tabBox = new CTabBox( "?m=tasks&a=view&task_id=$task_id", "", $tab );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_logs", 'Task Logs' );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_log_update", 'New Log' );

$tabBox->show();
?>