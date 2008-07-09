WbsList = function(ul, parent_list) {
	//console.log('INIT: WbsList');
	
	if (typeof parent_list == "WbsList") {
		this._parent = parent_list;
	} else {
		if (typeof ul.parentNode == "undefined") {
			this._parent = null;
		} else {
			if (ul.parentNode) {
				if (ul.parentNode.tagName == 'UL') {
					this._parent = new WbsList(ul.parentNode);
				}
			}
		}
	}
	
	if (ul.tagName != 'UL') {
		console.log('Error: tried to instantiate WbsList with '+ ul);
	}
	// constructor
	this._dom = YAHOO.util.Dom;
	this._evt = YAHOO.util.Event;
	this.container = ul;
}

WbsList.TYPE_LIST = 1;
WbsList.TYPE_ITEM = 2;

WbsList.create = function(container_id)
{
	console.log('Creating instance of WbsList...');
	
	var el = YAHOO.util.Dom.get(container_id);
	var list = new WbsList(el);
	
	var clickme_item = WbsList.item.create('Click here to enter a new task...');
	
	list.appendItem(clickme_item, null);
	window.tasks = list;
}

WbsList.prototype.getNode = function()
{
	return this.container;
}

// Get the child elements of this list node.
WbsList.prototype.getNodeChildren = function()
{
	return this.container.childNodes;
}

// Get lists which are related to this parent item.
WbsList.prototype.getItemSublists = function(item)
{
	var item_sublists = new Array();
	
	for (next_item = item.getNode().nextSibling; next_item.tagName == 'UL'; next_item = next_item.nextSibling) {
		item_sublists.push(next_item);
	}
	
	return item_sublists;
}

WbsList.prototype.count = function()
{
	return this.container.childNodes.length;
}

// Get the WbsList object type - in this case its a list
WbsList.prototype.getType = function()
{
	return WbsList.TYPE_LIST;
}

// Append an existing list to this list
WbsList.prototype.appendList = function(list)
{
	var list_items = list.getNodeChildren();
	var i;
	
	for (i = 0; i < list_items.length; i++)
	{
		var node = list_items[i];
		if (node.tagName == 'LI') {
			var new_item = new WbsList.item(node);
			this.appendItem(new_item);
		} else if (node.tagName == 'UL') {
			var new_list = new WbsList(node);
			this.appendList(new_list);
		}
	}
}

// Append a WbsList.item to a container
WbsList.prototype.appendItem = function(item, container)
{
	if (container == null) {
		container = this.container;
	}
	
	container.appendChild(item.getNode());
}

// Insert a list item above all existing items.
WbsList.prototype.insert = function(item)
{
	this.container.insertBefore(item.getNode(), this.getFirstItem());
}

WbsList.prototype.insertItemBefore = function(item, item_before)
{
	this.container.insertBefore(item.getNode(), item_before.getNode());
}

// Insert a WbsList.item after a sibling item
WbsList.prototype.insertItemAfter = function(item, item_before)
{
	var before_node = item_before.getNode();
	
	if ( before_node.nextSibling == null) {
		this.container.appendChild(item.getNode());
	} else {
		this.container.insertBefore(item.getNode(), before_node.nextSibling);
	}
}

// Get the item before the specified item
WbsList.prototype.getPreviousItemBefore = function(item)
{
	var item_node = item.getNode();
	if (typeof item_node.previousSibling == "undefined") {
		return null;
	} else {
		var prev_sibling = item_node.previousSibling;
		
		if (prev_sibling && prev_sibling.tagName == 'LI') {
			return new WbsList.item(prev_sibling);
		} else if (prev_sibling && prev_sibling.tagName == 'UL') {
			return new WbsList(prev_sibling);
		} else {
			return null;
		}
	}
}	

// Get the next sibling after the specified item
WbsList.prototype.getNextItemAfter = function(item)
{
	var item_node = item.getNode();
	if (item_node.nextSibling == null) {
		return null;
	} else {
		var next_sibling = item_node.nextSibling;
		
		if (next_sibling.tagName == 'LI') {
			return new WbsList.item(next_sibling, this);
		} else {
			return new WbsList(next_sibling, this);
		}
	}
}

WbsList.prototype.getFirstItem = function()
{
	var first = this.container.childNodes[0];
	return first;
}

WbsList.prototype.indent = function(item)
{
	console.log('Indenting item:' + item.getValue());
	
	var next_item = this.getNextItemAfter(item);
	var prev_item = this.getPreviousItemBefore(item);
	
	// Next sibling is a list
	if (next_item != null && 
		next_item.getType() == WbsList.TYPE_LIST && 
		(prev_item == null || prev_item.getType() == WbsList.TYPE_ITEM)) {
			
		console.log('Indenting item to neighbouring list below.');
		
		next_item.insert(item);
		item.edit();
	
	// Previous sibling is a list
	} else if (prev_item != null && 
		prev_item.getType() == WbsList.TYPE_LIST &&
		(next_item == null || next_item.getType() == WbsList.TYPE_ITEM)) {
		
		console.log('Indenting item to neighbouring list above.');
	
		prev_item.appendItem(item);
		item.edit();
		
	// Both siblings are lists.
	} else if (next_item != null && 
		next_item.getType() == WbsList.TYPE_LIST &&
		prev_item != null &&
		prev_item.getType() == WbsList.TYPE_LIST) {
	
		console.log('Concatenating neighbouring lists with this item');
		
		var prev_list = prev_item;
		var next_list = next_item;
		
		prev_list.appendItem(item);
		prev_list.appendList(next_list);
	
	// No siblings are lists.	
	} else {
		console.log('Creating new list with this item');
	
		var ul = document.createElement('ul');
		ul.className = 'wbs';
		
		var new_list = new WbsList(ul);
		
		item.getNode().parentNode.replaceChild(ul, item.getNode());
		new_list.appendItem(item);
		
		item.edit();
	}
}

