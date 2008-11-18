/**
 * DP.Widget.Search class
 * 
 * The search widget attaches to a text input and a button
 */

DP.Widget.Search = function(input_id, btn_id) {
	
	// Convenience references to YUI
	this._dom = YAHOO.util.Dom;
	this._evt = YAHOO.util.Event;
	
	this._filter = new DP.Datasource.Filter();
	var obs = new DP.Debug.Observer();
	this._filter.attach(obs);
	
	this._text_input = this._dom.get(input_id);
	this._search_button = this._dom.get(btn_id);
	
	this._evt.addListener(this._search_button, "click", DP.Widget.Search.searchAction);
	
	// Attach references to this object on the html nodes. to get around scoping issues when event is fired
	this._search_button.dpObjectRef = this;
	this._text_input.dpObjectRef = this;
	// TODO - may add keydown event listener to filter input for numeric field searching etc..
}

DP.Widget.Search.searchAction = function() {
	var obj = this.dpObjectRef;
	obj._filter.setValue(obj._text_input.value);
}

