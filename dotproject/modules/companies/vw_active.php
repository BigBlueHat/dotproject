<?php
##
##	Companies: View Projects sub-table
##
GLOBAL $company_id; 

$sql = "
SELECT project_id, project_name, project_start_date, project_status, project_target_budget,
	DATE_FORMAT(project_start_date, '%d-%b-%Y' ) project_start_date,
	users.user_first_name, users.user_last_name
from projects
left join users on users.user_id = projects.project_owner
where project_company = $company_id
	and project_active <> 0
order by project_name
";

$rows = db_loadList( $sql, NULL, __LINE__ );
?>
<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">
<tr>
	<th>Name</th>
	<th>Owner</th>
	<th>Started</th>
	<th>Status</th>
	<th>Budget</th>
</tr>

<?php 
$s = '';	
foreach ($rows as $row) {
	$s .= '<tr>';
	$s .= '<td width="100%">';
	$s .= '<a href="./index.php?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a>';
	$s .= '<td nowrap>'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
	$s .= '<td nowrap>'.$row["project_start_date"].'</td>';
	$s .= '<td nowrap>'.$pstatus[$row["project_status"]].'</td>';
	$s .= '<td nowrap align=right>$ '.$row["project_target_budget"].'</td>';
	$s .= '</tr>';
}
echo $s;
?>
</table>
