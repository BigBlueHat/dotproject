<?php
	global $AppUI, $task_id, $obj, $users, $task_access, $department_selection_list;
	global $task_parent_options, $dPconfig, $projects, $task_project, $can_edit_time_information, $tab;

	$perms =& $AppUI->acl();
?>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>"
  method="post"  name="detailFrm">
<input type="hidden" name="dosql" value="do_task_aed" />
<input type="hidden" name="sub_form" value="1" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>"
<table class="std" width="100%" border="1" cellpadding="4" cellspacing="0">
<tr>
	<td width="50%" valign='top'>
	    <table border="0">
	    	<tr>
	    		<td>
				    			<?php
				    				if($can_edit_time_information){
				    					?>
								<?php echo $AppUI->_( 'Task Creator' );?>
								<br />
							<?php echo arraySelect( $users, 'task_owner', 'class="text"', !isset($obj->task_owner) ? $AppUI->user_id : $obj->task_owner );?>
								<br />
									<?php
				    				} // $can_edit_time_information
								?>
								<?php echo $AppUI->_( 'Access' );?>
								<br />
								<?php echo arraySelect( $task_access, 'task_access', 'class="text"', intval( $obj->task_access ), true );?>
								<br /><?php echo $AppUI->_( 'Web Address' );?>
								<br /><input type="text" class="text" name="task_related_url" value="<?php echo @$obj->task_related_url;?>" size="40" maxlength="255" />

							</td>
							<td valign='top'>
								<?php echo $AppUI->_("Task Type"); ?>
								<br />
								<?php echo arraySelect(dPgetSysVal("TaskType"), "task_type",  "class='text' onchange='javascript:changeRecordType(this.value);'", $obj->task_type, false); ?>
								<br /><br />
					<?php
						if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
							echo "<input type='button' class='button' value='".$AppUI->_("Select contacts...")."' onclick='javascript:popContacts();' />";
						}
						// Let's check if the actual company has departments registered
						if($department_selection_list != ""){
							?>
								<br />
								<?php echo $AppUI->_("Departments"); ?><br />
								<?php echo $department_selection_list; ?>
							<?php
						}
						
					?>
				</td>
			</tr>
		<tr>
			<td><?php echo $AppUI->_( 'Task Parent' );?>:</td>
			<td><?php echo $AppUI->_( 'Target Budget' );?>:</td>
		</tr>
		<tr>
			<td>
				<select name='task_parent' class='text' onchange="javascript:setTasksStartDate()">
					<option value='<?php echo $obj->task_id; ?>'><?php echo $AppUI->_('None'); ?></option>
					<?php echo $task_parent_options; ?>
				</select>
			</td>
			<td><?php echo $dPconfig['currency_symbol'] ?><input type="text" class="text" name="task_target_budget" value="<?php echo @$obj->task_target_budget;?>" size="10" maxlength="10" /></td>
		</tr>
	<?php if ($task_id > 0){ ?>
		<tr>
			<td>
				<?php echo $AppUI->_( 'Move this task (and its children), to project' );?>:
			</td>
		</tr>
		<tr>
			<td>
				<?php echo arraySelect( $projects, 'new_task_project', 'size="1" class="text" id="medium" onchange="submitIt(document.editFrm)"',$task_project ); ?>
			</td>
		</tr>
	<?php } ?>
		</table>
	</td>
	<td valign="top" align="center">
		<table><tr><td align="left">
		<?php echo $AppUI->_( 'Description' );?>:
		<br />
		<textarea name="task_description" class="textarea" cols="60" rows="10" wrap="virtual"><?php echo @$obj->task_description;?></textarea>
		</td></tr></table><br />
		<?php
			require_once("./classes/customfieldsparser.class.php");
			// let's create the parser
			$cfp = new CustomFieldsParser("TaskCustomFields", $obj->task_id);
			
			// we will need the amount of record types
			$amount_task_record_types = count($cfp->custom_record_types);
		?>
		
		<?php
			// let's parse the custom fields form table
			echo $cfp->parseTableForm(true);
		?>
		
		<script language="javascript">
		    var task_types;
		    
		    // We need to create an array of all the names
		    // of the record types in JS so we can map the Key to the type name (used in the field filter)
		    task_types = new Array(<?php echo $amount_task_record_types; ?>);
		    
		    <?php
		    	foreach($cfp->custom_record_types as $key => $record_type){
		    		echo "task_types[$key] = new String('".$record_type."');\n";
		    	}
		    	reset($cfp->custom_record_types);
		    	if(count($cfp->custom_record_types) == 0){
		    		$record_type = "";
		    	} else {
		    		$record_type = isset($cfp->custom_record_types[$obj->task_type]) ? $cfp->custom_record_types[$obj->task_type] : null;
		    		if(is_null($record_type)){
		    			$record_type = current($cfp->custom_record_types);
		    		}
		    	}
		    	
		    	$actual_record_type = str_replace(" ", "_", $record_type);
		    	
		    	// Let's parse all the show functions
		    	echo $cfp->parseShowFunctions();
		    ?>
		    
		    // hideAll Function
			<?php echo $cfp->showHideAllRowsFunction(); ?>
			
			// by default hide everything and show the actual type record
			<?php echo "\n\nhideAllRows();";
			      if($actual_record_type != ""){
				      echo "show$actual_record_type();";
			      } 
			?>
		</script>
	</td>
</tr>
</table>
</form>
<script language="javascript">
 subForm.push(new FormDefinition(<?php echo $tab;?>, document.detailFrm, checkDetail, saveDetail));
</script>
