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
	
    regEx = sPid.toString();
    regEx = regEx.replace( '\(', '\\(');
    regEx = regEx.replace( '\)', '\\.(\\d)+\\)');
    
    var oRe = new RegExp("^" + regEx + "-");
    var oReBogus = new RegExp("^" + regEx + "-somethingbogus");
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
			    oReUcExp = (oRow.id).toString();
			    oReUcExp = oReUcExp.replace( '\(', '\\(');
			    oReUcExp = oReUcExp.replace( '\)', '\\.(\\d)+\\)');
			    oReUc = new RegExp("^" + oReUcExp + "-");
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
    
    regEx = sPid.toString();
    regEx = regEx.replace( '\(', '\\(');
    regEx = regEx.replace( '\)', '\\.(\\d)+\\)');
    var oRe = new RegExp("^" + regEx + "-");

/*		img = document.getElementById(sPid + '_img');
		if (img)
			img.src = "images/icons/collapse.gif";
*/

		for (var i = 0; i < oRows.length; i++) {
        var oRow = oRows.item(i);
        if (oRe.test(oRow.id)) {
					img = document.getElementById(oRow.id + '_img'); 
						_dpSetRowCollapsed(oRow, true, img);
            oRow.style.display = 'none';
						dpCollapseNode(oRow.id);
												
//						if (img)
//							img.src = "images/icons/expand.gif";

				}
    }
}

// collapse all nodes
function dpCollapseAll() {
    var oRows = table.rows;
    // Regular expression child nodes
    var oRe = /^node\(\d+\.\)(-\d+)+/;
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
	// "Cleaner" Solution to adding rows add in IE
	// ROW
	//pull out row properties and remove dangling white space
	tr_prop_str = html.substring(html.indexOf('<tr') + 3, html.indexOf('>'));
	tr_prop_str = tr_prop_str.replace(/^\s+|\s+$/g, "");
	// divide up string between property name and value
	tr_prop_arr = tr_prop_str.split('=');
	tr_pairs = new Array();
	//put ALL property names/values into an seperate array using loop
	for(j = 0; j < tr_prop_arr.length; j++){ 
	  tr_prop_arr[j].replace(/^\s+|\s+$/g, "");
	  if((tr_prop_arr[j]).length == 0){
	    //do nothing
	  }
	  //first name or last value
	  else if (j == 0 || j ==  (tr_prop_arr.length - 1)){
	    temp_str = tr_prop_arr[j];
	    temp_str = temp_str.replace(/^\"|\"$/g, "");
	    tr_pairs.push(temp_str);
	  }
	  // value/name substring
	  else {
	    // get value, trim any white space from ends, trim any quotation marks, add to array
	    temp_str = (tr_prop_arr[j].substring(0,tr_prop_arr[j].lastIndexOf(' '))).replace(/^\s+|\s+$/g, "");
	    temp_str = temp_str.replace(/^\"|\"$/g, "");
	    tr_pairs.push(temp_str);
	    //get name, add to array
	    temp_str = tr_prop_arr[j].substring(tr_prop_arr[j].lastIndexOf(' ')+1);
	    tr_pairs.push(temp_str);
	  }
	  
	}
	//insert row and set properties
	row = table.insertRow(pos);
	for(j = 0; j < tr_pairs.length; j=j+2){
	  if(j+1 < tr_pairs.length && tr_pairs[j+1].length != 0){
	    row.setAttribute(tr_pairs[j], tr_pairs[j+1]);
	  }
	  else{
	    row.setAttribute(tr_pairs[j]);
	  }
	}
	
	// CELLS
	// get substring for cells, remove dangling white space, loop through cells
	tds_str = (html.substring(html.indexOf('<td') , html.lastIndexOf('</td>'))).replace(/^\s+|\s+$/g, "");
	tds_arr = tds_str.split('</td>');
	for(i = 0; i < tds_arr.length; i++) {
	  //pull out cell properties, remove dangling white space, pull out cell text
	  td_prop_str = tds_arr[i].substring(tds_arr[i].indexOf('<td') + 3, tds_arr[i].indexOf('>'));
	  td_prop_str = td_prop_str.replace(/^\s+|\s+$/g, "");
	  
	  // divide up string between property name and value to get ALL cell property names/values into an array
	  td_prop_arr = td_prop_str.split('=');
	  td_pairs = new Array();
	  for(j = 0; j < td_prop_arr.length; j++){ 
	    td_prop_arr[j].replace(/^\s+|\s+$/g, "");
	    if((td_prop_arr[j].toString()).length == 0){
	      //do nothing
	    }
	    //first name or last value
	    else if (j == 0 || j ==  (td_prop_arr.length - 1)){
	      temp_str = td_prop_arr[j];
	      temp_str = temp_str.replace(/^\"|\"$/g, "");
	      td_pairs.push(temp_str);
	      
	    }
	    // value/name substring
	    else {
	      // get value, trim any white space from ends, trim any quotation marks, add to array
	      temp_str = (td_prop_arr[j].substring(0,td_prop_arr[j].lastIndexOf(' '))).replace(/^\s+|\s+$/g, "");
	      temp_str = temp_str.replace(/^\"|\"$/g, "");
	      td_pairs.push(temp_str);
	      //get name, add to array
	      temp_str = td_prop_arr[j].substring(td_prop_arr[j].lastIndexOf(' ')+1);
	      td_pairs.push(temp_str);
	    }
	    
	  }
	  
	  //insert cell and set properties
	  cell = row.insertCell(i);
	  for(j = 0; j < td_pairs.length; j=j+2){
	    if(j+1 < td_pairs.length){
	      cell.setAttribute(td_pairs[j], td_pairs[j+1]);
	    }
	    else{
	      cell.setAttribute(td_pairs[j]);
	    }
	  }
	  
	  //pull out cell text and set text in cell
	  td_text_str = tds_arr[i].substring(tds_arr[i].indexOf('>')+1);
	  cell.innerHTML = td_text_str;
	}
	
	// row.innerHTML = html; // doesn't work in IE
	//table.rows.item(pos).outerHTML = html;
	
	return row;
}

function processReqChange() {
	// only if req shows "loaded"
	
	if (req.readyState == 4) {
		// only if "OK"
		if (req.status == 200) {				
			ret = req.responseText;
//			alert('Returned: ' + ret);
			tasks = ret.substring(0, ret.length - 6).split('[][][]');
			for (task_num = tasks.length - 1; task_num >= 0; task_num--)
			{
				t = tasks[task_num].split('---');
				parent_id = t[0];
				row_data = t[1];

				rowInsert = document.getElementById(parent_id); //.rowIndex;
				table = document.getElementById(parent_id).parentNode.parentNode;
				row = insertNewRow(rowInsert, row_data);

				loadedTasks[parent_id] = true;
			}
		} else {
			return false
		}
	}
}
