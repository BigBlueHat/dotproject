<?php /* COMPANIES $Id$ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $pstatus, $dPconfig;

$sort = dPgetParam($_GET, 'sort', 'project_name');
if ($sort == 'project_priority')
        $sort .= ' DESC';

$df = $AppUI->getPref('SHDATEFORMAT');

$sql = "
SELECT project_id, project_name, project_start_date, project_status, project_target_budget,
	project_start_date,
        project_priority,
	contact_first_name, contact_last_name
FROM projects
LEFT JOIN users ON users.user_id = projects.project_owner
LEFT JOIN contacts ON user_contact = contact_id
WHERE project_company = $company_id
	AND project_active <> 0
ORDER BY $sort
";

$s = '';

if (!($rows = db_loadList( $sql, NULL ))) {
	$s .= $AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg();
} else {
	$s .= '<tr>';
	$s .= '<th><a style="color:white" href="index.php?m=companies&a=view&company_id='.$company_id.'&sort=project_priority">'.$AppUI->_('P').'</a></th>'
                .'<th><a style="color:white" href="index.php?m=companies&a=view&company_id='.$company_id.'&sort=project_name">'.$AppUI->_( 'Name' ).'</a></th>'
		.'<th>'.$AppUI->_( 'Owner' ).'</th>'
		.'<th>'.$AppUI->_( 'Started' ).'</th>'
		.'<th>'.$AppUI->_( 'Status' ).'</th>'
		.'<th>'.$AppUI->_( 'Budget' ).'</th>'
		.'</tr>';
	foreach ($rows as $row) {
		$start_date = new CDate( $row['project_start_date'] );
		$s .= '<tr>';
                $s .= '<td>';
                if ($row['project_priority'] < 0 ) {
                        $s .= "<img src='./images/icons/low.gif' width=13 height=16>";
                } else if ($row["project_priority"] > 0) {
                        $s .= "<img src='./images/icons/" . $row["project_priority"] .".gif' width=13 height=16>";
}

                $s .= '</td>';
		$s .= '<td width="100%">';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a></td>';
		$s .= '<td nowrap="nowrap">'.$row["contact_first_name"].'&nbsp;'.$row["contact_last_name"].'</td>';
		$s .= '<td nowrap="nowrap">'.$start_date->format( $df ).'</td>';
		$s .= '<td nowrap="nowrap">'.$AppUI->_($pstatus[$row["project_status"]]).'</td>';
		$s .= '<td nowrap="nowrap" align="right">'.$dPconfig["currency_symbol"].$row["project_target_budget"].'</td>';
		$s .= '</tr>';
	}
}
echo '<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">' . $s . '</table>';
?>
