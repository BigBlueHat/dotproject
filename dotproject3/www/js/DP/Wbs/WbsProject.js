WbsProject = function(container) 
{
	console.log('INIT: WbsProject');
	
	this._inittext = 'Click here to enter the project name...';
	this._labeltext = 'Project Name :';

	this._label = new WbsProject.label(container, this._labeltext);	
	this._editor = new WbsProject.editor(container, this._inittext);
}

WbsProject.create = function(container_id) 
{
	window.wbsproject = new WbsProject(document.getElementById(container_id));
}

// Get the instance of WbsProject.editor
WbsProject.prototype.getEditor = function ()
{
	return this._editor;
}

WbsProject.editor = function(container, value)
{
	this._evt = YAHOO.util.Event;
	
	this._container = container;
	
	this._editor_span = document.createElement('span');
	this._editor_span.id = 'ProjectName';
	this._editor_span.className = 'wbs_project_editor';
	
	this._editing = false;
	this._changed = false;
	
	if (value == null) { value = ''; }
	this._editor_text = document.createTextNode(value);
	
	this._editor_span.appendChild(this._editor_text);	
	this._evt.addListener(this._editor_span, 'click', WbsProject.editor.editClicked);
	
	this._container.appendChild(this._editor_span);
}

WbsProject.editor.editClicked = function(evt)
{
	console.log('Edit WbsProject');
	
	var span = evt.target;
	var editor = window.wbsproject.getEditor();
	editor.edit();
}

WbsProject.editor.keyPressed = function(evt)
{
	var text_input = evt.target;
	
}

// Transform text node into editable field, preserving value.
WbsProject.editor.prototype.edit = function()
{
	this._editing = true;
	
	this._evt.removeListener(this._editor_span, 'click');
	
	var text_value = '';
	if (this._changed == true) {
		text_value = this._editor_span.firstChild.value;
	}
	this._changed = true;
	
	var text_input = document.createElement('input');
	this._evt.addListener(text_input, 'keypress', this.keyPressed);
	
	this._editor_span.replaceChild(text_input, this._editor_text);
	text_input.focus();
}

WbsProject.label = function(container, label)
{
	this._evt = YAHOO.util.Event;
	
	this._label_span = document.createElement('span');
	this._label_span.className = 'wbs_project_label';
	this._label = document.createTextNode(label);
	
	this._label_span.appendChild(this._label);
	container.appendChild(this._label_span);
}