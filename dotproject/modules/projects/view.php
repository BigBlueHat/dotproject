<?php /* PROJECTS $Id$ */

$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );

// check permissions for this record
$canRead = !getDenyRead( $m, $project_id );
$canEdit = !getDenyEdit( $m, $project_id );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CProject();
$canDelete = $obj->canDelete( $msg, $project_id );

// load the record data
$sql = "
SELECT
	company_name,
	CONCAT_WS(' ',user_first_name,user_last_name) user_name,
	projects.*,
	SUM(t1.task_duration*t1.task_duration_type*t1.task_percent_complete)/SUM(t1.task_duration*t1.task_duration_type) AS project_percent_complete
FROM projects
LEFT JOIN companies ON company_id = project_company
LEFT JOIN users ON user_id = project_owner
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_id = $project_id
GROUP BY project_id
";

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// worked hours
$sql = "SELECT SUM(task_log_hours) FROM task_log, tasks WHERE task_log_task = task_id AND task_project = $project_id";
$worked_hours = db_loadResult($sql);

// total hours
$sql = "SELECT SUM(task_duration) FROM tasks WHERE task_project = $project_id AND task_duration_type = 24";
$days = db_loadResult($sql);
$sql = "SELECT SUM(task_duration) FROM tasks WHERE task_project = $project_id AND task_duration_type = 1";
$hours = db_loadResult($sql);
$total_hours = $days * $dPconfig['daily_working_hours'] + $hours;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
$end_date = intval( $obj->project_end_date ) ? new CDate( $obj->project_end_date ) : null;
$actual_end_date = intval( $obj->project_actual_end_date ) ? new CDate( $obj->project_actual_end_date ) : null;

// setup the title block
$titleBlock = new CTitleBlock( 'View Project', 'applet3-48.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=projects", "projects list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=projects&a=addedit&project_id=$project_id", "edit this project" );
	if ($canEdit) {
		$titleBlock->addCrumbDelete( 'delete project', $canDelete, $msg );
	}
}
$titleBlock->addCrumb( "?m=projects&a=reports&project_id=$project_id", "reports" );
$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Project').'?';?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
</form>

<tr>
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $obj->project_color_identifier;?>" colspan="2">
	<?php
		echo '<font color="' . bestColor( $obj->project_color_identifier ) . '"><strong>'
			. $obj->project_name .'<strong></font>';
	?>
	</td>
</tr>

<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->company_name;?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Short Name');?>:</td>
			<td class="hilite"><?php echo @$obj->project_short_name;?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Target End Date');?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Actual End Date');?>:</td>
			<td class="hilite"><?php echo $actual_end_date ? $actual_end_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Target Budget');?>:</td>
			<td class="hilite"><?php echo $dPconfig['currency_symbol'] ?><?php echo @$obj->project_target_budget;?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Project Owner');?>:</td>
			<td class="hilite"><?php echo $obj->user_name; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite"><a href="<?php echo @$obj->project_url;?>" target="_new"><?php echo @$obj->project_url;?></A></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Staging URL');?>:</td>
			<td class="hilite"><a href="<?php echo @$obj->project_demo_url;?>" target="_new"><?php echo @$obj->project_demo_url;?></a></td>
		</tr>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top">
		<strong><?php echo $AppUI->_('Summary');?></strong><br />
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Status');?>:</td>
			<td class="hilite" width="100%"><?php echo $pstatus[$obj->project_status];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="100%"><?php printf( "%.1f%%", $obj->project_percent_complete );?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Active');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No');?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Worked Hours');?>:</td>
			<td class="hilite" width="100%"><?php echo $worked_hours ?></td>
		</tr>	
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Total Hours');?>:</td>
			<td class="hilite" width="100%"><?php echo $total_hours ?></td>
		</tr>		
		</table>
		<strong><?php echo $AppUI->_('Description');?></strong><br />
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $obj->project_description); ?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</table>

<?php
if ($tab == 1) {
	$_GET['task_status'] = -1;
}
$query_string = "?m=projects&a=view&project_id=$project_id";
// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&a=view&project_id=$project_id", "", $tab );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks", 'Tasks' );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks", 'Tasks (In-Active)' );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_forums", 'Forums' );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_files", 'Files' );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/viewgantt", 'Gantt Chart' );

// settings for tasks
$f = 'all';
$min_view = true;

$tabBox->show();
?>
