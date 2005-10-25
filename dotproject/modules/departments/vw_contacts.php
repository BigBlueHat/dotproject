<?php
	global $dept_id, $tpl;
	
	$q  = new DBQuery;
	$q->addTable('contacts', 'con');
	$q->addQuery('contact_id, con.contact_first_name');
	$q->addQuery('con.contact_last_name, contact_email, contact_phone');
	$q->addWhere("contact_department='$dept_id'");
	$q->addWhere("(contact_owner = '$AppUI->user_id' or contact_private = '0')");
	$q->addOrder('contact_first_name');
	$contacts = $q->loadHashList("contact_id");

	$tpl->assign('contacts', $contacts);
	$tpl->displayFile('vw_contacts'); 
?>