WbsList.prototype.outdent = function(item)
{
	console.log('Outdenting item:' + item.getValue());
	
	var item_parent = item.getNode().parentNode;
	console.log('Outdent: item parent node: ' + item_parent);
	
	if (item_parent != null) {
		var parent_list = new WbsList(item_parent);
		
		// List contains more than this item, so we can't simply remove the UL tags
		if (parent_list.count() > 1 && parent_list.getNode().parentNode != null) {
			var grandparent = new WbsList(parent_list.getNode().parentNode);
			
			// This item is the first in the list, and so must be moved
			// outside the list, just before it.
			if (parent_list.getNode().firstChild == item.getNode()) {
				console.log('Outdenting outside this list, before it');
				grandparent.insertItemBefore(item, parent_list);		
				item.edit();
			// This item is the last in the list, and so must be moved
			// outside the list, just after it.
			} else if (parent_list.getNode().lastChild == item.getNode()) {
				console.log('Outdenting outside this list, after it');
				grandparent.insertItemAfter(item, parent_list);		
				item.edit();
			// This item is in the middle somewhere, and so the list must
			// be sliced with our item after the first slice.
			} else {
				console.log('Outdenting in the middle of list');
			
				var list_after_node = document.createElement('ul');
				list_after_node.className = 'wbs';
				
				// create a new list which only contains items after the
				// split index
				var list_after = new WbsList(list_after_node);
				var after_slice = false;
				
				for (i = 0; i < parent_list.count(); i++) {
					if (after_slice == true) {
						list_after.getNode().appendChild(parent_list.getNode().childNodes[i]);
					}
					
					if (parent_list.getNode().childNodes[i] == item.getNode()) {
						after_slice = true;
					}
				}
				//console.dir(grandparent);
				grandparent.insertItemAfter(list_after, parent_list);
				grandparent.insertItemAfter(item, parent_list);
				
				item.edit();
			}
		} else {	
			// This item is the only item in the list. The list can be removed and this item
			// added to the parent in the same spot.
			console.log('Replace list with item');
			var grandparent = new WbsList(parent_list.getNode().parentNode);
			
			grandparent.getNode().replaceChild(item.getNode(), parent_list.getNode());
			
			item.edit();
		}
	}
}

WbsList.prototype.outdentItem = function(li)
{
	var sublist = li.parentNode;
	var parent = sublist.parentNode;
	
	console.log(sublist.id);

	// Parent UL has siblings, so we cannot remove enclosing UL tags.
	if (sublist.childNodes.length > 1) {
	
		// if we are the first child of this UL, move ourselves to the previous sibling of the UL
		if (sublist.firstChild == li) {
			console.log('Outdent:firstChild');
			
			parent.insertBefore(li, sublist);
		
		// if we are the last child, move ourselves to the nextSibling of the UL element.	
		} else if (sublist.lastChild == li) { 
			console.log('Outdent::lastChild');
			
			if (sublist.nextSibling != null) {
				parent.insertBefore(li, sublist.nextSibling);
			} else {
				parent.appendChild(li);
			}
			
		// if we are in the middle, then split UL into two, insert us between ULs	
		} else {
			console.log('Outdent::middleChild (splitting ul element)');
			
			var new_list = document.createElement('ul');
			new_list.className = 'wbs';
			var after = false;
			
			// iterate through sublist members
			for (i = 0; i < sublist.childNodes.length; i++) {
				//console.log(sublist.childNodes[i]);
				if (after == true) {
						// add all items after us to a new UL
			// remove all of those items from the sublist
					new_list.appendChild(sublist.childNodes[i]);
					//sublist.removeChild(sublist.childNodes[i]);
				}
				
				// must be at end
				if (sublist.childNodes[i] == li) {
					after = true;
				}
			}
	
			// append the new UL
			if (parent.nextSibling != null) {
				parent.insertBefore(new_list, parent.nextSibling);
				parent.insertBefore(li, new_list);
			} else { 
				parent.appendChild(new_list);
				parent.insertBefore(li, new_list);
			}	
			// insert us before the new UL
		
		}
	
		// move our list item to the end of the parent child list
		parent.insertBefore(li, sublist.nextSibling);

	} else {
		// node being outdented has no siblings, its only wrapped in a UL tag.
		if (sublist.id == this.container.id) {
			// cant outdent past level zero
		} else {
			// remove wrapper by replacing list with just the item
			parent.replaceChild(li, sublist);
		}	
	}
}

