Timeline.Gantt = new Object();

Timeline.Gantt.create = function(elmt, taskelmt, bandInfos, taskInfo, orientation, unit) {
    return new Timeline.Gantt._Impl(elmt, taskelmt, bandInfos, taskInfo, orientation, unit);
};

Timeline.Gantt.createBandInfo = function(params) {
	var defaultTheme = Timeline.Gantt.Theme.create(Timeline.Platform.getDefaultLocale());
	Timeline.setDefaultTheme(defaultTheme);
	
    var theme = ("theme" in params) ? params.theme : Timeline.getDefaultTheme();
    
    var eventSource = ("eventSource" in params) ? params.eventSource : null;
    
    var ether = new Timeline.LinearEther({ 
        centersOn:          ("date" in params) ? params.date : new Date(),
        interval:           Timeline.DateTime.gregorianUnitLengths[params.intervalUnit],
        pixelsPerInterval:  params.intervalPixels
    });
    
    var etherPainter = new Timeline.GregorianEtherPainter({
        unit:       params.intervalUnit, 
        multiple:   ("multiple" in params) ? params.multiple : 1,
        theme:      theme,
        align:      ("align" in params) ? params.align : undefined
    });
    
    var layout = new Timeline.Gantt.SequentialLayout({
        eventSource:    eventSource,
        ether:          ether,
        showText:       ("showEventText" in params) ? params.showEventText : true,
        theme:          theme
    });
    
    var eventPainterParams = {
        showText:   ("showEventText" in params) ? params.showEventText : true,
        layout:     layout,
        showLineForNoText: false,
        theme:      theme
    };
    if ("trackHeight" in params) {
        eventPainterParams.trackHeight = params.trackHeight;
    }
    if ("trackGap" in params) {
        eventPainterParams.trackGap = params.trackGap;
    }
    var eventPainter = new Timeline.Gantt.TaskPainter(eventPainterParams);
    
    return {   
        width:          params.width,
        lengthStr:		params.lengthStr,
        eventSource:    eventSource,
        timeZone:       ("timeZone" in params) ? params.timeZone : 0,
        ether:          ether,
        etherPainter:   etherPainter,
        eventPainter:   eventPainter,
        type:			"band"	// Timeline band
    };
};

Timeline.Gantt.createTaskBandInfo = function(params) {
	var defaultTheme = Timeline.Gantt.Theme.create(Timeline.Platform.getDefaultLocale());
	Timeline.setDefaultTheme(defaultTheme);
	
    var theme = ("theme" in params) ? params.theme : Timeline.getDefaultTheme();
    
    var eventSource = ("eventSource" in params) ? params.eventSource : null;
    
    var ether = new Timeline.Gantt.TaskEther({ 
        centersOn:          ("taskIndex" in params) ? params.taskIndex : 0,
        interval:           Timeline.DateTime.gregorianUnitLengths[params.intervalUnit],
        pixelsPerInterval:  params.intervalPixels
    });
    
    var etherPainter = new Timeline.Gantt.TasklistEtherPainter({
        unit:       params.intervalUnit, 
        multiple:   ("multiple" in params) ? params.multiple : 1,
        theme:      theme,
        align:      ("align" in params) ? params.align : undefined
    });
    
    var layout = new Timeline.Gantt.TaskLayout({
        eventSource:    eventSource,
        ether:          ether,
        showText:       ("showEventText" in params) ? params.showEventText : true,
        theme:          theme
    });
    
    var eventPainterParams = {
        showText:   ("showEventText" in params) ? params.showEventText : true,
        layout:     layout,
        showLineForNoText: false,
        theme:      theme
    };
    if ("trackHeight" in params) {
        eventPainterParams.trackHeight = params.trackHeight;
    }
    if ("trackGap" in params) {
        eventPainterParams.trackGap = params.trackGap;
    }
    
    var eventPainter = new Timeline.Gantt.TaskInfoPainter(eventPainterParams);
    
    return {   
        width:          params.width,
        lengthStr:		params.lengthStr,
        eventSource:    eventSource,
        timeZone:       ("timeZone" in params) ? params.timeZone : 0,
        ether:          ether,
        etherPainter:   etherPainter,
        eventPainter:   eventPainter,
        type:			"task" // Tasklist band
    };	
}

