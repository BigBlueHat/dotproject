var callback = {
	success: function() { console.log('success'); },
	failure: function() { console.log('fail'); },
	scope:	this
};


WbsList.parser = function(container) 
{
	console.log('INIT: WbsList.parser');
	this._container = container;
	this._depth = 0;
	
	
	this._json = {
		tasks: [],
		project: 'Test'
	};
	
	this.parseList(this._container, this._json.tasks);
	
	console.dir(this._json.tasks);
	
	var jsonStr = YAHOO.lang.JSON.stringify(this._json); 
	YAHOO.util.Connect.asyncRequest('POST', '/test/wbs/save', callback, 'json='+jsonStr);
}

WbsList.parser.parse = function(container_id)
{
	console.log('Parsing container: ' + container_id);
	var container = document.getElementById(container_id);

	window.wbsparser = new WbsList.parser(container);	
}

WbsList.parser.prototype.parseList = function(ul, list_reference)
{

	this._depth++;
	console.log('Parsing list at depth: ' + this._depth);
	
	var i;
	
	for (i = 0; i < ul.childNodes.length; i++)
	{
		var current = ul.childNodes[i];
		
		if (current.tagName == 'UL') {
			console.log('Parsing list node');
			var sub_tree = new Array();
			list_reference.push(sub_tree);
			
			this.parseList(current, sub_tree);
		}
		
		if (current.tagName == 'LI') {
			console.log('Parsing leaf node');
			
			this.parseItem(current, list_reference);
		}
	}
	
	this._depth--;
	
}

WbsList.parser.prototype.parseItem = function(li, list_reference)
{
	var task_name = WbsList.item.getValue(li);
	console.log('WbsList.parser: Item: ' + task_name);
	list_reference.push(task_name);
}