<?php
	global $dept_id;
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<?php
	echo "<tr><th>".$AppUI->_("Name")."</th><th>".$AppUI->_("Email")."</th><th>".$AppUI->_("Telephone")."</th></tr>";
	
	$q  = new DBQuery;
	$q->addTable('departments', 'dep');
	$q->addQuery('dep.dept_name');
	$q->addWhere('dep.dept_id = '.$dept_id);
	$sql = $q->prepare();
	$q->clear();
	$contact_department = db_loadResult($sql);
	
	$q  = new DBQuery;
	$q->addTable('contacts', 'con');
	$q->addQuery('contact_id, con.contact_first_name');
	$q->addQuery('con.contact_last_name, contact_email, contact_phone');
	$q->addWhere("contact_department='$contact_department'");
	$q->addWhere("(contact_owner = '$AppUI->user_id' or contact_private = '0')");
	$q->addOrder('contact_first_name');
	$contacts = $q->loadHashList("contact_id");
	
	foreach($contacts as $contact_id => $contact_data){
		echo "<tr><td>".$contact_data["contact_first_name"]." ".$contact_data["contact_last_name"]."</td>";
		echo "<td>".$contact_data["contact_email"]."</td>";
		echo "<td>".$contact_data["contact_phone"]."</td></tr>";
	}
?>
</table>