/**
 * Timeline.Gantt Implementation
 * 
 * This should really be a subclass or clone of Timeline._Impl but I have yet to find
 * a reliable way of extending Timeline._Impl with the constructor.
 */

Timeline.Gantt._Impl = function(elmt, taskelmt, bandInfos, taskInfo, orientation, unit) {
    this._containerDiv = elmt;
    this._taskContainerDiv = taskelmt;
    
    this._bandInfos = bandInfos;
    this._taskInfo = taskInfo;
    this._orientation = orientation == null ? Timeline.HORIZONTAL : orientation;
    this._unit = (unit != null) ? unit : Timeline.NativeDateUnit;
    
    this._initialize();
};

Timeline.Gantt._Impl.prototype.dispose = function() {
    for (var i = 0; i < this._bands.length; i++) {
        this._bands[i].dispose();
    }
    this._bands = null;
    this._bandInfos = null;
    this._containerDiv.innerHTML = "";
};

Timeline.Gantt._Impl.prototype.getBandCount = function() {
    return this._bands.length;
};

Timeline.Gantt._Impl.prototype.getBand = function(index) {
    return this._bands[index];
};

Timeline.Gantt._Impl.prototype.layout = function() {
    this._distributeWidths();
};

Timeline.Gantt._Impl.prototype.paint = function() {
    for (var i = 0; i < this._bands.length; i++) {
        this._bands[i].paint();
    }
};

Timeline.Gantt._Impl.prototype.getDocument = function() {
    return this._containerDiv.ownerDocument;
};

Timeline.Gantt._Impl.prototype.addDiv = function(div, section) {
    if (section == "task") {
    	this._taskContainerDiv.appendChild(div);
    } else {
    	this._containerDiv.appendChild(div);
    }
};

Timeline.Gantt._Impl.prototype.removeDiv = function(div) {
    this._containerDiv.removeChild(div);
};

Timeline.Gantt._Impl.prototype.isHorizontal = function() {
    return this._orientation == Timeline.HORIZONTAL;
};

Timeline.Gantt._Impl.prototype.isVertical = function() {
    return this._orientation == Timeline.VERTICAL;
};

Timeline.Gantt._Impl.prototype.getPixelLength = function() {
    return this._orientation == Timeline.HORIZONTAL ? 
        this._containerDiv.offsetWidth : this._containerDiv.offsetHeight;
};

Timeline.Gantt._Impl.prototype.getPixelWidth = function() {
    return this._orientation == Timeline.VERTICAL ? 
        this._containerDiv.offsetWidth : this._containerDiv.offsetHeight;
};

Timeline.Gantt._Impl.prototype.getUnit = function() {
    return this._unit;
};

Timeline.Gantt._Impl.prototype.loadXML = function(url, f) {
    var tl = this;
    
    
    var fError = function(statusText, status, xmlhttp) {
        alert("Failed to load data xml from " + url + "\n" + statusText);
        tl.hideLoadingMessage();
    };
    var fDone = function(xmlhttp) {
        try {
            var xml = xmlhttp.responseXML;
            if (!xml.documentElement && xmlhttp.responseStream) {
                xml.load(xmlhttp.responseStream);
            } 
            f(xml, url);
        } finally {
            tl.hideLoadingMessage();
        }
    };
    
    this.showLoadingMessage();
    window.setTimeout(function() { Timeline.XmlHttp.get(url, fError, fDone); }, 0);
};

Timeline.Gantt._Impl.prototype.loadJSON = function(url, f) {
    var tl = this;
    
    
    var fError = function(statusText, status, xmlhttp) {
        alert("Failed to load json data from " + url + "\n" + statusText);
        tl.hideLoadingMessage();
    };
    var fDone = function(xmlhttp) {
        try {
            f(eval('(' + xmlhttp.responseText + ')'), url);
        } finally {
            tl.hideLoadingMessage();
        }
    };
    
    this.showLoadingMessage();
    window.setTimeout(function() { Timeline.XmlHttp.get(url, fError, fDone); }, 0);
};

// Extended to create a task list

