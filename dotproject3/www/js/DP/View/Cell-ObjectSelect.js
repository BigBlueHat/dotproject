/** ListSelection class - takes care of the client side ui changes related to selecting
 *	and de-selecting checkbox items from a list. 
 *	
 *	TODO - create hidden variables that store selections across page changes.
 */

ListSelection = function() 
{
	this._selections = [];	
}


/* commented out until this object keeps an internal list of selected checkboxes */
/*
ListSelection.prototype.add = function(element_id, element_value) 
{
	var new_selection = [ element_id, element_value ];
	this._selections.push(new_selection);
}

ListSelection.prototype.remove = function(element_id, element_value) 
{
	for (i = 0; i < this._selections.length; i++) {
		if (this._selections[i][1] == element_value) {
			this._selections.splice(i, 1);
		}
	}
}

ListSelection.prototype.selected = function()
{
	return this._selections;
}
*/

/* Select all checkboxes on the current page */
ListSelection.prototype.selectAll = function(el_table_id) 
{
	var el_table = document.getElementById(el_table_id);
	var inputs = el_table.getElementsByTagName('input');
	
	for (i = 0; i < inputs.length; i++) {
		if (inputs[i].type == 'checkbox') {
			inputs[i].checked = true;
		}
	}
}

/* Deselect all checkboxes on the current page */
ListSelection.prototype.selectNone = function(el_table_id)
{
	var el_table = document.getElementById(el_table_id);
	var inputs = el_table.getElementsByTagName('input');
	
	for (i = 0; i < inputs.length; i++) {
		if (inputs[i].type == 'checkbox') {
			inputs[i].checked = false;
		}
	}	
}

/* Invert the selection of checkboxes on the current page */
ListSelection.prototype.selectInvert = function(el_table_id)
{
	var el_table = document.getElementById(el_table_id);
	var inputs = el_table.getElementsByTagName('input');
	
	for (i = 0; i < inputs.length; i++) {
		if (inputs[i].type == 'checkbox') {
			if (inputs[i].checked == false) {
				inputs[i].checked = true;
			} else {
				inputs[i].checked = false;
			}
		}
	}		
}

var dpselection = new ListSelection;