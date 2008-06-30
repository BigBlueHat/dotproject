/** ListSelection class - takes care of the client side ui changes related to selecting
 *	and de-selecting checkbox items from a list. 
 *	
 *	TODO - create hidden variables that store selections across page changes.
 */

ListSelection = function() 
{
	this._selections = [];
	
	this.highlightBackground = '#FFFFEE';
	this.highlightFontWeight = 'bold';
}


/* Toggle a given checkbox input element */
ListSelection.prototype.toggle = function(el, highlight_el)
{
	if (el.checked == true) {
		this.add(el, highlight_el);	
	} else {
		this.remove(el, highlight_el);
	}
}

/* add a given input element to the selection */
ListSelection.prototype.add = function(el, highlight_el)
{
	this._selections.push(el);
	
	highlight_el.style.backgroundColor = this.highlightBackground;
	highlight_el.style.fontWeight = this.highlightFontWeight;
}

/* remove a given input element from the selection */
ListSelection.prototype.remove = function(el, highlight_el)
{

	for (i = 0; i < this._selections.length; i++) {
		if (this._selections[i] == el) {
			this._selections.splice(i, 1);

			highlight_el.style.backgroundColor = '';
			highlight_el.style.fontWeight = '';
		}
	}
}

ListSelection.prototype.clear = function()
{
	this._selections = [];
}

ListSelection.prototype.selected = function()
{
	var selectiontext = 'Selected: ';
	for (i = 0; i < this._selections.length; i++) {
		selectiontext = selectiontext + this._selections[i].value + ',';
	}
	return selectiontext;
	//return this._selections;
}

// @todo - strings need localisation.
ListSelection.prototype.delete = function(el_table_id)
{
	if (this._selections.length == 0) {
		alert('You havent selected anything');
	} else {
		if (confirm('^Are you sure you want to delete the selected items?'))
		{
			document.getElementById('select-delete').value = 1;
			document.getElementById(el_table_id).parentNode.submit();
			// submit form
		}
	}
}

/* Select all checkboxes on the current page */
ListSelection.prototype.selectAll = function(el_table_id) 
{
	//var chks = dojo.query("[type=checkbox]", el_table_id);
	
	
	chks.forEach(function(cb) {
		cb.checked = 'checked';
		dpselection.add(cb, cb.parentNode.parentNode); // scope doesnt resolve this keyword to ListSelection?
	});
}

/* Deselect all checkboxes on the current page */
ListSelection.prototype.selectNone = function(el_table_id)
{
	var chks = dojo.query("[type=checkbox]", el_table_id);
	
	chks.forEach(function(cb) {
		cb.checked = '';
		cb.parentNode.parentNode.style.backgroundColor = '';
		cb.parentNode.parentNode.style.fontWeight = '';
	});

	this.clear();
}

/* Invert the selection of checkboxes on the current page */
ListSelection.prototype.selectInvert = function(el_table_id)
{
	var toremove = [];
	var chks = dojo.query("[type=checkbox]", el_table_id);
	
	chks.forEach(function(cb) {
		if (cb.checked) {
			cb.checked = false;
			toremove.push(cb);
		} else {
			cb.checked = 'checked';
			dpselection.add(cb,cb.parentNode.parentNode);
		}
	});

	
	for (t = 0; t < toremove.length; t++) {
		this.remove(toremove[t], toremove[t].parentNode.parentNode);
	}		
}

var dpselection = new ListSelection;