Timeline.Gantt._Impl.prototype._initialize = function() {
    var containerDiv = this._containerDiv;
    var taskContainerDiv = this._taskContainerDiv;
    
    var doc = containerDiv.ownerDocument;
    
    containerDiv.className = 
        containerDiv.className.split(" ").concat("timeline-container").join(" ");
   	
   	taskContainerDiv.className = 
        containerDiv.className.split(" ").concat("timeline-task-container").join(" ");
        
    while (containerDiv.firstChild) {
        containerDiv.removeChild(containerDiv.firstChild);
    }
    
    //console.log("container: %s", taskContainerDiv);
    
    /*
     *  inserting copyright and link to simile
     */
    var elmtCopyright = Timeline.Graphics.createTranslucentImage(doc, Timeline.urlPrefix + (this.isHorizontal() ? "images/copyright-vertical.png" : "images/copyright.png"));
    elmtCopyright.className = "timeline-copyright";
    elmtCopyright.title = "Timeline (c) SIMILE - http://simile.mit.edu/timeline/";
    Timeline.DOM.registerEvent(elmtCopyright, "click", function() { window.location = "http://simile.mit.edu/timeline/"; });
    containerDiv.appendChild(elmtCopyright);
    

    /*
     *  creating task list
     */
    this._tasklist = new Timeline.Gantt._TaskBand(this, this._taskInfo, 0);

    /*
     *  creating bands
     */
    this._bands = [];
    
    for (var i = 0; i < this._bandInfos.length; i++) {
        	var band = new Timeline._Band(this, this._bandInfos[i], i);
        	this._bands.push(band);
    }
    this._distributeWidths();
    
    /*
     *  sync'ing bands
     */
    for (var i = 0; i < this._bandInfos.length; i++) {
        var bandInfo = this._bandInfos[i];
        if ("syncWith" in bandInfo) {
            this._bands[i].setSyncWithBand(
                this._bands[bandInfo.syncWith], 
                ("highlight" in bandInfo) ? bandInfo.highlight : false
            );
        }
    }
    

    
    /*
     *  creating loading UI
     */
    var message = Timeline.Graphics.createMessageBubble(doc);
    message.containerDiv.className = "timeline-message-container";
    containerDiv.appendChild(message.containerDiv);
    
    message.contentDiv.className = "timeline-message";
    message.contentDiv.innerHTML = "<img src='" + Timeline.urlPrefix + "images/progress-running.gif' /> Loading...";
    
    this.showLoadingMessage = function() { message.containerDiv.style.display = "block"; };
    this.hideLoadingMessage = function() { message.containerDiv.style.display = "none"; };
};

Timeline.Gantt._Impl.prototype._distributeWidths = function() {
    var length = this.getPixelLength();
    var width = this.getPixelWidth();

    var cumulativeWidth = 0;
    
    // Iterate through regular bands
    for (var i = 0; i < this._bands.length; i++) {
        
        var band = this._bands[i];
        var bandInfos = this._bandInfos[i];
        var widthString = bandInfos.width;
        
        var x = widthString.indexOf("%");
        if (x > 0) {
            var percent = parseInt(widthString.substr(0, x));
            var bandWidth = percent * width / 100;
        } else {
            var bandWidth = parseInt(widthString);
        }

       	band.setBandShiftAndWidth(cumulativeWidth, bandWidth);
        band.setViewLength(length);
        
        cumulativeWidth += bandWidth;
    }
    
    var cumulativeWidth = 0;
    
    var taskInfo = this._taskInfo;
    var widthString = taskInfo.width;
    
    var x = widthString.indexOf("%");
        if (x > 0) {
            var percent = parseInt(widthString.substr(0, x));
            var bandWidth = percent * width / 100;
        } else {
            var bandWidth = parseInt(widthString);
        }
    
    this._tasklist.setBandShiftAndWidth(cumulativeWidth, bandWidth);
    this._tasklist.setViewLength(length);
};

/*==================================================
 *  Timeline.Gantt._TaskBand
 *==================================================
 */
