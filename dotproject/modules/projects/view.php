<?php

$project_id = isset($HTTP_GET_VARS['project_id']) ? $HTTP_GET_VARS['project_id'] : 0;

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

<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
<TR>
	<TD><img src="./images/icons/projects.gif" alt="" border="0"></td>
	<TD nowrap><span class="title">Manage Project</span></td>
	<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<TD valign="top" align="right" width="100%">
		<TABLE width="400" bgcolor="#000000" cellspacing=1 cellpadding=2>
		<TR>
			<TD bgcolor="#eeeeee"><b>status</b></span> </TD>
			<TD nowrap bgcolor="#eeeeee"><b>Percent Complete</b></TD>
			<TD bgcolor="#eeeeee"><b>Active?</b></TD>
		</TR>
		<TR>
			<TD bgcolor="#eeeeee">
				<?php echo $pstatus[$prow["project_status"]]; ?>
			</TD>
			<TD bgcolor="#eeeeee"><?php printf( "%.1f%%", $prow["project_precent_complete"] );?></TD>
			<TD bgcolor="#eeeeee"><?php if($prow["project_active"]){?>Yes<?php }else{?>No<?php }?></TD>
		</TR>
		</TABLE>
	</td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<TR>
	<TD width="50%" nowrap>
	<a href="./index.php?m=projects">Project List</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="./index.php?m=projects&a=addedit&project_id=<?php echo $prow["project_id"];?>">Edit this Project</a>
<?php } ?>

	</td>
	<TD width="50%" align="right"><?php include ("./includes/create_new_menu.php");?></td>
</TR>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<TR>
	<TD style="border: outset #eeeeee 2px;" bgcolor="<?php echo $prow["project_color_identifier"];?>">
	<?php
		echo '<font color="' . bestColor( $prow["project_color_identifier"] ) . '"><b>'
			. $prow["project_name"] .'<b></font>';
	?>
	</TD>
</tr>
</TABLE>

<table border="0" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
<tr valign="top">
	<td width="50%">
		<TABLE width="100%">
		<TR>
			<TD><b>Company:</b></TD>
			<td><?php echo $prow["company_name"];?></td>
		</TR>
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
			<TD><A href="<?php echo @$prow["project_demo_url"];?>" target="_new"><?php echo @$prow["project_demo_url"];?></A></TD>
		</tr>
		</TABLE>
	</TD>
	<td width="50%">
		<b>Full Description</b><br>
		<?php echo str_replace( chr(10), "<BR>", $prow["project_description"]); ?>
	</td>
</TR>
</table>

<?php //------Begin Task Include--------?>
<?php

?>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR>
		<TD width=50% valign=top>
		
			<?php require "vw_tasks.php"; ?>
		
		</TD>

		<TD width=50% valign=top>
			<?php require "vw_forums.php"; ?>
		</TD>
	</TR>
</TABLE>

