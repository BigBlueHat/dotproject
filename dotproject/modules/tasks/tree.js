/*
 * provide functions for expanding and colllapsing elements of the
 * task tree
 */


// the property for setting rows visible in this browser
var _visRow = 'table-row';
var loadedTasks = new Array();
var table;
var req;

// determine which identifier should be used for _visRow
function _dpTreeInit() {
     var tr = document.createElement('tr');
     try {
         tr.style.display = _visRow;
     } catch (e) {
         // fall back, to block display (for ie)
         _visRow = 'block';
     }
}
_dpTreeInit();

function _dpSetRowCollapsed(oRow, bCollapsed, img) {
    oRow._dpCollapsed = bCollapsed;

    try {
        var sImg = "images/icons/" + 
            (bCollapsed ? "expand" : "collapse") + ".gif";
        var oImg = img;
        
        oImg.src = sImg;
    } catch (e) {
        // quietly ignore.. we are trying to modify a row without the image.
    }
}

/** Expand a given node
 *
 * @param oRows	all rows in the table
 * @param sPid the parent id (task_id) of the task, being expanded.
 */
function dpExpandNode(sPid) {
	oRows = table.rows;
    var oRe = new RegExp("^" + sPid + "-");
    var oReBogus = new RegExp("^" + sPid + "-somethingbogus");
    var oReUc = oReBogus;

    if (loadedTasks[sPid] == undefined)
    {
//alert('index.php?m=tasks&a=listtasks&suppressHeaders=1&table=1&node_id=' + sPid);
    	loadXMLDoc('index.php?m=tasks&a=listtasks&suppressHeaders=1&table=1&node_id=' + sPid);
    }
    else
    {
	    for (var i = 0; i < oRows.length; i++) {
	        var oRow = oRows.item(i);
	
	        if (oRe.test(oRow.id)) {
	            if (!oReUc.test(oRow.id)) {
	                oRow.style.display = _visRow;
	                if (oRow._dpCollapsed) {
	                    oReUc = new RegExp("^" + oRow.id + "-");
	                } else {
	                    oReUc = oReBogus;
	                }
	            }
	        }
	    }
    }
}

// collapse a given node
function dpCollapseNode(sPid) {
	oRows = table.rows;
    var oRe = new RegExp("^" + sPid + "-");

    for (var i = 0; i < oRows.length; i++) {
        var oRow = oRows.item(i);

        if (oRe.test(oRow.id)) {
            oRow.style.display = 'none';
        }
    }
}

// collapse all nodes
function dpCollapseAll() {
    var oRows = table.rows;
    // Regular expression child nodes
    var oRe = /^node-\d+(-\d+)+/;
    for (var i = 0; i < oRows.length; i++) {
        var oRow = oRows.item(i);
        _dpSetRowCollapsed(oRow, true);

        if (oRe.test(oRow.id)) {
            oRow.style.display = 'none';
        }
    }
}

// expand all nodes
function dpExpandAll() {
    var oRows = table.rows;

    for (var i = 0; i < oRows.length; i++) {
        _dpSetRowCollapsed(oRows.item(i), false);
        oRows.item(i).style.display = _visRow;
    }

    return false;
}

function dpToggleTree() {
    table._dpTreeCollapse = !table._dpTreeCollapse;

    if (table._dpTreeCollapse) {
        dpCollapseAll(table);
    } else {
        dpExpandAll(table);
    }
}

function dpToggleNode(img) { // oRow
	oRow = img.parentNode.parentNode;
	table = oRow.parentNode.parentNode;

	if (oRow._dpCollapsed == undefined)
		oRow._dpCollapsed = true;
	
	if (oRow._dpCollapsed == true)
		dpExpandNode(oRow.id);
	else
		dpCollapseNode(oRow.id);

	_dpSetRowCollapsed(oRow, !oRow._dpCollapsed, img);
}

// AJAX stuff
function loadXMLDoc(url) {
	
	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
		req.onreadystatechange = processReqChange;
		req.open("GET", url, true);
		req.send(null);
		
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		req = new ActiveXObject("Microsoft.XMLHTTP");

		if (req) {
			req.onreadystatechange = processReqChange;
			req.open("GET", url, true);
			req.send();
		}
	}
}

function insertNewRow(row, html)
{
	table = row.parentNode.parentNode;
	pos = row.rowIndex + 1;
	row = table.insertRow(pos);
	tr_id = html.substring(4, html.indexOf('>'));
	attrName = tr_id.substring(0, tr_id.indexOf('='));
	attrValue = tr_id.substring(tr_id.indexOf('=') + 1);
	attrValue = attrValue.substring(1, attrValue.length-1);
	row.setAttribute(attrName, attrValue);
//alert(html);
	// row.innerHTML = html; // doesn't work in IE
	// DIRTY HACK for IE
	tds = html.split('</td>');
  for(i = 0; i < tds.length; i++)
	{
		cell = row.insertCell(i);
		// Get content.
		td = tds[i].substring(tds[i].indexOf('<td') + 3);
		attr = td.substring(0, td.indexOf('>'));
		attr = attr.replace(/^\s+|\s+$/g, "");
		// Parse td attributes
		if (attr != '')
		{
			attrs = attr.split(' ');
			for(j = 0; j < attrs.length; j++)
			{
				attrName = attrs[j].substring(0, attrs[j].indexOf('='));
				attrName = attrName.replace(/^\s+|\s+$/g, "");
				if (attrName == '')
					continue;
				attrValue = attrs[j].substring(attrs[j].indexOf('=') + 1);
				attrValue = attrValue.substring(1, attrValue.length-1);
				cell.setAttribute(attrName, attrValue);
			}
		}
		td = td.substring(td.indexOf('>') + 1);
		cell.innerHTML = td;
	}
	//table.rows.item(pos).outerHTML = html;
	
	return row;
}

function processReqChange() {
	// only if req shows "loaded"
	
	if (req.readyState == 4) {
		// only if "OK"
		if (req.status == 200) {				
			ret = req.responseText;
			// alert('Returned: ' + ret);
			tasks = ret.substring(0, ret.length - 6).split('[][][]');
			for (task_num = 0; task_num < tasks.length; task_num++)
			{
				t = tasks[task_num].split('---');

				node_id = t[1].substring(t[1].indexOf('id')+4);
				node_id = node_id.substring(0, node_id.indexOf('\''));
				
			//	t[1] = t[1].substring(t[1].indexOf('\n')+2, t[1].length - 5);
			//	t[1] = t[1].substring(t[1].indexOf('\n'));
			
				parent_id = t[0].substring(0, t[0].lastIndexOf('-'));
				rowInsert = document.getElementById(parent_id); //.rowIndex;
				table = document.getElementById(parent_id).parentNode.parentNode;
				row = insertNewRow(rowInsert, t[1]);
				row.setAttribute('id', t[0]);

				loadedTasks[parent_id] = true;
			}
		} else {
			return false
		}
	}
}