Timeline.Gantt._TaskBand = function(timeline, bandInfo, index) {
    this._timeline = timeline;
    this._bandInfo = bandInfo;
    this._index = index;
    
    this._locale = ("locale" in bandInfo) ? bandInfo.locale : Timeline.Platform.getDefaultLocale();
    this._timeZone = ("timeZone" in bandInfo) ? bandInfo.timeZone : 0;
    this._labeller = ("labeller" in bandInfo) ? bandInfo.labeller : 
        timeline.getUnit().createLabeller(this._locale, this._timeZone);
    
    this._dragging = false;
    this._changing = false;
    this._originalScrollSpeed = 5; // pixels
    this._scrollSpeed = this._originalScrollSpeed;
    this._onScrollListeners = [];
    
    var b = this;
    this._syncWithBand = null;
    this._syncWithBandHandler = function(band) {
        b._onHighlightBandScroll();
    };
    this._selectorListener = function(band) {
        b._onHighlightBandScroll();
    };
    
    /*
     *  Install a textbox to capture keyboard events
     */
    var inputDiv = this._timeline.getDocument().createElement("div");
    inputDiv.className = "timeline-band-input";
    this._timeline.addDiv(inputDiv, "task");
    
    this._keyboardInput = document.createElement("input");
    this._keyboardInput.type = "text";
    inputDiv.appendChild(this._keyboardInput);
    Timeline.DOM.registerEventWithObject(this._keyboardInput, "keydown", this, this._onKeyDown);
    Timeline.DOM.registerEventWithObject(this._keyboardInput, "keyup", this, this._onKeyUp);
    
    /*
     *  The band's outer most div that slides with respect to the timeline's div
     */
    this._div = this._timeline.getDocument().createElement("div");
    this._div.className = "timeline-gantt-taskband";
    this._timeline.addDiv(this._div, "task");
    
    Timeline.DOM.registerEventWithObject(this._div, "mousedown", this, this._onMouseDown);
    Timeline.DOM.registerEventWithObject(this._div, "mousemove", this, this._onMouseMove);
    Timeline.DOM.registerEventWithObject(this._div, "mouseup", this, this._onMouseUp);
    Timeline.DOM.registerEventWithObject(this._div, "mouseout", this, this._onMouseOut);
    Timeline.DOM.registerEventWithObject(this._div, "dblclick", this, this._onDblClick);
    
    /*
     *  The inner div that contains layers
     */
    this._innerDiv = this._timeline.getDocument().createElement("div");
    this._innerDiv.className = "timeline-gantt-taskband-inner";
    this._div.appendChild(this._innerDiv);
    
    /*
     *  Initialize parts of the band
     */
    this._ether = bandInfo.ether;
    bandInfo.ether.initialize(timeline);
        
    this._etherPainter = bandInfo.etherPainter;
    bandInfo.etherPainter.initialize(this, timeline);
    
    this._eventSource = bandInfo.eventSource;
    if (this._eventSource) {
        this._eventListener = {
            onAddMany: function() { b._onAddMany(); },
            onClear:   function() { b._onClear(); }
        }
        this._eventSource.addListener(this._eventListener);
    }
        
    this._eventPainter = bandInfo.eventPainter;
    bandInfo.eventPainter.initialize(this, timeline);
    
    this._decorators = ("decorators" in bandInfo) ? bandInfo.decorators : [];
    for (var i = 0; i < this._decorators.length; i++) {
        this._decorators[i].initialize(this, timeline);
    }
        
    this._bubble = null;
};

Timeline.Gantt._TaskBand.SCROLL_MULTIPLES = 5;

Timeline.Gantt._TaskBand.prototype.dispose = function() {
    this.closeBubble();
    
    if (this._eventSource) {
        this._eventSource.removeListener(this._eventListener);
        this._eventListener = null;
        this._eventSource = null;
    }
    
    this._timeline = null;
    this._bandInfo = null;
    
    this._labeller = null;
    this._ether = null;
    this._etherPainter = null;
    this._eventPainter = null;
    this._decorators = null;
    
    this._onScrollListeners = null;
    this._syncWithBandHandler = null;
    this._selectorListener = null;
    
    this._div = null;
    this._innerDiv = null;
    this._keyboardInput = null;
    this._bubble = null;
};

Timeline.Gantt._TaskBand.prototype.addOnScrollListener = function(listener) {
    this._onScrollListeners.push(listener);
};

Timeline.Gantt._TaskBand.prototype.removeOnScrollListener = function(listener) {
    for (var i = 0; i < this._onScrollListeners.length; i++) {
        if (this._onScrollListeners[i] == listener) {
            this._onScrollListeners.splice(i, 1);
            break;
        }
    }
};

Timeline.Gantt._TaskBand.prototype.setSyncWithBand = function(band, highlight) {
    if (this._syncWithBand) {
        this._syncWithBand.removeOnScrollListener(this._syncWithBandHandler);
    }
    
    this._syncWithBand = band;
    this._syncWithBand.addOnScrollListener(this._syncWithBandHandler);
    this._highlight = highlight;
    this._positionHighlight();
};

