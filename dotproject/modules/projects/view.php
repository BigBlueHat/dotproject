<?php /* PROJECTS $Id$ */
$project_id = dPgetParam( $_GET, "project_id", 0 );

// check permissions for this project
$canEdit = !getDenyEdit( $m, $project_id );
$AppUI->savePlace();

$AppUI->setState( 'ActiveProject', $project_id );

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

$sql = "SELECT COUNT(task_id) FROM tasks WHERE task_project = $project_id";
$canDelete = (db_loadResult( $sql ) < 1);

//pull data
$sql = "
SELECT
	company_name,
	CONCAT(user_first_name, ' ', user_last_name) user_name,
	projects.*,
	SUM(t1.task_duration*t1.task_precent_complete)/SUM(t1.task_duration) AS project_precent_complete
FROM projects
LEFT JOIN companies ON company_id = project_company
LEFT JOIN users ON user_id = project_owner
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_id = $project_id
GROUP BY project_id
";
//echo "<pre>$sql</pre>";

if (!db_loadHash( $sql, $project )) {
	$titleBlock = new CTitleBlock( 'Invalid Project ID', 'projects.gif', $m, 'ID_HELP_PROJ_VIEW' );
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	$titleBlock->show();
} else {
	$df = $AppUI->getPref('SHDATEFORMAT');

	$start_date = $project["project_start_date"] ? CDate::fromDateTime( $project["project_start_date"] ) : new CDate();
	$start_date->setFormat( $df );

	$end_date = $project["project_end_date"] ? CDate::fromDateTime( $project["project_end_date"] ) : new CDate();
	$end_date->setFormat( $df );

	$actual_end_date = $project["project_actual_end_date"] ? CDate::fromDateTime( $project["project_actual_end_date"] ) : new CDate();
	$actual_end_date->setFormat( $df );

// setup the title block
	$titleBlock = new CTitleBlock( 'View Project', 'projects.gif', $m, 'ID_HELP_PROJ_VIEW' );
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
			'<form action="?m=tasks&a=addedit&project_id=' . $project_id . '" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	if ($canEdit) {
		$titleBlock->addCrumb( "?m=projects&a=addedit&project_id=$project_id", "edit this project" );
		if ($canDelete) {
			$titleBlock->addCrumbRight(
				'<a href="javascript:delIt()">'
					. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
					. $AppUI->_('delete project') . '</a>'
			);
		}
	}
	$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('projectsDelete');?>" )) {
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
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $project["project_color_identifier"];?>" colspan="2">
	<?php
		echo '<font color="' . bestColor( $project["project_color_identifier"] ) . '"><strong>'
			. $project["project_name"] .'<strong></font>';
	?>
	</td>
</tr>

<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%"><?php echo $project["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Short Name');?>:</td>
			<td class="hilite"><?php echo @$project["project_short_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite"><?php echo $start_date->isValid() ? $start_date->toString() : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Target End Date');?>:</td>
			<td class="hilite"><?php echo $end_date->isValid() ? $end_date->toString() : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Actual End Date');?>:</td>
			<td class="hilite"><?php echo $actual_end_date->isValid() ? $actual_end_date->toString() : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Target Budget');?>:</td>
			<td class="hilite">$<?php echo @$project["project_target_budget"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Project Owner');?>:</td>
			<td class="hilite"><?php echo $project["user_name"]; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite"><A href="<?php echo @$project["project_url"];?>" target="_new"><?php echo @$project["project_url"];?></A></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Staging URL');?>:</td>
			<td class="hilite"><A href="<?php echo @$project["project_demo_url"];?>" target="_new"><?php echo @$project["project_demo_url"];?></A></td>
		</tr>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top">
		<strong><?php echo $AppUI->_('Summary');?></strong><br />
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Status');?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($pstatus[$project["project_status"]]);?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="100%"><?php printf( "%.1f%%", $project["project_precent_complete"] );?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Active');?>:</td>
			<td class="hilite" width="100%"><?php echo $project["project_active"] ? $AppUI->_('Yes') : $AppUI->_('No');?></td>
		</tr>
		</table>
		<strong><?php echo $AppUI->_('Description');?></strong><br />
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $project["project_description"]); ?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</table>

<?php
	$query_string = "?m=projects&a=view&project_id=$project_id";
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=projects&a=view&project_id=$project_id", "", $tab );
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks", 'Tasks' );
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_forums", 'Forums' );
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_files", 'Files' );
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/viewgantt", 'Gantt Chart' );

	// settings for tasks
	$f = 'all';
	$min_view = true;

	$tabBox->show();
}
?>
