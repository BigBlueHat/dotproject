<?php /* COMPANIES $Id$ */
##
##	Companies: View User sub-table
##
GLOBAL $AppUI, $company_id;

$sql = "
SELECT user_id, user_username, contact_first_name, contact_last_name
FROM users
LEFT JOIN contacts ON user_contact = contact_id
WHERE user_company = $company_id
";

if (!($rows = db_loadList( $sql, NULL ))) {
	echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();
} else {
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_( 'Username' );?></td>
	<th><?php echo $AppUI->_( 'Name' );?></td>
</tr>
<?php
$s = '';
foreach ($rows as $row){
	$s .= '<tr><td>';
	$s .= '<a href="./index.php?m=admin&a=viewuser&user_id='.$row["user_id"].'">'.$row["user_username"].'</a>';
	$s .= '<td>'.$row["contact_first_name"].'&nbsp;'.$row["contact_last_name"].'</td>';
	$s .= '</tr>';
}
echo $s;
?>
</table>
<?php } ?>