Timeline.Gantt._TaskBand.prototype.getLocale = function() {
    return this._locale;
};

Timeline.Gantt._TaskBand.prototype.getTimeZone = function() {
    return this._timeZone;
};

Timeline.Gantt._TaskBand.prototype.getLabeller = function() {
    return this._labeller;
};

Timeline.Gantt._TaskBand.prototype.getIndex = function() {
    return this._index;
};

Timeline.Gantt._TaskBand.prototype.getEther = function() {
    return this._ether;
};

Timeline.Gantt._TaskBand.prototype.getEtherPainter = function() {
    return this._etherPainter;
};

Timeline.Gantt._TaskBand.prototype.getEventSource = function() {
    return this._eventSource;
};

Timeline.Gantt._TaskBand.prototype.getEventPainter = function() {
    return this._eventPainter;
};

Timeline.Gantt._TaskBand.prototype.layout = function() {
    this.paint();
};

Timeline.Gantt._TaskBand.prototype.paint = function() {
    this._etherPainter.paint();
    this._paintDecorators();
    this._paintEvents();
};

Timeline.Gantt._TaskBand.prototype.softLayout = function() {
    this.softPaint();
};

Timeline.Gantt._TaskBand.prototype.softPaint = function() {
    this._etherPainter.softPaint();
    this._softPaintDecorators();
    this._softPaintEvents();
};

Timeline.Gantt._TaskBand.prototype.setBandShiftAndWidth = function(shift, width) {
    var inputDiv = this._keyboardInput.parentNode;
    var middle = shift + Math.floor(width / 2);

	// TaskBand only supports vertical scroll    	
    //console.log("setting width: %d and shift: %d", width, shift);

    this._div.style.top = shift + "px";
    this._div.style.height = width + "px";
    
    inputDiv.style.top = middle + "px";
    inputDiv.style.left = "-1em";
};

Timeline.Gantt._TaskBand.prototype.getViewWidth = function() {
    //if (this._timeline.isHorizontal()) {
    //    return this._div.offsetHeight;
    //} else {
        return this._div.offsetWidth;
    //}
};

Timeline.Gantt._TaskBand.prototype.setViewLength = function(length) {
    this._viewLength = length;
    this._recenterDiv();
    this._onChanging();
};

Timeline.Gantt._TaskBand.prototype.getViewLength = function() {
    return this._viewLength;
};

Timeline.Gantt._TaskBand.prototype.getTotalViewLength = function() {
    return Timeline.Gantt._TaskBand.SCROLL_MULTIPLES * this._viewLength;
};

Timeline.Gantt._TaskBand.prototype.getViewOffset = function() {
    return this._viewOffset;
};

/*
Timeline.Gantt._TaskBand.prototype.getMinDate = function() {
	console.log("Replace call with getMinTask");
	console.trace();
	//    return this._ether.pixelOffsetToDate(this._viewOffset);
};*/

Timeline.Gantt._TaskBand.prototype.getMinTask = function() {
	return this._ether.pixelOffsetToTask(this._viewOffset);
}

/*
Timeline.Gantt._TaskBand.prototype.getMaxDate = function() {
    console.log("Replace call with getMaxTask");
    console.trace();
    //	return this._ether.pixelOffsetToDate(this._viewOffset + Timeline.Gantt._TaskBand.SCROLL_MULTIPLES * this._viewLength);
};
*/

Timeline.Gantt._TaskBand.prototype.getMaxTask = function() {
	return this._ether.taskToPixelOffset(this._viewOffset + Timeline.Gantt._TaskBand.SCROLL_MULTIPLES * this._viewLength);
}

/*
Timeline.Gantt._TaskBand.prototype.getMinVisibleDate = function() {
    return this._ether.pixelOffsetToDate(0);
};

Timeline.Gantt._TaskBand.prototype.getMaxVisibleDate = function() {
    return this._ether.pixelOffsetToDate(this._viewLength);
};

Timeline.Gantt._TaskBand.prototype.getCenterVisibleDate = function() {
    return this._ether.pixelOffsetToDate(this._viewLength / 2);
};
*/

