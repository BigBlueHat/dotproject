<?php /* COMPANIES $Id$ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $canEdit;

$sql = "
SELECT departments.*, COUNT(user_department) dept_users
FROM departments
LEFT JOIN users ON user_department = dept_id
WHERE dept_company = $company_id
GROUP BY dept_id
ORDER BY dept_parent
";
##echo $sql;

function showchild( &$a, $level=0 ) {
	$s = '';

	$s .= '<td>';
	$s .= '<a href="./index.php?m=departments&a=addedit&dept_id='.$a["dept_id"].'">';
	$s .= '<img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>';
	$s .= '</td>';
	$s .= '<td>';

	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12" border="0">';
		}
	}

	$s .= '<a href="./index.php?m=departments&a=view&dept_id='.$a["dept_id"].'">'.$a["dept_name"].'</a>';
	$s .= '</td>';
	$s .= '<td align="center">'.($a["dept_users"] ? $a["dept_users"] : '').'</td>';

	echo "<tr>$s</tr>";
}

function findchild( &$tarr, $parent, $level=0 ){
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["dept_parent"] == $parent && $tarr[$x]["dept_parent"] != $tarr[$x]["dept_id"]){
			showchild( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["dept_id"], $level);
		}
	}
}


$s = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">';
$s .= '<tr>';
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
	$s .= '<th>&nbsp;</th>';
	$s .= '<th width="100%">'.$AppUI->_( 'Name' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Users' ).'</th>';
} else {
	$s .= $AppUI->_('No data available');
}
$s .= '<td nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new department' ).'" onClick="javascript:window.location=\'./index.php?m=departments&a=addedit&company_id='.$company_id.'\';">';
}
$s .= '</td>';
$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	if ($row["dept_parent"] == 0) {
		showchild( $row );
		findchild( $rows, $row["dept_id"] );
	}
}
echo '</table>';
?>