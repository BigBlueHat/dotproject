<?php // $Id$
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}
	/** Custom field base object 
	 * 
	 * Provides base functionality and properties for custom field objects 
	 */
	class CustomField
	{
		var $field_id;
		var $field_order;
		var $field_name;
		var $field_description;
		var $field_htmltype;
		// TODO - data type, meant for validation if you just want numeric data in a text input
		// but not yet implemented
		var $field_datatype;
		var $field_extratags;

		var $object_id = null;

		var $value_id = 0;
		var $value_charvalue;
		var $value_intvalue;

		/**
		 * @abstract CustomField constructor
		 * 
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->field_id = $field_id;
			$this->field_name = $field_name;
			$this->field_order = $field_order;
			$this->field_description = $field_description;
			$this->field_extratags = $field_extratags;
		}

		/**
		 * @abstract Loads data for the requested $object_id into this object.
		 * 
		 * @param int $object_id
		 * 
		 * @global ADOConnection $db
		 * 
		 * @return void
		 */
		function load($object_id)
		{
			// Override Load Method for List type Classes
			global $db;
			$q  = new DBQuery();
			$q->addTable('custom_fields_values');
			$q->addWhere("value_field_id = ".$this->field_id);
			$q->addWhere("value_object_id = ".$object_id);
			$rs = $q->exec();
			$row = $q->fetchRow();
			$q->clear();

			$value_id = $row["value_id"];
			$value_charvalue = $row["value_charvalue"];
			$value_intvalue = $row["value_intvalue"];

			if ($value_id != NULL) {
				$this->value_id = $value_id;
				$this->value_charvalue = $value_charvalue;
				$this->value_intvalue = $value_intvalue;
			}
		}

		/**
		 * @abstract Store the instance variables back into the database for $object_id. 
		 * @param int $object_id The ID of the dotproject object
		 * @return Database error string on failure, otherwise NULL.
		 */  
		function store($object_id)
		{
			global $db;
			if ($object_id == NULL) {
				return 'Error: Cannot store field ('.$this->field_name.'), associated id not supplied.';
			} else {				 
				$ins_intvalue = $this->value_intvalue == NULL ? 'NULL' : $this->value_intvalue;
				$ins_charvalue = $this->value_charvalue == NULL ? '' : stripslashes($this->value_charvalue);

				if ($this->value_id > 0) {
					//return $this->value();
					$q  = new DBQuery();
					$q->addTable('custom_fields_values');
					$q->addUpdate('value_charvalue', $ins_charvalue );
					$q->addUpdate('value_intvalue', $ins_intvalue);
					$q->addWhere("value_id = ".$this->value_id);
				} else {
					$new_value_id = $db->GenID('custom_fields_values_id', 1 );
		
					$q  = new DBQuery();
					$q->addTable('custom_fields_values');
					$q->addInsert('value_id', $new_value_id);
					$q->addInsert('value_module', '');
					$q->addInsert('value_field_id', $this->field_id);
					$q->addInsert('value_object_id', $object_id);

					$q->addInsert('value_charvalue', $ins_charvalue );
					$q->addInsert('value_intvalue', $ins_intvalue);
				}

               			$rs = $q->exec();
				$q->clear();

				if (!$rs) {
					return $db->ErrorMsg()." | SQL: ";
				}
			}
		}

		/**
		 * @abstract Set the value of this custom field to an integer.
		 * @param int $v The integer value to set this custom field to
		 * @return void
		 */
		function setIntValue($v)
		{	
			$this->value_intvalue = $v;
		}

		/**
		 * @abstract Access the integer value of this custom field
		 * @return Integer value
		 */
		function intValue()
		{
			return $this->value_intvalue;
		}

		/**
		 * @abstract Set the string value of this custom field
		 * @param string $v The string value to set this custom field to
		 * @return void
		 */ 
		function setValue($v)
		{
			$this->value_charvalue = $v;
		}

		/**
		 * @abstract Access the string value of this custom field
		 * @return String value
		 */
		function value()
		{
			return $this->value_charvalue;
		}

		/**
		 * @abstract Access the string value of this custom field, same implementation as value()
		 * @return String value
		 */
		function charValue()
		{
			return $this->value_charvalue;
		}

		/**
		 * @abstract Set the value id. This corresponds to the row in custom_fields_values that stores the custom field values and the corresponding dotproject object id
		 * @param int The value id
		 */
		function setValueId($v)
		{
			$this->value_id = $v;
		}

		/**
		 * @abstract Access the value id
		 * @return int value id of this custom field (if the custom field has been loaded with values via the load() method)
		 */ 
		function valueId()
		{
			return $this->value_id;
		}

		/**
		 * @abstract Access the custom field HTML name
		 * @return string HTML name of the custom field.
		 */
		function fieldName()
		{
			return $this->field_name;
		}

		/**
		 * @abstract Access the custom field description 
		 * @return string description of the custom field.
		 */
		function fieldDescription()
		{
			return $this->field_description;
		}
			
		/**
		 * @abstract Access the custom field ID 
		 * @return string ID of the custom field.
		 */
		function fieldId()
		{
			return $this->field_id;
		}

		/**
		 * @abstract Access the custom field type 
		 * @return string indicating the type of custom field.
		 */
		function fieldHtmlType()
		{	
			return $this->field_htmltype;
		}

		/**
		 * @abstract Access the extra tags string that should be appended to the custom fields element 
		 * @return string of tag attributes 
		 */
		function fieldExtraTags()
		{
			return $this->field_extratags;
		}
	}

	/** Custom field using a HTML checkbox type INPUT 
	 * Produces an INPUT Element of the CheckBox type in edit mode, view mode prints a string, either 'Yes' or 'No'
	 */
	class CustomFieldCheckBox extends CustomField
	{
		/** @abstract CustomFieldCheckBox constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldCheckBox($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'checkbox';
		}

		/** Generate the HTML for the custom field.
		 * @param string $mode	Can be "edit" or "view" to indicate if editing or viewing controls are to be generated
		 * @return string custom field as HTML
		 */ 
		function getHTML($mode)
		{
			switch ($mode) {
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

		/**
		 * @abstract Set the value of this checkbox, 0 is unticked, 1 is ticked 
		 * @param int $v The integer value to set this custom field to
		 * @return void
		 */ 
		function setValue($v)
		{
			$this->value_intvalue = $v;
		}
	}
	
	/** Custom field using a HTML text type INPUT 
	 * Produces an INPUT Element of the text type in edit mode, view mode prints the string value 
	 */
	class CustomFieldText extends CustomField
	{
		/** @abstract CustomFieldText constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldText($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'textinput';
		}

		/** Generate the HTML for the custom field.
		 * @param string $mode	Can be "edit" or "view" to indicate if editing or viewing controls are to be generated
		 * @return string custom field as HTML
		 */ 
		function getHTML($mode)
		{
			switch ($mode) {
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

	/** Custom field using a HTML TEXTAREA 
	 * 
	 * Produces an TEXTAREA Element in edit mode, view mode prints the string value and converts newlines to <br> elements 
	 */
	class CustomFieldTextArea extends CustomField
	{
		/** @abstract CustomFieldText constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldTextArea($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'textarea';
		}

		/** Generate the HTML for the custom field.
		 * @param string $mode	Can be "edit" or "view" to indicate if editing or viewing controls are to be generated
		 * @return string custom field as HTML
		 */ 
		function getHTML($mode)
		{
			switch ($mode) {
				case "edit":
					$html = $this->field_description.": </td><td><textarea name=\"".$this->field_name."\" ".$this->field_extratags.">".$this->charValue()."</textarea>";
					break;
				case "view":
					$html = $this->field_description.": </td><td class=\"hilite\" width=\"100%\">".nl2br($this->charValue());
					break;
			}
			return $html;
		}
	}
	
	/** Custom field string label 
	 * 
	 * Produces a string label which is uneditable by users 
	 */
	class CustomFieldLabel extends CustomField 
	{

	/** @abstract CustomFieldLabel constructor
	 * @param int $field_id 	ID of the custom field
	 * @param string $field_name 	HTML name of the custom field 
	 * @param int $field_order 	custom field order 
	 * @param string $field_description 	Description of the custom field
	 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
	 */
	    function CustomFieldLabel($field_id, $field_name, $field_order, $field_description, $field_extratags)
	    {
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
           		$this->field_htmltype = 'label';
	    }
	    
	   /** Generate the HTML for the custom field.
	    * @param string $mode this parameter is ignored for the label class
	    * @return string custom field as HTML
	    */ 
	    function getHTML($mode)
	    {
			// We don't really care about its mode
			return "<span $this->field_extratags>$this->field_description</span>";
	    }
	}
	
	/** Custom field seperator 
	 * 
	 * Produces a horizontal seperator (HR element) 
	 */
	class CustomFieldSeparator extends CustomField 
	{
	/** @abstract CustomFieldSeperator constructor
	 * @param int $field_id 	ID of the custom field
	 * @param string $field_name 	HTML name of the custom field 
	 * @param int $field_order 	custom field order 
	 * @param string $field_description 	Description of the custom field
	 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
	 */
	    function CustomFieldSeparator($field_id, $field_name, $field_order, $field_description, $field_extratags)
	    {
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'separator';
	    }
	    
	   /** Generate the HTML for the custom field.
	    * @param string $mode this parameter is ignored for the seperator class
	    * @return string custom field as HTML
	    */ 
	    function getHTML($mode)
	    {
			// We don't really care about its mode
			return "<hr $this->field_extratags />";
	    }
	}

	/** Custom field sqlselect (select based on an SQL query generated list) 
	 * 
	 * Generates a SELECT element with options loaded from a user supplied SQL query 
	 */
	class CustomFieldSQLSelect extends CustomFieldSelect
	{
		var $options; /**< select options as class SQLCustomOptionList */
		
		/** @abstract CustomFieldSQLSelect constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldSQLSelect($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'sqlselect';
			$this->options = new SQLCustomOptionList($field_id);		
			$this->options->load();	
		}
	}

	/** Custom field select (select based on a predefined list) 
	 * 
	 * Generates a SELECT element with options predefined in the custom field editor 
	 */
	class CustomFieldSelect extends CustomField
	{
		var $options; /**< select options as class CustomOptionList */ 

		/** @abstract CustomFieldSelect constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldSelect($field_id, $field_name, $field_order, $field_description, $field_extratags)
		{
			$this->CustomField($field_id, $field_name, $field_order, $field_description, $field_extratags);
			$this->field_htmltype = 'select';
			$this->options = new CustomOptionList($field_id);		
			$this->options->load();
		}

	   	/** Generate the HTML for the custom field.
	    	* @param string $mode can be "view" or "edit", determines which controls should be output.
	    	* @return string custom field as HTML
	    	*/ 
		function getHTML($mode)
		{
			switch ($mode) {
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
 
		/** Set the selected value index
		 * @param int $v index of the selected item
		 */ 
		function setValue($v)
		{
			$this->value_intvalue = $v;
		}

		/** Get the selected value index
		 * @return int the index of the selected item 
		 */
		function value()
		{
			return $this->value_intvalue;
		}
	}

	/** Custom field weblink  
	 * 
	 * Generates a HTML anchor (<a>) element with its string value as the href attribute 
	 */
	class CustomFieldWeblink extends CustomField {
		/** @abstract CustomFieldWeblink constructor
		 * @param int $field_id 	ID of the custom field
		 * @param string $field_name 	HTML name of the custom field 
		 * @param int $field_order 	custom field order 
		 * @param string $field_description 	Description of the custom field
		 * @param string $field_extratags 	Extra attributes applied to the custom field HTML tag 
		 */
		function CustomFieldWeblink ( $field_id, $field_name, $field_order, $field_description, $field_extratags ) {
			$this->CustomField( $field_id, $field_name, $field_order, $field_description, $field_extratags );
			$this->field_htmltype = 'href';
		}

	   	/** Generate the HTML for the custom field.
	    	* @param $mode string can be "view" or "edit", determines which controls should be output.
	    	* @return string custom field as HTML
	    	*/ 
		function getHTML($mode) {
			switch($mode) {
				case "edit":
					$html = $this->field_description.": </td><td><input type=\"text\" name=\"".$this->field_name."\" value=\"".$this->charValue()."\" ".$this->field_extratags." />";
					break;
				case "view":
					$html = $this->field_description.': </td><td class="hilite" width="100%"><a href="'.$this->charValue().'">'.$this->charValue().'</a>';
					break;
			}
			return $html;
		}
	}

	/** Custom fields class
	 *	
	 * Loads all custom fields related to a module, produces a html table of all custom fields
	 * Also loads values automatically if the obj_id parameter is supplied. The obj_id parameter is the ID of the module object 
	 * eg. company_id for companies module
	 */
	class CustomFields
	{
		var $m; /**< The module name */
		var $a; /**< The module action */ 
		var $mode; /**< Display mode: can be "edit" or "view" */
		var $obj_id; /**< Object Id: relates to a dotproject object such as project, company etc. */		

		var $fields; /**< associative array of CustomField objects */

		/** CustomFields constructor
		 * @param string $m Module name
		 * @param string $a Action name
		 * @param int $obj_id ID of the object with the requested module
		 * @param string $mode
		 * 
		 * @return void
		 */
		function CustomFields($m, $a, $obj_id = NULL, $mode = "edit")
		{
			$this->m = $m;
			$this->a = 'addedit'; // only addedit or view pages can carry the custom field for now, class assumes only addedit/view will be used
			$this->obj_id = $obj_id;
			$this->mode = $mode;

			// Get Custom Fields for this Module
			$q  = new DBQuery();
			$q->addTable('custom_fields_struct');
			$q->addWhere("field_module = '".$this->m."' AND	field_page = '".$this->a."'");
			$q->addOrder('field_order, field_name ASC');
			$rows = $q->loadList();						
			if ($rows == NULL) {
				// No Custom Fields Available
			} else {
				foreach ($rows as $row) {
					switch ($row["field_htmltype"]) {
						case "checkbox":
							$this->fields[$row["field_name"]] = new CustomFieldCheckbox( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "href":
							$this->fields[$row["field_name"]] = New CustomFieldWeblink( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "textarea":
							$this->fields[$row["field_name"]] = new CustomFieldTextArea( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "select":
							$this->fields[$row["field_name"]] = new CustomFieldSelect( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break;
						case "sqlselect":
							$this->fields[$row["field_name"]] = new CustomFieldSQLSelect( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
					   	break;
						case "label":
					      $this->fields[$row["field_name"]] = new CustomFieldLabel( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
					      break;
					   case "separator":
					      $this->fields[$row["field_name"]] = new CustomFieldSeparator( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
					      break;    
						default:
							$this->fields[$row["field_name"]] = new CustomFieldText( $row["field_id"], $row["field_name"], $row["field_order"], stripslashes($row["field_description"]), stripslashes($row["field_extratags"]) );
							break; 
					}
				}
	
				if ($obj_id > 0) {
					//Load Values
					foreach ($this->fields as $key => $cfield) {
						$this->fields[$key]->load( $this->obj_id );
					}
				}
			}
		}

		/** Add a custom field to the database
		 *
		 * Automatically generates the field_order variable so that the new field appears beneath any existing custom fields.
		 * @param $field_name HTML name of the custom field
		 * @param $field_description User description of the custom field
		 * @param $field_htmltype Type of custom field
		 * @param $field_datatype (Not Implemented) Type of validation that should be applied to the field
		 * @param $field_extratags Extra attributes to be added to the custom field element
		 * @param &$error_msg Reference to a variable to store any error messages 
		 */
		function add($field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, &$error_msg)
		{
			global $db;
			$next_id = $db->GenID('custom_fields_struct_id', 1);

			//$field_order = 1;
			$field_a = 'addedit'; // allows for expansion later, so that custom fields may be added to modules with multiple different pages 

			$q = new DBQuery();
			$q->addTable('custom_fields_struct');
			$q->addQuery('MAX(field_order) AS field_order_max');
			$q->addWhere('field_module = \''.$this->m.'\'');
			$q->addWhere('field_page = \''.$field_a.'\'');
			if (!$rs = $q->exec()) {
				$q->clear();
				return 0;
			}
			else
			{
				$row = $rs->fetchRow();
				$field_order_max = $row['field_order_max'];
				if (is_numeric($field_order_max))
				{
					$field_order = $field_order_max + 1; 
				}
				else
				{
					$field_order = 1;
				}
				$q->clear();
			}
			
			// TODO - module pages other than addedit
			// TODO - validation that field_name doesnt already exist
			$q  = new DBQuery();
			$q->addTable('custom_fields_struct');
			$q->addInsert('field_id', $next_id);
			$q->addInsert('field_module', $this->m);
			$q->addInsert('field_page', $field_a);
			$q->addInsert('field_htmltype', $field_htmltype);
			$q->addInsert('field_datatype', $field_datatype);
			$q->addInsert('field_order', $field_order);
			$q->addInsert('field_name', $field_name);
			$q->addInsert('field_description', $field_description);
			$q->addInsert('field_extratags', $field_extratags);


			if (!$q->exec()) {
				//return "<pre>".$sql."</pre>";
				$error_msg = $db->ErrorMsg();
				$q->clear();
				return 0;
			} else {
				$q->clear();
				return $next_id;
			}
		} 

		/** Update the details of an existing custom field
		 *
		 * @param $field_name HTML name of the custom field
		 * @param $field_description User description of the custom field
		 * @param $field_htmltype Type of custom field
		 * @param $field_datatype (Not Implemented) Type of validation that should be applied to the field
		 * @param $field_extratags Extra attributes to be added to the custom field element
		 * @param &$error_msg Reference to a variable to store any error messages 
		 */
		function update($field_id, $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, &$error_msg)
		{
			global $db;
			
			$q  = new DBQuery();
			$q->addTable('custom_fields_struct');
			$q->addUpdate('field_name', $field_name);
			$q->addUpdate('field_description', $field_description);
			$q->addUpdate('field_htmltype', $field_htmltype);
			$q->addUpdate('field_datatype', $field_datatype);
			$q->addUpdate('field_extratags', $field_extratags);
			$q->addWhere("field_id = ".$field_id);
			if (!$q->exec()) {
				$error_msg = $db->ErrorMsg();
				$q->clear();
				return 0;
			} else {
				$q->clear();
				return $field_id;
			}
		}

		/** Change the position/order of a custom field
		 *
		 * @param $field_id ID of the custom field to change
		 * @param $field_order Position number to change to
		 */
		function updateOrder($field_id, $field_order)
		{
			global $db;

			$q = new DBQuery();
			$q->addTable('custom_fields_struct');
			$q->addUpdate('field_order', $field_order);
			$q->addWhere("field_id = ".$field_id);
			if (!$q->exec()) {
				// unused for now
				//$error_msg = $db->ErrorMsg();
				$q->clear();
				return 0;
			} else {
				$q->clear();
				return $field_id;
			}
		}	

		/** get the CustomField object with the supplied field_id
		 *
		 * @param $field_id ID of the CustomField object to return
		 * @return requested CustomField object
		 */ 
		function fieldWithId($field_id)
		{
			foreach ($this->fields as $k => $v) {
				if ($this->fields[$k]->field_id == $field_id) { 
					return $this->fields[$k];
				}
			}
		}
	
		/** get the index of the CustomField object in the CustomFields $fields array
		 *
		 * @param $field_id ID of the CustomField object to find
		 * @return index of the CustomField object 
		 */
		function indexOfFieldWithId($field_id)
		{
			for ($i = 0; $i < $this->count(); $i++) {
				if ($this->fields[$i]->field_id == $field_id) { 
					return $i;
				}
			}
		}

		/** Move the position/order of a field up or down by one place
		 * @param $field_id ID of the CustomField object to move
		 * @param $direction Direction to move the field, can be "up" or "down"
		 */
		function moveFieldOrder($field_id, $direction )
		{
			$field_to_move = $this->fieldWithId($field_id);
			$cfenum = $this->getEnumerator(); 
			//die(print_r($this->fields));	
			//die('fid:'.print_r($field_id).'. fobj:'.print_r($field_to_move).'.');

                        if ($field_to_move->field_order == 1)
                        {
                                // upgrade fields with no proper order assigned

                                $reorder_idx = 2;

                                while ($cf = $cfenum->nextObject())
                                {
                                        if ($cf->field_id != $field_id && $cf->field_order == 1)
                                        {
                                                //update field with reordered index     
                                                $this->updateOrder($cf->field_id, $reorder_idx);
                                                $f = $this->fieldWithId($cf->field_id);
                                                $f->field_order = $reorder_idx;
                                                $reorder_idx++;
                                        }
                                }
                                $cfenum->reset();
                        }

			// switch places with the next field
			if ($direction == 'down')
			{
				while($cf = $cfenum->nextObject())
				{
					if ($cf == $field_to_move)
					{
						$nextfield = $cfenum->nextObject();
		
						if ($nextfield != NULL)
						{
							$field_order = $field_to_move->field_order;
							$nextfield_order = $nextfield->field_order;
							
							if ($field_order == $nextfield_order) 
							{
								$nextfield_order++;
							}
							$this->updateOrder( $field_to_move->field_id, $nextfield_order );
							$field_to_move->field_order = $nextfield_order;

							$this->updateOrder( $nextfield->field_id, $field_order ); 					
							$nextfield->field_order = $field_order;					
						}
						return 0;
					}	
				}
			}
			
			if ($direction == 'up')
			{
				$cfenum->moveLast();
			
				while($cf = $cfenum->prevObject())
				{
					if ($cf == $field_to_move)
					{
						$prevfield = $cfenum->prevObject();

						if ($prevfield != NULL)
						{
							$field_order = $field_to_move->field_order;
							$prevfield_order = $prevfield->field_order;
							
							if ($field_order == $prevfield_order)
							{
								$prevfield_order--;
							}
						
							$this->updateOrder( $field_to_move->field_id, $prevfield_order );
							$field_to_move->field_order = $prevfield_order;

							$this->updateOrder( $prevfield->field_id, $field_order ); 					
							$prevfield->field_order = $field_order;					
						}
						return 0;
					}	
				}
			}
			
		}

		/** Bind an array of POST or GET variables to the associated CustomField objects
		 * @param &$formvars Associative array of form variables
		 */ 
		function bind(&$formvars)
		{
			if (!count($this->fields) == 0) {
				foreach ($this->fields as $k => $v) {
//					if ($formvars[$k] != NULL) {
						$this->fields[$k]->setValue(@$formvars[$k]);
//					}
				}
			}
		}

		/** Store the current values of all the CustomField objects in this collection
		 * @param $object_id ID of the dotproject object (id of the company, project, etc)  
		 */
		function store($object_id)
		{
			if (!count($this->fields) == 0) {
				$store_errors = '';
				foreach ($this->fields as $k => $cf) {
					$result = $this->fields[$k]->store($object_id);
					if ($result) {
						$store_errors .= "Error storing custom field ".$k.":".$result;
					}
				}

				//if ($store_errors) return $store_errors;
				if (!is_null($store_errors)) {
					return $store_errors;	
				}
			}
		}

		/** Delete a custom field from the database
		 * @param $field_id ID of the custom field	
		 */
		function deleteField($field_id)
		{
			global $db;
			$q  = new DBQuery();
			$q->setDelete('custom_fields_struct');
			$q->addWhere("field_id = $field_id");
			if (!$q->exec()) {
				$q->clear();
				return $db->ErrorMsg();
			}
		}

		/** Get a count of the custom fields loaded
		 * @return Integer count of the custom fields
		 */
		function count()
		{
			return count($this->fields);
		}

		/** Generate the custom fields HTML as a table
		 * @return String containing the custom fields as a HTML table
		 */
		function getHTML()
		{
			if ($this->count() == 0) {
				return "";
			} else {
				$html = "<table width=\"100%\">\n";
	
				foreach ($this->fields as $cfield) {
					$html .= "\t<tr><td nowrap=\"nowrap\">".$cfield->getHTML($this->mode)."</td></tr>\n";
				}
				$html .= "</table>\n";

				return $html;
			}
		}

		/** Echo the custom fields as a HTML table
		 */  
		function printHTML()
		{
			$html = $this->getHTML();
			echo $html;
		}
		
		/** Custom Fields Smart Searcher
		 *
		 * Module agnostic custom field smart search plugin helper method
		 * Allows smartsearch to find patterns in custom fields on a per module base
		 * This has been implemented in stable_2 on 20060724 by gregorerhardt
		 * At the time of writing the smartsearch seems to be redesigned by cyberhorse
		 * Searchability of custom fields should be implemented by upcoming redesigned smartsearch plugins
		 */
		function search($moduleTable, $moduleTableId, $moduleTableName, $keyword )
		{
			$q  = new DBQuery;
			$q->addTable('custom_fields_values', 'cfv');
			$q->addQuery('m.'.$moduleTableId);
			$q->addQuery('m.'.$moduleTableName);
			$q->addJoin('custom_fields_struct', 'cfs', 'cfs.field_id = cfv.value_field_id');
			$q->addJoin($moduleTable, 'm', 'm.'.$moduleTableId.' = cfv. value_object_id');
			$q->addWhere('cfs.field_module = "'.$this->m.'"');
			$q->addWhere('cfv.value_charvalue LIKE "%'.$keyword.'%"');
			return $q->loadList();
		}

		/** get a custom fields enumerator 
		 * @return An instance of the CustomFieldsEnumerator class.
		 */  
		function getEnumerator()
		{
			return new CustomFieldsEnumerator( $this );
		}
	}

	/** CustomFields object Enumerator
	 * 
	 * Enumerates a CustomFields object in the style of a Java enumerator class
	 */		
	class CustomFieldsEnumerator
	{
		var $customfieldsobj; /**< Reference to CustomFields object */
		var $keys; /**< Keys of the CustomField objects inside CustomFields */
		var $index; /**< Current working index */

		/** CustomFieldsEnumerator constructor
		 * @param $customfieldsobj A CustomFields object to enumerate
		 */
		function CustomFieldsEnumerator( $customfieldsobj )
		{	
			$this->customfieldsobj = $customfieldsobj;
			$this->index = 0;
			$this->keys = array_keys($this->customfieldsobj->fields);
		}

		/** Get the next CustomField object in the collection
		 * @return The next CustomField object, or NULL if there are no more objects to enumerate
		 */
		function nextObject()
		{
			if ($this->index >= count($this->keys))
			{
				return NULL;	
			} 
			$nextobject = $this->customfieldsobj->fields[$this->keys[$this->index]];
			$this->index++;
			return $nextobject;
		}

		/** Get the previous CustomField object in the collection
		 * @return The previous CustomField object, or NULL if we are already at the first object in the collection. 
		 */
		function prevObject()
		{
			if ($this->index < 0)
			{
				return NULL;
			}
			$prevobject = $this->customfieldsobj->fields[$this->keys[$this->index]];
			$this->index--;
			return $prevobject;
		}

		/** Move to the last CustomField object in the collection
		 */
		function moveLast()
		{
			$this->index = (count($this->keys) - 1);
		}

		/** Reset to the first CustomField object in the collection
		 */
		function reset()
		{
			$this->index = 0;
		}
	}

	/** SQL generated option list
	 *
	 * Used in conjunction with the CustomFieldSQLSelect class
	 */ 
	class SQLCustomOptionList
	{
		var $field_id; /**< Custom field ID */
		var $query; /**< SQL Query to produce the list options */
		
		/** SQLCustomOptionList constructor
		 * @param $field_id ID of the CustomField object
		 */
		function SQLCustomOptionList($field_id)
		{
			$this->field_id = $field_id;
		}
		
		/** Load the query to be used for the option list 
		 */ 
		function load()
		{
			global $db;
			
			$q = new DBQuery();
			$q->addTable('custom_fields_lists');
			$q->addWhere('field_id = '.$this->field_id);
			if (!$rs = $q->exec()) {
				$q->clear();
				return $db->ErrorMsg();
			}
			
			$opt_qryrow = $q->fetchRow();
			$this->query = $opt_qryrow["list_value"];			
			
			$q->clear();		 
		}
		
		/** Store the query used for the option list
		*/
		function store()
		{
			global $db;
			
			$q  = new DBQuery();
			$q->addTable('custom_fields_lists');
			$q->addInsert('field_id', $this->field_id);
			$q->addInsert('list_option_id', 0);
			$q->addInsert('list_value', db_escape(strip_tags($this->query)));
			
			$rs = $q->exec();
		}
		
		/** Delete this option list from the database
		*/
		function delete()
		{
			$q = new DBQuery();
			$q->setDelete('custom_fields_lists');
			$q->addWhere("field_id = ".$this->field_id);
			
			$q->exec();
		}
		
		/** Set the SQL query to be used for generating the HTML option list.
		 * @param $sql SQL Query containing two columns: the first column contains the key and the second column contains the value
		 */ 
		function setQuery($sql)
		{
			$this->query = $sql;
		}
		
		/** Get the current SQL query
		 * @return The SQL query
		 */
		function getQuery()
		{
			return $this->query;
		}
		
		/** Generate the HTML for this option list
		 * @param $field_name The field_name of the CustomFieldSQLSelect object 
		 * @param $selected The index of the selected option	
		 */ 
		function getHTML($field_name, $selected)
		{
			global $db;
			//die($this->query);
			$rs = $db->Execute($this->query);
			$html = "<select name=\"".$field_name."\">\n";
			
			while ($r = $rs->fetchRow()) {
					$html .= "\t<option value=\"".$r[0]."\"";
					if ($r[0] == $selected) $html .= " selected ";
					$html .= ">".$r[1]."</option>";
			}	
			
			$html .= "</select>\n";
			return $html;
		}
		
		/** Get the option item at a particular index
		 * @param $i The index of the option in the list
		 * @return An array containing the key and the value for the option at index $i
		 */ 
		function itemAtIndex($i)
		{
			global $db;
			$rs = $db->Execute($this->query);
			$allrows = $rs->GetAll();
			// Non Zero Based Index is Supplied
			return $allrows[$i - 1][1];
		}
	}
	
	class CustomOptionList
	{
		var $field_id;
		var $options;

		function CustomOptionList( $field_id )
		{
			$this->field_id = $field_id;
			$this->options = array();
		}

		function load()
		{
			global $db;
	
			$q  = new DBQuery();
			$q->addTable('custom_fields_lists');
			$q->addWhere("field_id = {$this->field_id}");
			$q->addOrder("list_value");
			if (!$rs = $q->exec()) {
				$q->clear();
				return $db->ErrorMsg();		
			}

			$this->options = array();

			while ($opt_row = $q->fetchRow()) {
				$this->options[$opt_row["list_option_id"]] = $opt_row["list_value"];
			}
			$q->clear();
		}

		function store()
		{
			global $db;

			if (!is_array($this->options)) {
				$this->options = array();
			}
			
			//load the dbs options and compare them with the options
			$q  = new DBQuery();
			$q->addTable('custom_fields_lists');
			$q->addWhere("field_id = {$this->field_id}");
			$q->addOrder("list_value");
			if (!$rs = $q->exec()) {
				$q->clear();
			  	return $db->ErrorMsg();		
			}

			$dboptions = array();

			while ($opt_row = $q->fetchRow()) {
				$dboptions[$opt_row["list_option_id"]] = $opt_row["list_value"];
			}
			$q->clear();
			
			$newoptions = array();
			$newoptions = array_diff($this->options, $dboptions);
			$deleteoptions = array_diff($dboptions, $this->options);
			//insert the new options
			foreach($newoptions as $opt) {
				$optid = $db->GenID('custom_fields_option_id', 1 );

				$q  = new DBQuery();
				$q->addTable('custom_fields_lists');
				$q->addInsert('field_id', $this->field_id);
				$q->addInsert('list_option_id', $optid);
				$q->addInsert('list_value', db_escape(strip_tags($opt)));

				if (!$q->exec()) $insert_error = $db->ErrorMsg();  	
				$q->clear();
			}	
			//delete the deleted options
			foreach($deleteoptions as $opt => $value) {
				$q  = new DBQuery();
				$q->setDelete('custom_fields_lists');
				$q->addWhere("list_option_id = $opt");

				if (!$q->exec()) {
					$delete_error = $db->ErrorMsg();
				}  	
				$q->clear();
			}	

			return $insert_error.' '.$delete_error;
		}

		function delete()
		{
			$q  = new DBQuery();
			$q->setDelete('custom_fields_lists');
			$q->addWhere("field_id = {$this->field_id}");
			$q->exec();
			$q->clear();
		}

		function setOptions($option_array)
		{
			$this->options = $option_array;
		} 

		function getOptions()
		{
			return $this->options;
		}

		function itemAtIndex($i)
		{
			return $this->options[$i];
		}

		function getHTML($field_name, $selected)
		{
			$html = "<select name=\"".$field_name."\">\n";
			foreach ($this->options as $i => $opt) {
				$html .= "\t<option value=\"".$i."\"";
				if ($i == $selected) {
					$html .= " selected ";
				}
				$html .= ">".$opt."</option>";
			}	
			$html .= "</select>\n";
			return $html;
		}		
	}
?>
