<?php /* $Id$ */

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );

// check permissions for this record
$canRead = !getDenyRead( $m, $task_id );
$canEdit = !getDenyEdit( $m, $task_id );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$sql = "
SELECT tasks.*,
	project_name, project_color_identifier,
	u1.user_username as username
FROM tasks
LEFT JOIN users u1 ON u1.user_id = task_owner
LEFT JOIN projects ON project_id = task_project
WHERE task_id = $task_id
";

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CTask();
$canDelete = $obj->canDelete( $msg, $task_id );

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;

// get the users on this task
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

$durnTypes = dPgetSysVal( 'TaskDurationType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'applet-48.png', $m, "$m.$a" );
$titleBlock->addCell(
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project='.$obj->task_project.'&task_parent=' . $task_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$obj->task_project", "view this project" );
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", "edit this task" );
}
if ($canEdit) {
	$titleBlock->addCrumbDelete( 'delete task', $canDelete, $msg );
}
$titleBlock->show();
?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function updateTask() {
	var f = document.editFrm;
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
			<td style="background-color:#<?php echo $obj->project_color_identifier;?>">
				<font color="<?php echo bestColor( $obj->project_color_identifier ); ?>">
					<?php echo @$obj->project_name;?>
				</font>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
			<td class="hilite"><strong><?php echo @$obj->task_name;?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Creator');?>:</td>
			<td class="hilite"> <?php echo @$obj->username;?></td>
		</tr>				<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority');?>:</td>
			<td class="hilite">
		<?php
			if ($obj->task_priority == 0) {
				echo $AppUI->_('normal');
			} else if ($obj->task_priority < 0){
				echo $AppUI->_('low');
			} else {
				echo $AppUI->_('high');
			}
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address');?>:</td>
			<td class="hilite" width="300"><?php echo @$obj->task_related_url;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone');?>:</td>
			<td class="hilite" width="300"><?php if($obj->task_milestone){echo $AppUI->_("Yes");}else{echo $AppUI->_("No");}?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="300"><?php echo @$obj->task_percent_complete;?>%</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked');?>:</td>
			<td class="hilite" width="300"><?php echo @$obj->task_hours_worked;?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Dates and Targets');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite" width="300"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?>:</td>
			<td class="hilite" width="300"><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration');?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_duration.' '.$AppUI->_( $durnTypes[$obj->task_duration_type] );?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_target_budget;?></td>
		</tr>
		<tr>
			<td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Description');?></strong></td>
		</tr>
		<tr>
			<td valign="top" height="75" colspan="2" class="hilite">
				<?php $newstr = str_replace( chr(10), "<br />", $obj->task_description);echo $newstr;?>
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
			<td width="100%"><strong><?php echo $AppUI->_('Attached Files');?></strong></td>
			<td align="right" nowrap="nowrap">
			<?php if (!getDenyEdit( 'files' )) { ?>
				<a href="./index.php?m=files&a=addedit&project_id=<?php echo $obj->task_project;?>&file_task=<?php echo $task_id;?>"><?php echo $AppUI->_('Attach a file');?>
				</a>
			<?php } ?>
			</td>
			<td width="20">
				<?php echo dPshowImage( dPfindImage( 'stock_attach-16.png', $m ), 16, 16, '' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = count( $files ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
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