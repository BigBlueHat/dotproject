<?php
	$company_id           = dPgetParam($_GET, "company_id", 0);
	$contact_id           = dPgetParam($_POST, "contact_id", 0);
	$call_back            = dPgetParam($_GET, "call_back", null);
	$contacts_submited    = dPgetParam($_POST, "contacts_submited", 0);
	$selected_contacts_id = dPgetParam($_GET, "selected_contacts_id", "");

	if($contacts_submited == 1){
		$contacts_id = "";
		if(is_array($contact_id)){
			$contacts_id = implode(",", $contact_id);
		}
		$call_back_string = !is_null($call_back) ? "window.opener.$call_back('$contacts_id');" : "";
		?>
			<script language="javascript">
				<?= $call_back_string ?>
				self.close();
			</script>
		<?php
	}
	
	$contacts_id = explode(",", $selected_contacts_id);
	
	$sql = "select c.company_name
	        from companies as c
	        where company_id = $company_id";
	$company_name = db_loadResult($sql);
	
	$sql = "select contact_id, contact_first_name, contact_last_name, contact_department
	        from contacts
	        where contact_company = '$company_name'
	              and (contact_owner='$AppUI->user_id' or contact_private='0')
	        group by contact_department, contact_first_name";

	$contacts = db_loadHashList($sql, "contact_id");
?>

<h2>Contacts for <?= $company_name ?></h2>

<form action='index.php?m=public&a=contact_selector&dialog=1&<?php if(!is_null($call_back)) echo "call_back=$call_back&"; ?>company_id=<?= $company_id ?>' method='post'>
<?php
	$actual_department = "";

	foreach($contacts as $contact_id => $contact_data){
		if($contact_data["contact_department"] != $actual_department){
			echo "<h5>".$contact_data["contact_department"]."</h5>";
			$actual_department = $contact_data["contact_department"];
		}
		$checked = in_array($contact_id, $contacts_id) ? "checked" : "";
		echo "<input type='checkbox' name='contact_id[]' value='$contact_id' $checked />";
		echo $contact_data["contact_first_name"]." ".$contact_data["contact_last_name"];
		echo "<br />";
	}
?>
<hr />
<input name='contacts_submited' type='hidden' value='1' />
<input type='submit' value='<?= $AppUI->_("Continue"); ?>' class='button' />
</form>