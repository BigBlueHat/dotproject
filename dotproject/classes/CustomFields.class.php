<?php
	// $Id$
	/*
	 *	CustomField Classes
	 */

	class CustomField
	{
		var $field_id;
		// TODO - Implement Field Order - some people like to change the order of fields
		var $field_order;
		var $field_name;
		var $field_description;
		var $field_htmltype;
		// TODO - data type, meant for validation if you just want numeric data in a text input
		// but not yet implemented
		var $field_datatype;

		var $field_extratags;

		var $object_id = NULL;

		var $value_id = 0;

		var $value_charvalue;
		var $value_intvalue;

		function CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags )
		{
			$this->field_id = $field_id;
			$this->field_name = $field_name;
			$this->field_order = $field_order;
			$this->field_description = $field_description;
			$this->field_extratags = $field_extratags;
		}

		function load( $object_id )
		{
			// Override Load Method for List type Classes
			GLOBAL $db;
			$sql = "
				SELECT	*
				FROM	custom_fields_values
				WHERE	value_field_id = ".$this->field_id."
				AND	value_object_id = ".$object_id."	
			";	

			$rs = $db->Execute($sql);
			$row = $rs->FetchRow();

			$value_id = $row["value_id"];
			$value_charvalue = stripslashes($row["value_charvalue"]);
			$value_intvalue = $row["value_intvalue"];

			if ($value_id != NULL)
			{
				$this->value_id = $value_id;
				$this->value_charvalue = $value_charvalue;
				$this->value_intvalue = $value_intvalue;
			}
		}

		function store( $object_id )
		{
			GLOBAL $db;
			if ($object_id == NULL)
			{
				return 'Error: Cannot store field ('.$this->field_name.'), associated id not supplied.';
			}
			else
			{ 
				$ins_intvalue = $this->value_intvalue == NULL ? 'NULL' : $this->value_intvalue;

				if ($this->value_id > 0)
				{

						$sql = "UPDATE custom_fields_values SET	
								value_charvalue = '".db_escape(strip_tags( $this->value_charvalue ))."',
								value_intvalue = ".$ins_intvalue."
							WHERE
								value_id = ".$this->value_id."
						";
				}
				else
				{
						$new_value_id = $db->GenID('custom_fields_values_id', 1 );
		
						$sql = "INSERT INTO custom_fields_values (
								value_id,
								value_module,
								value_object_id,
								value_field_id,
								value_intvalue,
								value_charvalue
							)
							VALUES (
								".$new_value_id.",
								'',
								".$object_id.",
								".$this->field_id.",
								".$ins_intvalue.",	
								'".db_escape(strip_tags($this->value_charvalue) )."'	
							)
						";
				}
				if ($sql != NULL) $rs = $db->Execute($sql);
				if (!$rs) return $db->ErrorMsg()." | SQL: ".$sql;
			}
		}

		function setIntValue( $v )
		{	
			$this->value_intvalue = $v;
		}

		function intValue()
		{
			return $this->value_intvalue;
		}

		function setValue( $v )
		{
			$this->value_charvalue = $v;
		}

		function value()
		{
			return $this->value_charvalue;
		}

		function charValue()
		{
			return $this->value_charvalue;
		}

		function setValueId( $v )
		{
			$this->value_id = $v;
		}

		function valueId()
		{
			return $this->value_id;
		}

		function fieldName()
		{
			return $this->field_name;
		}

		function fieldDescription()
		{
			return $this->field_description;
		}
			
		function fieldId()
		{
			return $this->field_id;
		}

		function fieldHtmlType()
		{	
			return $this->field_htmltype;
		}

		function fieldExtraTags()
		{
			return $this->field_extratags;
		}

	}

	// CustomFieldCheckBox - Produces an INPUT Element of the CheckBox type in edit mode, view mode indicates 'Yes' or 'No'
	class CustomFieldCheckBox extends CustomField
	{
		function CustomFieldCheckBox( $field_id, $field_name, $field_order, $field_description, $field_extratags )
		{
			$this->CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags );
			$this->field_htmltype = 'checkbox';
		}

		function getHTML($mode)
		{
			switch($mode)
			{
				case "edit":
					$bool_tag = ($this->intValue()) ? "checked " : "";
					$html = $this->field_description.": </td><td><input type=\"checkbox\" name=\"".$this->field_name."\" value=\"1\" ".$bool_tag.$this->field_extratags."/>";
					break;
				case "view":
					$bool_text = ($this->intValue()) ? "Yes" : "No";
					$html = $this->field_description.": </td><td class=\"hilite\" width=\"100%\">".$bool_text;
					break;
			}
			return $html;
		}

		function setValue( $v )
		{
			$this->value_intvalue = $v;
		}
	}
	
	// CustomFieldText - Produces an INPUT Element of the TEXT type in edit mode
	class CustomFieldText extends CustomField
	{
		function CustomFieldText( $field_id, $field_name, $field_order, $field_description, $field_extratags )
		{
			$this->CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags );
			$this->field_htmltype = 'textinput';
		}

		function getHTML($mode)
		{
			switch($mode)
			{
				case "edit":
					$html = $this->field_description.": </td><td><input type=\"text\" name=\"".$this->field_name."\" value=\"".$this->charValue()."\" ".$this->field_extratags." />";
					break;
				case "view":
					$html = $this->field_description.": </td><td class=\"hilite\" width=\"100%\">".$this->charValue();
					break;
			}
			return $html;
		}
	}

	// CustomFieldTextArea - Produces a TEXTAREA Element in edit mode
	class CustomFieldTextArea extends CustomField
	{
		function CustomFieldTextArea( $field_id, $field_name, $field_order, $field_description, $field_extratags )
		{
			$this->CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags );
			$this->field_htmltype = 'textarea';
		}

		function getHTML($mode)
		{
			switch($mode)
			{
				case "edit":
					$html = $this->field_description.": </td><td><textarea name=\"".$this->field_name."\" ".$this->field_extratags." />".$this->charValue()."</textarea>";
					break;
				case "view":
					$html = $this->field_description.": </td><td class=\"hilite\" width=\"100%\">".nl2br($this->charValue());
					break;
			}
			return $html;
		}
	}

	// CustomFieldSelect - Produces a SELECT list, extends the load method so that the option list can be loaded from a seperate table
	class CustomFieldSelect extends CustomField
	{
		var $options;

		function CustomFieldSelect( $field_id, $field_name, $field_order, $field_description, $field_extratags )
		{
			$this->CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags );
			$this->field_htmltype = 'select';
			$this->options = New CustomOptionList( $field_id );		
			$this->options->load();
		}

		function getHTML($mode)
		{
			switch($mode)
			{
				case "edit":
					$html = $this->field_description.": </td><td>";
					$html.= $this->options->getHTML( $this->field_name, $this->intValue() );
					break;
				case "view":
					$html = $this->field_description.": </td><td class=\"hilite\" width=\"100%\">".$this->options->itemAtIndex($this->intValue());
					break;
			}
			return $html;
		}

		function setValue( $v )
		{
			$this->value_intvalue = $v;
		}

		function value()
		{
			return $this->value_intvalue;
		}
	}


	// CustomFields class - loads all custom fields related to a module, produces a html table of all custom fields
	// Also loads values automatically if the obj_id parameter is supplied. The obj_id parameter is the ID of the module object 
	// eg. company_id for companies module
	class CustomFields
	{
		var $m;
		var $a;
		var $mode;
		var $obj_id;		

		var $fields;

		function CustomFields($m, $a, $obj_id = NULL, $mode = "edit")
		{
			$this->m = $m;
			$this->a = 'addedit'; // only addedit pages can carry the custom field for now
			$this->obj_id = $obj_id;
			$this->mode = $mode;

			// Get Custom Fields for this Module
			$sql = "
				SELECT  *
				FROM	custom_fields_struct
				WHERE	field_module = '".$this->m."'
				AND	field_page = '".$this->a."'
				ORDER BY
					field_order ASC
			";
		
			//echo "<pre>$sql</pre>";
			
			$rows = db_loadList($sql);						
			if ($rows == NULL)
			{
				// No Custom Fields Available
			}
			else
			{
				foreach($rows as $row)
				{
					switch ($row["field_htmltype"])
					{
						case "checkbox":
							$this->fields[$row["field_name"]] = New CustomFieldCheckbox( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "textarea":
							$this->fields[$row["field_name"]] = New CustomFieldTextArea( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "select":
							$this->fields[$row["field_name"]] = New CustomFieldSelect( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						default:
							$this->fields[$row["field_name"]] = New CustomFieldText( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break; 
					}
				
				}
	
				if ($obj_id > 0)
				{
					//Load Values
					foreach ($this->fields as $key => $cfield)
					{
						$this->fields[$key]->load( $this->obj_id );
					}
				}
			}
		

		}

		function add( $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, &$error_msg )
		{
			GLOBAL $db;
			$next_id = $db->GenID( 'custom_fields_struct_id', 1 );

			$field_order = 1;
			$field_a = 'addedit';

			$sql = "
				INSERT
				INTO	custom_fields_struct (
						field_id,
						field_module,
						field_page,
						field_htmltype,
						field_datatype,
						field_order,
						field_name,
						field_description,
						field_extratags
				)
				VALUES
					(".$next_id.", '".$this->m."', '".$field_a."', '".$field_htmltype."', '".$field_datatype."', ".$field_order.", '".$field_name."', '".$field_description."', '".$field_extratags."');"; 

			// TODO - module pages other than addedit
			// TODO - validation that field_name doesnt already exist

			//echo $sql;

			if (!$db->Execute($sql))
			{
				//return "<pre>".$sql."</pre>";
				$error_msg = $db->ErrorMsg();
				return 0;
			}
			else
			{
				return $next_id;
			}
		} 

		function update( $field_id, $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, &$error_msg )
		{
			GLOBAL $db;
			
			$sql = "
				UPDATE custom_fields_struct
				SET
					field_name = '".$field_name."',
					field_description = '".$field_description."',
					field_htmltype = '".$field_htmltype."',		
					field_datatype = '".$field_datatype."',
					field_extratags = '".$field_extratags."'
				WHERE
					field_id = ".$field_id."
			";
	
			if (!$db->Execute($sql))
			{
				$error_msg = $db->ErrorMsg();
				return 0;
			}
			else
			{
				return $field_id;
			}
		}

		function fieldWithId( $field_id )
		{
			foreach ($this->fields as $k => $v)
			{
				if ($this->fields[$k]->field_id == $field_id) 
					return $this->fields[$k];	
			}
		}

		function bind( &$formvars )
		{
			if (!count($this->fields) == 0)
			{
				foreach ($this->fields as $k => $v)
				{
					if ($formvars[$k] != NULL)
					{
						$this->fields[$k]->setValue($formvars[$k]);
					}
				}
			}
		}

		function store( $object_id )
		{
			if (!count($this->fields) == 0)
			{
				foreach ($this->fields as $k => $cf)
				{
					$result = $this->fields[$k]->store( $object_id );
					if ($result)
					{
						$store_errors .= "Error storing custom field ".$k.":".$result;
					}
				}

				//if ($store_errors) return $store_errors;
				if ($store_errors) echo $store_errors;
			}
		}

		function deleteField( $field_id )
		{
			GLOBAL $db;
			$sql = "
				DELETE FROM custom_fields_struct
				WHERE field_id = $field_id 	
			";
			if (!$db->Execute($sql))
			{
				return $db->ErrorMsg();
			}	
		}

		function count()
		{
			return count($this->fields);
		}

		function getHTML()
		{
			if ($this->count() == 0)
			{
				return "";
			}
			else
			{
				$html = "<table width=\"100%\">\n";
	
				foreach ($this->fields as $cfield)
				{
					$html .= "\t<tr><td nowrap=\"nowrap\">".$cfield->getHTML($this->mode)."</td></tr>\n";
				}
				$html .= "</table>\n";

				return $html;
			}
		}


		function printHTML()
		{
			$html = $this->getHTML();
			echo $html;
		}
		
	}

	class CustomOptionList
	{
		var $field_id;
		var $options;

		function CustomOptionList( $field_id )
		{
			$this->field_id = $field_id;
		}

		function load()
		{
			GLOBAL $db;
	
			$sql = "
				SELECT	*
				FROM	custom_fields_lists
				WHERE	field_id = ".$this->field_id;
					
			if (!$rs = $db->Execute($sql)) return $db->ErrorMsg();		

			$this->options = Array();

			while ($opt_row = $rs->FetchRow())
			{
				$this->options[] = $opt_row["list_value"];
			}
		}

		function store()
		{
			GLOBAL $db;

			foreach($this->options as $opt)
			{
				$optid = $db->GenID('custom_fields_option_id', 1 );

				$sql = "INSERT INTO custom_fields_lists ( field_id, list_option_id, list_value )
				VALUES ( ".$this->field_id.", ".$optid.", '".db_escape(strip_tags($opt))."')";

				if (!$db->Execute($sql)) $insert_error = $db->ErrorMsg();  	
			}	

			return $insert_error;
		}

		function delete()
		{
			GLOBAL $db;
			$sql = "DELETE FROM custom_fields_lists WHERE field_id = ".$this->field_id;
			$db->Execute($sql);
		}

		function setOptions( $option_array )
		{
			$this->options = $option_array;
		} 

		function getOptions()
		{
			return $this->options;
		}

		function itemAtIndex( $i )
		{
			return $this->options[$i];
		}

		function getHTML( $field_name, $selected )
		{
			$html = "<select name=\"".$field_name."\">\n";
			foreach ($this->options as $i => $opt)
			{
				$html .= "\t<option value=\"".$i."\"";
				if ($i == $selected) $html .= " selected ";
				$html .= ">".$opt."</option>";
			}	
			$html .= "</select>\n";
			return $html;
		}		
	}
?>