Timeline.Gantt._TaskBand.prototype.getMinVisibleTask = function() {
	return this._ether.pixelOffsetToTask(0);
}
Timeline.Gantt._TaskBand.prototype.getMaxVisibleTask = function() {
	return this._ether.pixelOffsetToTask(this._viewLength);
}
Timeline.Gantt._TaskBand.prototype.getCenterVisibleTask = function() {
	return this._ether.pixelOffsetToTask(this._viewLength / 2);
}


/*
Timeline.Gantt._TaskBand.prototype.setMinVisibleDate = function(date) {
    if (!this._changing) {
        this._moveEther(Math.round(-this._ether.dateToPixelOffset(date)));
    }
};

Timeline.Gantt._TaskBand.prototype.setMaxVisibleDate = function(date) {
    if (!this._changing) {
        this._moveEther(Math.round(this._viewLength - this._ether.dateToPixelOffset(date)));
    }
};

Timeline.Gantt._TaskBand.prototype.setCenterVisibleDate = function(date) {
    if (!this._changing) {
        this._moveEther(Math.round(this._viewLength / 2 - this._ether.dateToPixelOffset(date)));
    }
};
*/

Timeline.Gantt._TaskBand.prototype.setMinVisibleTask = function(taskIndex) {
	if (!this._changing) {
		this._moveEther(Math.round(-this._ether.taskToPixelOffset(taskIndex)));
	}
}

Timeline.Gantt._TaskBand.prototype.setMaxVisibleTask = function(taskIndex) {
    if (!this._changing) {
        this._moveEther(Math.round(this._viewLength - this._ether.taskToPixelOffset(taskIndex)));
    }	
}

/*
Timeline.Gantt._TaskBand.prototype.dateToPixelOffset = function(date) {
    return this._ether.dateToPixelOffset(date) - this._viewOffset;
};

Timeline.Gantt._TaskBand.prototype.pixelOffsetToDate = function(pixels) {
    return this._ether.pixelOffsetToDate(pixels + this._viewOffset);
};
*/
Timeline.Gantt._TaskBand.prototype.taskToPixelOffset = function(taskIndex) {
	return this._ether.taskToPixelOffset(taskIndex) - this._viewOffset;
}

Timeline.Gantt._TaskBand.prototype.pixelOffsetToTask = function(pixels) {
	return this._ether.pixelOffsetToTask(pixels + this._viewOffset);
}


Timeline.Gantt._TaskBand.prototype.createLayerDiv = function(zIndex) {
    var div = this._timeline.getDocument().createElement("div");
    div.className = "timeline-gantt-taskband-layer";
    div.style.zIndex = zIndex;
    this._innerDiv.appendChild(div);
    
    var innerDiv = this._timeline.getDocument().createElement("div");
    innerDiv.className = "timeline-gantt-taskband-layer-inner";
    if (Timeline.Platform.browser.isIE) {
        innerDiv.style.cursor = "move";
    } else {
        innerDiv.style.cursor = "-moz-grab";
    }
    div.appendChild(innerDiv);
    
    return innerDiv;
};

Timeline.Gantt._TaskBand.prototype.removeLayerDiv = function(div) {
    this._innerDiv.removeChild(div.parentNode);
};

Timeline.Gantt._TaskBand.prototype.closeBubble = function() {
    if (this._bubble != null) {
        this._bubble.close();
        this._bubble = null;
    }
};

Timeline.Gantt._TaskBand.prototype.openBubbleForPoint = function(pageX, pageY, width, height) {
    this.closeBubble();
    
    this._bubble = Timeline.Graphics.createBubbleForPoint(
        this._timeline.getDocument(), pageX, pageY, width, height);
        
    return this._bubble.content;
};
/*
Timeline.Gantt._TaskBand.prototype.scrollToCenter = function(date) {
    var pixelOffset = this._ether.dateToPixelOffset(date);
    if (pixelOffset < -this._viewLength / 2) {
        this.setCenterVisibleDate(this.pixelOffsetToDate(pixelOffset + this._viewLength));
    } else if (pixelOffset > 3 * this._viewLength / 2) {
        this.setCenterVisibleDate(this.pixelOffsetToDate(pixelOffset - this._viewLength));
    }
    this._autoScroll(Math.round(this._viewLength / 2 - this._ether.dateToPixelOffset(date)));
};
*/

