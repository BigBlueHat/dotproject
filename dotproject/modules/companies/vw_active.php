<?php
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $pstatus;

$df = $AppUI->getPref('SHDATEFORMAT');

$sql = "
SELECT project_id, project_name, project_start_date, project_status, project_target_budget,
	project_start_date,
	users.user_first_name, users.user_last_name
FROM projects
LEFT JOIN users ON users.user_id = projects.project_owner
WHERE project_company = $company_id
	AND project_active <> 0
ORDER BY project_name
";

$s = '';

if (!($rows = db_loadList( $sql, NULL ))) {
	$s .= $AppUI->_( 'No data available' ).'<br>'.$AppUI->getMsg();
} else {
	$s .= '<tr>';
	$s .= '<th>'.$AppUI->_( 'Name' ).'</th>'
		.'<th>'.$AppUI->_( 'Owner' ).'</th>'
		.'<th>'.$AppUI->_( 'Started' ).'</th>'
		.'<th>'.$AppUI->_( 'Status' ).'</th>'
		.'<th>'.$AppUI->_( 'Budget' ).'</th>'
		.'</tr>';
	foreach ($rows as $row) {
		$start_date = CDate::fromDateTime( $row['project_start_date'] );
		$start_date->setFormat( $df );
		$s .= '<tr>';
		$s .= '<td width="100%">';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a></td>';
		$s .= '<td nowrap="nowrap">'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
		$s .= '<td nowrap="nowrap">'.$start_date->toString().'</td>';
		$s .= '<td nowrap="nowrap">'.$pstatus[$row["project_status"]].'</td>';
		$s .= '<td nowrap="nowrap" align="right">$ '.$row["project_target_budget"].'</td>';
		$s .= '</tr>';
	}
}
echo '<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">' . $s . '</table>';
?>
