<?php
$project_id = isset($HTTP_GET_VARS['project_id']) ? $HTTP_GET_VARS['project_id'] : 0;
// view mode = 0 tabbed, 1 flat
$vm = isset($HTTP_GET_VARS['vm']) ? $HTTP_GET_VARS['vm'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $project_id );
$denyEdit = getDenyEdit( $m, $project_id );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

//pull data
$psql = "
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
//echo "<pre>$psql</pre>";
$prc = mysql_query( $psql );
$prow = mysql_fetch_array( $prc, MYSQL_ASSOC );

if (strlen( $prow["project_start_date"] ) == 0) {
	$start_date = date(time());
} else {
	$start_date = mktime( 0, 0, 0, substr( $prow["project_start_date"], 5, 2),
		substr( $prow["project_start_date"], 8, 2 ),
		substr( $prow["project_start_date"], 0, 4 )
	);
}

if (strlen( $prow["project_end_date"] ) == 0) {
	$end_date = date(time()+(3600*24));
} else {
	$end_date = mktime( 0, 0, 0, substr( $prow["project_end_date"], 5, 2 ),
		substr( $prow["project_end_date"], 8, 2),
		substr($prow["project_end_date"], 0, 4 )
	);
	//$end_date = $prow["project_end_date"];
}

if (strlen( $prow["project_actual_end_date"] ) ==0) {
	$actual_end_date = 0;
} else {
	$actual_end_date = mktime( 0, 0, 0, substr($prow["project_actual_end_date"], 5, 2 ),
		substr( $prow["project_actual_end_date"], 8, 2),
		substr( $prow["project_actual_end_date"], 0, 4)
	);
}

?>

<table width="95%" border="0" cellpadding="1" cellspacing="1">
<tr>
	<td><img src="./images/icons/projects.gif" alt="" border="0"></td>
	<td nowrap><span class="title">Manage Project</span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td align="right" width="100%">
		<table width="225" cellspacing=1 cellpadding=1 class="tbl">
		<tr>
			<th>status</th>
			<th>Progress</th>
			<th>Active?</th>
		</tr>
		<tr>
			<td><?php echo $pstatus[$prow["project_status"]]; ?></td>
			<td align="center"><?php printf( "%.1f%%", $prow["project_precent_complete"] );?></td>
			<td align="center"><?php if($prow["project_active"]){?>Yes<?php }else{?>No<?php }?></td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<tr>
	<td nowrap>
	<a href="./index.php?m=projects">Project List</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="./index.php?m=projects&a=addedit&project_id=<?php echo $prow["project_id"];?>">Edit this Project</a>
<?php } ?>

	</td>
</tr>
</table>

<table border="0" cellpadding="2" cellspacing="0" width="95%" class="std">
<tr>
	<td style="border: outset #d1d1cd 1px;background-color:<?php echo $prow["project_color_identifier"];?>" colspan="2">
	<?php
		echo '<font color="' . bestColor( $prow["project_color_identifier"] ) . '"><b>'
			. $prow["project_name"] .'<b></font>';
	?>
	</td>
</tr>
<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td><b>Company:</b></td>
			<td><?php echo $prow["company_name"];?></td>
		</tr>
		<tr>
			<td><b>Short Name:</b></td>
			<td><?php echo @$prow["project_short_name"];?></td>
		</tr>
		<tr>
			<td><b>Start date:</b></td>
			<td><?php echo fromDate(substr($prow["project_start_date"], 0,10));?></td>
		</tr>
		<tr>
			<td><b>Target End Date:</b></td>
			<td><?php echo fromDate(substr($prow["project_end_date"], 0, 10));?></td>
		</tr>
		<tr>
			<td><b>Actual End Date:</b></td>
			<td><?php echo fromDate(SUBSTR($prow["project_actual_end_date"], 0, 10));?></td>
		</tr>
		<tr>
			<td><b>Target Budget:</b></td>
			<td>$<?php echo @$prow["project_target_budget"];?></td>
		</tr>
		<tr>
			<td><b>Project Owner:</b></td>
			<td><?php echo $prow["user_name"]; ?></td>
		</tr>
		<tr>
			<td><b>URL:</b></td>
			<td><A href="<?php echo @$prow["project_url"];?>" target="_new"><?php echo @$prow["project_url"];?></A></td>
		</tr>
		<tr>
			<td><b>Staging URL:</b></td>
			<td><A href="<?php echo @$prow["project_demo_url"];?>" target="_new"><?php echo @$prow["project_demo_url"];?></A></td>
		</tr>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top">
		<b>Full Description</b><br>
		<?php echo str_replace( chr(10), "<BR>", $prow["project_description"]); ?>
	</td>
</table>

<table border="0" cellpadding="2" cellspacing="0" width="95%">
<tr>
	<td>
		<a href="./index.php?m=projects&a=view&project_id=<?php echo $project_id;?>&vm=0">tabbed</a> :
		<a href="./index.php?m=projects&a=view&project_id=<?php echo $project_id;?>&vm=1">flat</a>
	</td>
</tr>
</table>

<?php	
$tabs = array(
	'tasks' => 'Tasks',
	'forums' => 'Forums',
	'files' => 'Files'
);

if ($vm == 1) { ?>
<table border="0" cellpadding="2" cellspacing="0" width="95%">
<?php
	foreach ($tabs as $k => $v) {
		echo "<tr><td><b>$v</b></td></tr>";
		echo "<tr><td>";
		include "vw_$k.php";
		echo "</td></tr>";
	}
?>
</table>
<?php 
} else {
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tasks';
	drawTabBox( $tabs, $tab, "./index.php?m=projects&a=view&project_id=$project_id", "./modules/projects" );
}

?>