Timeline.Gantt._TaskBand.prototype.scrollToCenter = function(taskIndex) {
    var pixelOffset = this._ether.taskToPixelOffset(taskIndex);
    if (pixelOffset < -this._viewLength / 2) {
        this.setCenterVisibleTask(this.pixelOffsetToTask(pixelOffset + this._viewLength));
    } else if (pixelOffset > 3 * this._viewLength / 2) {
        this.setCenterVisibleTask(this.pixelOffsetToTask(pixelOffset - this._viewLength));
    }
    this._autoScroll(Math.round(this._viewLength / 2 - this._ether.taskToPixelOffset(taskIndex)));
};


Timeline.Gantt._TaskBand.prototype._onMouseDown = function(innerFrame, evt, target) {
    this.closeBubble();
    
    console.log("MouseDown event");
    this._dragging = true;
    this._dragX = evt.clientX;
    this._dragY = evt.clientY;
};

Timeline.Gantt._TaskBand.prototype._onMouseMove = function(innerFrame, evt, target) {
    if (this._dragging) {
        var diffX = evt.clientX - this._dragX;
        var diffY = evt.clientY - this._dragY;
        
        this._dragX = evt.clientX;
        this._dragY = evt.clientY;
        //console.log("Dragged mouse Y direction pixels: %d", diffY);
        //this._moveEther(this._timeline.isHorizontal() ? diffX : diffY);
        this._moveEther(diffY);
        this._positionHighlight();
    }
};

Timeline.Gantt._TaskBand.prototype._onMouseUp = function(innerFrame, evt, target) {
    this._dragging = false;
    this._keyboardInput.focus();
};

Timeline.Gantt._TaskBand.prototype._onMouseOut = function(innerFrame, evt, target) {
    var coords = Timeline.DOM.getEventRelativeCoordinates(evt, innerFrame);
    coords.x += this._viewOffset;
    if (coords.x < 0 || coords.x > innerFrame.offsetWidth ||
        coords.y < 0 || coords.y > innerFrame.offsetHeight) {
        this._dragging = false;
    }
};

Timeline.Gantt._TaskBand.prototype._onDblClick = function(innerFrame, evt, target) {
    var coords = Timeline.DOM.getEventRelativeCoordinates(evt, innerFrame);
    var distance = coords.x - (this._viewLength / 2 - this._viewOffset);
    
    this._autoScroll(-distance);
};

Timeline.Gantt._TaskBand.prototype._onKeyDown = function(keyboardInput, evt, target) {
    if (!this._dragging) {
        switch (evt.keyCode) {
        case 27: // ESC
            break;
        case 37: // left arrow
        case 38: // up arrow
            this._scrollSpeed = Math.min(50, Math.abs(this._scrollSpeed * 1.05));
            this._moveEther(this._scrollSpeed);
            break;
        case 39: // right arrow
        case 40: // down arrow
            this._scrollSpeed = -Math.min(50, Math.abs(this._scrollSpeed * 1.05));
            this._moveEther(this._scrollSpeed);
            break;
        default:
            return true;
        }
        this.closeBubble();
        
        Timeline.DOM.cancelEvent(evt);
        return false;
    }
    return true;
};

Timeline.Gantt._TaskBand.prototype._onKeyUp = function(keyboardInput, evt, target) {
    if (!this._dragging) {
        this._scrollSpeed = this._originalScrollSpeed;
        
        switch (evt.keyCode) {
        case 35: // end
            this.setCenterVisibleDate(this._eventSource.getLatestDate());
            break;
        case 36: // home
            this.setCenterVisibleDate(this._eventSource.getEarliestDate());
            break;
        case 33: // page up
            this._autoScroll(this._timeline.getPixelLength());
            break;
        case 34: // page down
            this._autoScroll(-this._timeline.getPixelLength());
            break;
        default:
            return true;
        }
        
        this.closeBubble();
        
        Timeline.DOM.cancelEvent(evt);
        return false;
    }
    return true;
};

Timeline.Gantt._TaskBand.prototype._autoScroll = function(distance) {
    var b = this;
    var a = Timeline.Graphics.createAnimation(function(abs, diff) {
        b._moveEther(diff);
    }, 0, distance, 1000);
    a.run();
};

