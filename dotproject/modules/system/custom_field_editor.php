<?php

	function showFieldTypeOptions($default_type = ""){
		$field_types = array("text","select","textarea", "checkbox","label");
		$parsed = "";
		
		foreach($field_types as $field_type){
			$sel     = $field_type == $default_type ? "selected" : "";
			$parsed .= "<option value='$field_type' $sel>$field_type</option>";
		}
		return $parsed;
	}
	
	function showRecordTypes($default_type = ""){
		global $record_types;
		
		$parsed = "";
		foreach($record_types as $record_type){
			$sel     = $record_type == $default_type ? "selected" : "";
			$parsed .= "<option value='$record_type' $sel>$record_type</option>";
		}
		return $parsed;
	}

	$custom_field_types = array("CompanyCustomFields", "TaskCustomFields");

	$current_type = dPgetParam($_REQUEST, "custom_field_type", "");
	
	$record_types = array("");
	switch($current_type){
		case "TaskCustomFields":
			$record_types += dPgetSysVal("TaskType");
			break;
		case "CompanyCustomFields":
			$record_types += dPgetSysVal("CompanyType");
			break;
	}
	
	// Let's check if we need to save something
	if(isset($_POST["field_name"])){
		$custom_fields = "";
		foreach($_POST["field_name"] as $key => $value){
			if($value != "" && $_POST["key_to_delete"] != $key){ // let's ignore empty names
				$custom_fields .= "$key|";
				
				$config_array   = array("record_type" => $_POST["field_record_type"][$key],
					                      "name"      => $_POST["field_name"][$key],
					                      "type"      => $_POST["field_type"][$key],
					                      "options"   => stripslashes($_POST["field_options"][$key]),
					                      "selects"   => $_POST["field_selects"][$key]);
					                      
				$config_array["options"] = str_replace("'", "\'", $config_array["options"]);
				$config_array["selects"] = str_replace("'", "\'", $config_array["selects"]);
				
				$custom_fields .= serialize($config_array)."\n";
			}
		}
		
		$sysval_id = db_loadResult("select sysval_id from sysvals where sysval_title = '$current_type'");
		if(!$sysval_id){
			$sql = "insert into sysvals (sysval_key_id, sysval_title, sysval_value) values ('2','$current_type','$custom_fields')";
		} else {
			$sql = "update sysvals set sysval_key_id = '2', sysval_title = '$current_type', sysval_value='$custom_fields' where sysval_id = '$sysval_id'";
		}
		db_exec($sql);
		$AppUI->setMsg($AppUI->_("Fields updated"));
	}

	$titleBlock = new CTitleBlock("Custom field editor", "", "admin", "admin.custom_field_editor");
	$titleBlock->addCrumb( "?m=system", "system admin" );
	if($current_type != "") $titleBlock->addCrumb("?m=system&a=custom_field_editor", "record types");
	$titleBlock->show();
	
	if($current_type == ""){ // no type selected
		?>
			<ul>
				<?php
					foreach($custom_field_types as $custom_field_type){
						echo "<li><a href='index.php?m=system&a=custom_field_editor&custom_field_type=$custom_field_type'>$custom_field_type</a></li>";
					}
				?>
			</ul>
		<?php
	} else {
		$current_configuration = dPgetSysVal($current_type);
		if(!is_array($current_configuration)) $current_configuration = array();
		?>
			<script language='javascript'>
				<?php
				// security improvement:
				// some javascript functions may not appear on client side in case of user not having write permissions
				// else users would be able to arbitrarily run 'bad' functions
				if ($canEdit) {
				?>
				function deleteField(key){
					document.frmEdit.key_to_delete.value = key;
					document.frmEdit.submit();
				}
				<?php } ?>
			</script>
			<h3><?php echo $current_type; ?></h3>
			<form action='index.php?m=system&a=custom_field_editor&custom_field_type=<?php echo $current_type; ?>' method='post' name='frmEdit'>
				<table>
					<tr>
						<td><?php echo $AppUI->_("Record Type"); ?></td>
						<td><?php echo $AppUI->_("Field name"); ?></td>
						<td><?php echo $AppUI->_("Type"); ?> </td>
					</tr>
					<?php
						// Let's insert a blank one to enable the creation of a new one
					$fields_ids = array_keys( $current_configuration);
					asort($fields_ids);
					$max_id = array_pop( $fields_ids )+1;
 					$current_configuration += array($max_id => serialize(array("record_type" => "", "name" => "", "type" => "", "options" => "")));

 					foreach($current_configuration as $key => $field_configuration){
								// fields with ' or " are not unserializing correctly!!!
//							echo $field_configuration;
 							$field_configuration = unserialize($field_configuration);
//							echo "<pre>"; print_r($field_configuration); echo "</pre>";
							echo "<tr>";
							echo "<td><select name='field_record_type[$key]'>".showRecordTypes($field_configuration["record_type"])."</select></td>";
							echo "<td><input type='text' size='25'  name='field_name[$key]' value='".$field_configuration["name"]."' /></td>";
							echo "<td><select name='field_type[$key]'>".showFieldTypeOptions($field_configuration["type"])."</select></td>";
							echo "</tr>";
							echo "<tr><td>".$AppUI->_("Options")."</td><td colspan='2'><input type='text' size='50' name='field_options[$key]' value='".$field_configuration["options"]."' /></td>";
							echo "<tr><td>".$AppUI->_("Selects")."</td><td colspan='2'><input type='text' size='50' name='field_selects[$key]' value='".$field_configuration["selects"]."' /></td>";
							echo "</tr>";
							$new_text    = $field_configuration["name"] == "" ? $AppUI->_("New") : "";
							$delete_text = $field_configuration["name"] != "" ? "<a href='javascript:void(deleteField($key));'>".$AppUI->_("Delete")."</a>" : "";
							echo "<tr><td><b>$new_text</b> <input type='submit' value='".$AppUI->_("Save")."' /></td><td></td><td>$delete_text</td></tr>";
						}
					?>
				</table>
				<input type='hidden' name='key_to_delete' value='' />
			</form>
		<?php
	}
?>
