<?php
require_once( "$root_dir/classdefs/date.php" );

$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $project_id );
$denyEdit = getDenyEdit( $m, $project_id );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

$AppUI->setState( 'ActiveProject', $project_id );

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

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
db_loadHash( $sql, $project );

$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = $project["project_start_date"] ? CDate::fromDateTime( $project["project_start_date"] ) : new CDate();
$start_date->setFormat( $df );

$end_date = $project["project_end_date"] ? CDate::fromDateTime( $project["project_end_date"] ) : new CDate();
$end_date->setFormat( $df );

$actual_end_date = $project["project_actual_end_date"] ? CDate::fromDateTime( $project["project_actual_end_date"] ) : new CDate();
$actual_end_date->setFormat( $df );

$crumbs = array();
$crumbs["?m=projects"] = "projects list";
if (!$denyEdit) {
	$crumbs["?m=projects&a=addedit&project_id=$project_id"] = "edit this project";
}
?>

<table width="98%" border="0" cellpadding="1" cellspacing="1">
<tr>
	<td><img src="./images/icons/projects.gif" alt="" border="0"></td>
	<td nowrap><span class="title"><?php echo $AppUI->_('Manage Project');?></span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td align="right" width="100%">
		<table width="225" cellspacing="1" cellpadding="1" class="tbl">
		<tr>
			<th><?php echo $AppUI->_('Status');?></th>
			<th><?php echo $AppUI->_('Progress');?></th>
			<th><?php echo $AppUI->_('Active');?>?</th>
		</tr>
		<tr>
			<td><?php echo $AppUI->_($pstatus[$project["project_status"]]); ?></td>
			<td align="center"><?php printf( "%.1f%%", $project["project_precent_complete"] );?></td>
			<td align="center"><?php echo $project["project_active"] ? $AppUI->_('Yes') : $AppUI->_('No');?></td>
		</tr>
		</table>
	</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td nowrap="nowrap"><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;background-color:<?php echo $project["project_color_identifier"];?>" colspan="2">
	<?php
		echo '<font color="' . bestColor( $project["project_color_identifier"] ) . '"><b>'
			. $project["project_name"] .'<b></font>';
	?>
	</td>
</tr>
<tr>
	<td width="50%" valign="top">
		<b><?php echo $AppUI->_('Details');?></b>
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
		<b><?php echo $AppUI->_('Description');?></b><br>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<BR>", $project["project_description"]); ?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</table>

<?php	
// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&a=view&project_id=$project_id", "./modules/projects", $tab );
$tabBox->add( 'vw_tasks', 'Tasks' );
$tabBox->add( 'vw_forums', 'Forums' );
$tabBox->add( 'vw_files', 'Files' );
$tabBox->add( 'vw_gantt', 'Gantt Chart' );
$tabBox->show();
?>
