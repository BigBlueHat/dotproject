// List item constructor takes a reference to list item element
WbsList.item = function(li, parent_list) 
{
	//console.log('INIT: WbsList.item');
	
	// Use supplied parent or try to determine list parent.
	
	if (parent_list != null)
	{
		this._parent = parent_list;
	} else {
		if (li.parentNode != null) {
			if (li.parentNode.tagName == 'UL') {
				this._parent = new WbsList(li.parentNode);
			} else {
				this._parent = null;
			}
		} else {
			this._parent = null;
		}
	}
	
	if (li.tagName != 'LI')
	{
		console.log('Constructor error: Not a list item');
	}

	this._dom = YAHOO.util.Dom;
	this._evt = YAHOO.util.Event;

	this._li = li;
	this._num_span = li.childNodes[0];
	this._name_span = li.childNodes[1];
	this._name = this._name_span.firstChild;
	
	if (this._name.tagName == 'INPUT') {
		this._editing = true;
	} else {
		this._editing = false;
	}
}

WbsList.item.MODE_VIEW = 1; // view mode
WbsList.item.MODE_EDIT = 2; // edit mode

// Create a new WbsList.item with the default text value of [val] and mode of [mode]
WbsList.item.create = function(val, mode)
{	
	if (typeof mode == "undefined") {
		mode = WbsList.item.MODE_VIEW;
	}
	
	if (typeof val == "undefined") {
		val = '';
	}

	console.log('Creating instance of WbsList.item with text "'+val+'"...');
	
	if (mode == WbsList.item.MODE_VIEW) {
		var name = document.createTextNode(val);
	} else {
		var name = document.createElement('input');
		name.value = val;
	}
	
	var name_span = document.createElement('span');
	name_span.appendChild(name);
	
	var num_span = document.createElement('span');
	
	var li = document.createElement('li');
	li.appendChild(num_span);
	li.appendChild(name_span);
	
	var item = new WbsList.item(li);
	
	if (mode == WbsList.item.MODE_VIEW) {
		item.setNotEditing();
	} else {
		item.setEditing();
		
		item._evt.addListener(item.getNameElement(), 'blur', WbsList.item.evtBlur);
		
		item._evt.addListener(item.getNameElement(), 'keypress', WbsList.item.evtKeyPress);
		item._evt.addListener(item.getNameElement(), 'keydown', WbsList.item.evtKeyDown);
	}
	
	return item;
}

// EVENT HANDLERS

WbsList.item.evtEditItem = function(evt)
{
	var li = evt.target.parentNode;
	var item = new WbsList.item(li);
	item.edit();
}

WbsList.item.evtHighlight = function(evt)
{
	var li = evt.target.parentNode;
	//console.log(li);
	var item = new WbsList.item(li);
	item.highlight();
}

WbsList.item.evtNoHighlight = function(evt)
{
	var li = evt.target.parentNode;
	//console.log(li);
	var item = new WbsList.item(li);
	item.deHighlight();
}

WbsList.item.evtKeyPress = function(evt)
{
	var input = evt.target;
	var li = input.parentNode.parentNode;

	var item = new WbsList.item(li);

	// Pressed enter
	if (evt.which == 13) {
		item.save();
		
		var new_item = WbsList.item.create('', WbsList.item.MODE_EDIT);
		
		var parent_list = new WbsList(li.parentNode);
		parent_list.insertItemAfter(new_item, item);
		new_item.select();
	}
}

WbsList.item.evtKeyDown = function(evt)
{
	var input = evt.target
	var li = input.parentNode.parentNode;
	
	var item = new WbsList.item(li);
	
	// Press TAB - Indent item
	if (evt.which == 9 && evt.shiftKey == false) {
		window.tasks.indent(item);
		item.select();
		evt.preventDefault(); // dont actually input the character
	}
	
	// Press Shift-Tab - Outdent item
	if (evt.which == 9 && evt.shiftKey == true) {
		window.tasks.outdent(item);
		item.select();
		evt.preventDefault();
	}
}

WbsList.item.evtBlur = function(evt)
{
	var input = evt.target;
	var li = input.parentNode.parentNode;
	
	var item = new WbsList.item(li);
	
	item.save();
}

// The item is in the edit state
WbsList.item.prototype.setEditing = function()
{
	this._editing = true;
	
	this._evt.removeListener(this._name_span, 'click');
	this._evt.removeListener(this._name_span, 'mouseover');
	this._evt.removeListener(this._name_span, 'mouseout');
}

// The item is in the view/select state
WbsList.item.prototype.setNotEditing = function()
{
	this._editing = false;
	
	// Add edit controls
	this._evt.addListener(this._name_span, 'click', WbsList.item.evtEditItem);
	this._evt.addListener(this._name_span, 'mouseover', WbsList.item.evtHighlight);
	this._evt.addListener(this._name_span, 'mouseout', WbsList.item.evtNoHighlight);	
}

WbsList.item.prototype.getType = function()
{
	return WbsList.TYPE_ITEM;
}

// Determine whether the item is in edit or view state
WbsList.item.prototype.isEditing = function()
{
	return this._editing;
}

// Set the task name value of the list item.
WbsList.item.prototype.setValue = function(v)
{
	if (this.isEditing()) {	
		this._name.nodeValue = v;	
	} else {		
		var new_value = document.createTextNode(v);
		this._name_span.replaceChild(new_value, this._name);
	}
}

// Get the task name value of the list item.
WbsList.item.prototype.getValue = function()
{
	if (this.isEditing()) {
		return this._name.value;
	} else {
		return this._name.textContent;
	}
}

WbsList.item.prototype.getNameElement = function()
{
	return this._name;
}

// Check if the item is empty or not.
WbsList.item.prototype.isEmpty = function()
{
	if (this.getValue() == '') {
		return true;
	} else {
		return false;
	}
}

WbsList.item.prototype.getNode = function()
{
	return this._li;
}

// Switch the item into edit mode
WbsList.item.prototype.edit = function()
{
	var input = document.createElement('input');
	input.value = this.getValue();
	
	this._evt.addListener(input, 'keypress', WbsList.item.evtKeyPress);
	this._evt.addListener(input, 'keydown', WbsList.item.evtKeyDown);
	
	this._evt.addListener(input, 'blur', WbsList.item.evtBlur);
	
	this._name_span.removeChild(this._name);
	this._name = input;
	this._name_span.appendChild(input);
	input.focus();

	this.setEditing();	
}

WbsList.item.prototype.delete = function()
{
	this._li.parentNode.removeChild(this._li);
}

// Switch the item into view mode
WbsList.item.prototype.save = function()
{
	if (this.isEmpty()) {
		// empty items are automatically deleted.
		this.delete();
	} else {
		var item_text = this.getValue(); // get input value
		var text_node = document.createTextNode(item_text);
		
		this._evt.removeListener(this._name, 'keypress');
		this._evt.removeListener(this._name, 'keydown');
		
		this._evt.removeListener(this._name, 'blur');
		
		this._name_span.removeChild(this._name);
		this._name = text_node;
		this._name_span.appendChild(text_node);
		
		this.deHighlight();
		
		this.setNotEditing(); // change state to view
	}
}

WbsList.item.prototype.select = function()
{
	if (this.isEditing()) {
		this._name.focus();
	} else {
		// maybe just highlight?
	}
}

// Highlight the item
WbsList.item.prototype.highlight = function()
{
	this._li.style.backgroundColor = '#FFFFEE';
}

// De-Highlight the item
WbsList.item.prototype.deHighlight = function()
{
	this._li.style.backgroundColor = '#FFFFFF';
}
