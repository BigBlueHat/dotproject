<?php /* COMPANIES $Id$ */
##
##	Companies: View User sub-table
##
GLOBAL $AppUI, $company_id, $obj;

$sql = "
SELECT contact_id, contact_first_name, contact_last_name, contact_email, contact_department
FROM contacts
WHERE contact_company = '$obj->company_name'
";

$s = '';
if (!($rows = db_loadList( $sql, NULL ))) {
	echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();
} else {
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_( 'Name' );?></td>
	<th><?php echo $AppUI->_( 'e-mail' );?></td>
	<th><?php echo $AppUI->_( 'Department' );?></td>
</tr>
<?php
	foreach ($rows as $row){
		$s .= '<tr><td>';
		$s .= '<a href="./index.php?m=contacts&a=addedit&contact_id='.$row["contact_id"].'">'.$row["contact_first_name"] . " " . $row["contact_last_name"] .'</a>';
		$s .= '<td><a href="mailto:'.$row["contact_email"] .'">' .$row["contact_email"] .'</a></td>';
		$s .= '<td>'.$row["contact_department"] .'</td>';
		$s .= '</tr>';
	}
}

	$s .= '<tr><td colspan="3" align="right" valign="top" style="background-color:#ffffff">';
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new contact' ).'" onClick="javascript:window.location=\'./index.php?m=contacts&a=addedit\'">';
	$s .= '</td></tr>';
	echo $s;
	
?>
</table>