Timeline.Gantt._TaskBand.prototype._moveEther = function(shift) {
    this.closeBubble();
    
    this._viewOffset += shift;
    this._ether.shiftPixels(-shift);
    //if (this._timeline.isHorizontal()) {
    //    this._div.style.left = this._viewOffset + "px";
    //} else {
        this._div.style.top = this._viewOffset + "px";
    //}
    
    if (this._viewOffset > -this._viewLength * 0.5 ||
        this._viewOffset < -this._viewLength * (Timeline.Gantt._TaskBand.SCROLL_MULTIPLES - 1.5)) {
        
        this._recenterDiv();
    } else {
        this.softLayout();
    }
    
    this._onChanging();
}

Timeline.Gantt._TaskBand.prototype._onChanging = function() {
    this._changing = true;

    this._fireOnScroll();
    this._setSyncWithBandDate();
    
    this._changing = false;
};

Timeline.Gantt._TaskBand.prototype._fireOnScroll = function() {
    for (var i = 0; i < this._onScrollListeners.length; i++) {
        this._onScrollListeners[i](this);
    }
};

Timeline.Gantt._TaskBand.prototype._setSyncWithBandDate = function() {
    if (this._syncWithBand) {
        var centerDate = this._ether.pixelOffsetToDate(this.getViewLength() / 2);
        this._syncWithBand.setCenterVisibleDate(centerDate);
    }
};

/*
Timeline.Gantt._TaskBand.prototype._onHighlightBandScroll = function() {
    if (this._syncWithBand) {
        var centerDate = this._syncWithBand.getCenterVisibleDate();
        var centerPixelOffset = this._ether.dateToPixelOffset(centerDate);
        
        this._moveEther(Math.round(this._viewLength / 2 - centerPixelOffset));
        
        if (this._highlight) {
            this._etherPainter.setHighlight(
                this._syncWithBand.getMinVisibleDate(), 
                this._syncWithBand.getMaxVisibleDate());
        }
    }
};*/

Timeline.Gantt._TaskBand.prototype._onHighlightBandScroll = function() {
    if (this._syncWithBand) {
        var centerDate = this._syncWithBand.getCenterVisibleDate();
        var centerPixelOffset = this._ether.taskToPixelOffset(centerDate);
        
        this._moveEther(Math.round(this._viewLength / 2 - centerPixelOffset));
        
        if (this._highlight) {
            this._etherPainter.setHighlight(
                this._syncWithBand.getMinVisibleDate(), 
                this._syncWithBand.getMaxVisibleDate());
        }
    }
};


Timeline.Gantt._TaskBand.prototype._onAddMany = function() {
    this._paintEvents();
};

Timeline.Gantt._TaskBand.prototype._onClear = function() {
    this._paintEvents();
};

Timeline.Gantt._TaskBand.prototype._positionHighlight = function() {
    if (this._syncWithBand) {
        var startDate = this._syncWithBand.getMinVisibleDate();
        var endDate = this._syncWithBand.getMaxVisibleDate();
        
        if (this._highlight) {
            this._etherPainter.setHighlight(startDate, endDate);
        }
    }
};

Timeline.Gantt._TaskBand.prototype._recenterDiv = function() {
    this._viewOffset = -this._viewLength * (Timeline.Gantt._TaskBand.SCROLL_MULTIPLES - 1) / 2;
    /*if (this._timeline.isHorizontal()) {
        this._div.style.left = this._viewOffset + "px";
        this._div.style.width = (Timeline.Gantt._TaskBand.SCROLL_MULTIPLES * this._viewLength) + "px";
    } else {*/
        this._div.style.top = this._viewOffset + "px";
        this._div.style.height = (Timeline.Gantt._TaskBand.SCROLL_MULTIPLES * this._viewLength) + "px";
    //}
    this.layout();
};

Timeline.Gantt._TaskBand.prototype._paintEvents = function() {
    this._eventPainter.paint();
};

Timeline.Gantt._TaskBand.prototype._softPaintEvents = function() {
    this._eventPainter.softPaint();
};

Timeline.Gantt._TaskBand.prototype._paintDecorators = function() {
    for (var i = 0; i < this._decorators.length; i++) {
        this._decorators[i].paint();
    }
};

Timeline.Gantt._TaskBand.prototype._softPaintDecorators = function() {
    for (var i = 0; i < this._decorators.length; i++) {
        this._decorators[i].softPaint();
    }
};

