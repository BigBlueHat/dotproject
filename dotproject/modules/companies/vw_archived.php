<?php
##
##	Companies: View Archived Projects sub-table
##
GLOBAL $AppUI, $company_id; 

$sql = "
SELECT projects.*, users.user_first_name,users.user_last_name
FROM projects
LEFT JOIN users ON users.user_id = projects.project_owner
WHERE project_company = $company_id
	and project_active = 0
ORDER BY project_name
";

if (!($rows = db_loadList( $sql, NULL ))) {
	echo 'None Available<br>'.$AppUI->getMsg();
} else {
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>Name</td>
	<th>Owner</td>
</tr>

<?php
$s = '';
foreach ($rows as $row){
	$s .= '<tr><td>';
	$s .= '<a href="./index.php?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a>';
	$s .= '<td>'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
	$s .= '</tr>';
}
echo $s;
?>
</table>
<?php } ?>
