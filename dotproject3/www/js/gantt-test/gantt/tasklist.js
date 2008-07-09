/*
 *	Task list generation
 */

var Tasklist = new Object();

Tasklist.create = function(elmt, bandInfos) {
	return new Tasklist._Impl(elmt, bandInfos);
}


Tasklist._Impl = function(elmt, bandInfos) {
    this._containerDiv = elmt;
    this._bandInfos = bandInfos;

    this._initialize();	
}


Tasklist._Impl.prototype._initialize = function() {
    
    var containerDiv = this._containerDiv;
    var doc = containerDiv.ownerDocument;
    
    containerDiv.className = 
        containerDiv.className.split(" ").concat("tasklist-container").join(" ");
    
    var tasklist = doc.createElement("ul");
    var taskitem = doc.createElement("li");
    var desc = doc.createTextNode("Task");
    
    taskitem.appendChild(desc);
    tasklist.appendChild(taskitem);
    containerDiv.appendChild(tasklist);
}