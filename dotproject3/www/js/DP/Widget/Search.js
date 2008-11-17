/**
 * DP.Widget.Search class
 * 
 * The search widget attaches to a text input and a button
 */

DP.Widget.Search = function(input_id, btn_id) {
	
	// Convenience references to YUI
	this._dom = YAHOO.util.Dom;
	this._evt = YAHOO.util.Event;
	
	this._filter = DP.Datasource.Filter.factory('string');
	
	this._text_input = this._dom.get(input_id);
	this._search_button = this._dom.get(btn_id);
	
	this._evt.addListener(this._search_button, "click", this.searchAction);
	// TODO - may add keydown event listener to filter input for numeric field searching etc..
}

/**
 * Fire the search action.
 * @return
 */
DP.Widget.Search.prototype.searchAction = function() {
	alert('search');
}