<?php
	global $dept_id;
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<?php
	echo "<tr><th>".$AppUI->_("Name")."</th><th>".$AppUI->_("Email")."</th><th>".$AppUI->_("Telephone")."</th></tr>";
	
	$contact_department = db_loadResult("select dept_name
	                                     from departments
	                                     where dept_id='$dept_id'");
	
	$sql = "select contact_id, contact_first_name, contact_last_name, contact_email, contact_phone
	        from contacts
	        where contact_department='$contact_department'
	              and (contact_owner = '$AppUI->user_id' or contact_private = '0')
	        order by contact_first_name";
	$contacts = db_loadHashList($sql, "contact_id");
	
	foreach($contacts as $contact_id => $contact_data){
		echo "<tr><td>".$contact_data["contact_first_name"]." ".$contact_data["contact_last_name"]."</td>";
		echo "<td>".$contact_data["contact_email"]."</td>";
		echo "<td>".$contact_data["contact_phone"]."</td></tr>";
	}
?>
</table>