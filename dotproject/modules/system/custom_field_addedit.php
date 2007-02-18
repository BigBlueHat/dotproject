<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}


	/*
	 *	Custom Field Add/Edit
	 *
	 */
if (!$canEdit) {
    $AppUI->redirect( "m=public&a=access_denied" );
}
	require_once(DP_BASE_DIR . '/classes/CustomFields.class.php');
		
	$titleBlock = new CTitleBlock('Custom Fields - Add/Edit', "", "admin", "admin.custom_field_addedit");
	$titleBlock->addCrumb( "?m=system", 'system admin' );
	$titleBlock->addCrumb( "?m=system&amp;a=custom_field_editor", 'custom fields' );
	if ($canDelete)
	  $titleBlock->addCrumbDelete( 'delete custom field', $canDelete, $msg );
	$titleBlock->show();

	$field_id = dpGetParam( $_POST, "field_id", NULL ) != NULL ? dpGetParam( $_POST, "field_id", NULL) : dpGetParam( $_GET, "field_id", 0);
	$delete_field = dpGetParam( $_GET, "delete", 0 );
	$module = dpGetParam($_GET, "module", NULL ) == NULL ? dpGetParam($_POST, "module", NULL) : dpGetParam($_GET, "module", NULL);

	$select_newitem = dpGetParam($_POST, "select_newitem", NULL);
	$select_items = dpGetParam($_POST, "select_items", Array());

	$select_delitem = dpGetParam($_POST, "delete_item", NULL);

	if ($select_newitem != NULL)
	{
		$select_items[] = $select_newitem;
	}

	if ($select_delitem != NULL)
	{
		$new_selectitems = Array();

		foreach($select_items as $itm)
		{
			if ($itm != $select_delitem) $new_selectitems[] = $itm;			
		}
	
		unset($select_items);
		$select_items = &$new_selectitems;
	}

	// Loading the page for the first time
	if (dpGetParam($_GET, "field_id", NULL) != NULL)
	{	
		$custom_fields = New CustomFields($module, 'addedit', NULL, 'edit');

		if ($delete_field)
		{
			$custom_fields->deleteField( $field_id );
			$AppUI->redirect();
		}

		$cf =& $custom_fields->fieldWithId( $field_id );

		if (is_object($cf))
		{
			$field_name = $cf->fieldName();
			$field_description = $cf->fieldDescription();
			$field_htmltype = $cf->fieldHtmlType();
			$field_extratags = $cf->fieldExtraTags();
			

			if ($field_htmltype == "select")
			{
				$select_options = New CustomOptionList( $field_id );
				$select_options->load();
				$select_items = $select_options->getOptions();
			}

			if ($field_htmltype == "sqlselect")
			{
				$select_options = New SQLCustomOptionList( $field_id );
				$select_options->load();
				$select_query = $select_options->getQuery();
			}
		}
		else
		{
			//No such field exists with this ID
			$AppUI->setMsg('Couldnt load the Custom Field, It might have been deleted somehow.'); 
			$AppUI->redirect();
		}

		$edit_title = $AppUI->_("Edit Custom Field In"); 
	}
	else
	{
		$edit_title = $AppUI->_("New Custom Field In");

		$field_name = dpGetParam( $_POST, "field_name", NULL );
		$field_description = dpGetParam( $_POST, "field_description", NULL );
		$field_htmltype = dpGetParam( $_POST, "field_htmltype", "textinput");
		$field_extratags = dpGetParam( $_POST, "field_extratags", NULL );
	}

	$html_types = Array(
		'textinput'=>$AppUI->_('Text Input'),
		'textarea'=>$AppUI->_('Text Area'),
		'checkbox'=>$AppUI->_('Checkbox'),
		'select'=>$AppUI->_('Select List'),
		'sqlselect'=>$AppUI->_('SQL Query Select List'),
		'label' => $AppUI->_('Label'),
		'separator' => $AppUI->_('Separator'),
		'href'=>$AppUI->_('Weblink')
	);

	$visible_state = Array();

	foreach ($html_types as $k => $ht)
	{
		if ($k == $field_htmltype)
		{
			$visible_state["div_".$k] = "display : block";
		}	
		else
		{
			$visible_state["div_".$k] = "display : none";
		}
	}

	include(DP_BASE_DIR . '/modules/system/custom_field_addedit.js'); 

	$tpl->assign('module', $module); 
	$tpl->assign('field_id', $field_id); 
	$tpl->assign('field_name', $field_name); 
	$tpl->assign('field_description', $field_description); 
	$tpl->assign('field_extratags', $field_extratags); 
	$tpl->assign('select_items', $select_items);
	$tpl->assign('html_types', $html_types);
	$tpl->assign('field_htmltype', $field_htmltype);
	$tpl->assign('select_query', $select_query);


	$tpl->assign('visible_state', $visible_state);

	$tpl->displayFile('custom_field_addedit');
?>

<?php if ($canDelete) { ?>
<script type="text/javascript">
<!--
function delIt() {
  if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Field', UI_OUTPUT_JS).'?';?>" )) {
		form = document.getElementById('custform');
		form.del.value = '1';		
		postCustomField();
//    form.submit();
  }
}
-->
</script>
<?php } ?>

