<?php

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

//Projects
$company_id = isset($_REQUEST["company_id"]) ? $_REQUEST["company_id"] : $thisuser_company;

//Set up defaults
$orderby = isset($HTTP_GET_VARS["orderby"]) ? $HTTP_GET_VARS["orderby"] : 'project_end_date';
// view mode = 0 tabbed, 1 flat

$active = ($tab == 'idx_archived') ? 0 : 1;

// get read denied projects
$dsql = "
SELECT project_id
FROM projects, permissions
WHERE permission_user = $user_cookie
	AND permission_grant_on = 'projects' 
	AND permission_item = project_id
	AND permission_value = 0
";
//echo "<pre>$dsql</pre>";
$drc = mysql_query($dsql);
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
	$deny[] = $row[0];
}

// pull projects
$projects = array();
$psql = "
SELECT
	project_id, project_status, project_color_identifier, project_name,
	DATE_FORMAT(project_start_date, '%d %b %Y') project_start_date, 
	DATE_FORMAT(project_end_date, '%d %b %Y') project_end_date, 
	project_color_identifier,
	COUNT(distinct t1.task_id) AS total_tasks,
	COUNT(distinct t2.task_id) AS my_tasks,
	user_username,
	SUM(t1.task_duration*t1.task_precent_complete)/sum(t1.task_duration) as project_precent_complete
FROM permissions,projects
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
LEFT JOIN tasks t2 ON projects.project_id = t2.task_project
	AND t2.task_owner = $thisuser_id
WHERE project_active = $active"
.($company_id ? "\nAND project_company = $company_id" : '')
."
	AND permission_user = $user_cookie
	AND permission_value <> 0 
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
" . (COUNT($deny) > 0 ? 'AND project_id NOT IN (' . implode( ',', $deny ) . ')' : '') . "
GROUP BY project_id
ORDER BY $orderby";
//echo "<pre>$psql</pre>";
$prc = mysql_query( $psql );
while ($row = mysql_fetch_array( $prc, MYSQL_ASSOC )) {
	$projects[] = $row;
}

$companies = array( 0 => 'all' );
$csql = "SELECT company_id,company_name FROM companies ORDER BY company_name";
$crc = mysql_query($csql);
while ($row = mysql_fetch_row( $crc )) {
	$companies[$row[0]] = $row[1];
}
?>

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickCompany">
<tr>
	<td><img src="./images/icons/projects.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap><span class="title">Project Management</span></td>
	<td align="right" width="100%">
		Company:
<?php
	echo arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id );
?>		
	</td>
</tr>
</form>
</table>

<img src="images/shim.gif" width="1" height="10" alt="" border="0"><br>

<?php	
$tabs = array(
	'idx_active' => 'Active',
	'idx_archived' => 'Archived'
);

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'idx_active';
drawTabBox( $tabs, $tab, "./index.php?m=projects&company_id=$company_id&order_by=$order_by", "./modules/projects" );

?>
