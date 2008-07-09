/*==================================================
 *  Gantt Task Painter
 *==================================================
 */
Timeline.Gantt.TaskPainter = new Object();

Timeline.Gantt._layout = null;

Timeline.Gantt.TaskPainter = function(params) {
    this._params = params;
    this._theme = params.theme;
    this._layout = params.layout;
    
    this._showText = params.showText;
    this._showLineForNoText = ("showLineForNoText" in params) ? 
        params.showLineForNoText : params.theme.event.instant.showLineForNoText;
        
    this._filterMatcher = null;
    this._highlightMatcher = null;
};

Timeline.Gantt.TaskPainter.prototype.initialize = function(band, timeline) {
    this._band = band;
    this._timeline = timeline;

    this._layout.initialize(band, timeline);
    
    this._eventLayer = null;
    this._highlightLayer = null;
};

Timeline.Gantt.TaskPainter.prototype.getLayout = function() {
    return this._layout;
};

Timeline.Gantt.TaskPainter.prototype.setLayout = function(layout) {
    this._layout = layout;
};

Timeline.Gantt.TaskPainter.prototype.getFilterMatcher = function() {
    return this._filterMatcher;
};

Timeline.Gantt.TaskPainter.prototype.setFilterMatcher = function(filterMatcher) {
    this._filterMatcher = filterMatcher;
};

Timeline.Gantt.TaskPainter.prototype.getHighlightMatcher = function() {
    return this._highlightMatcher;
};

Timeline.Gantt.TaskPainter.prototype.setHighlightMatcher = function(highlightMatcher) {
    this._highlightMatcher = highlightMatcher;
};

Timeline.Gantt.TaskPainter.prototype.paint = function() {
    
    var eventSource = this._band.getEventSource();
    if (eventSource == null) {
        return;
    }
    
    if (this._highlightLayer != null) {
        this._band.removeLayerDiv(this._highlightLayer);
    }
    this._highlightLayer = this._band.createLayerDiv(105);
    this._highlightLayer.setAttribute("name", "event-highlights");
    this._highlightLayer.style.display = "none";
    
    if (this._eventLayer != null) {
        this._band.removeLayerDiv(this._eventLayer);
    }
    this._eventLayer = this._band.createLayerDiv(110);
    this._eventLayer.setAttribute("name", "events");
    this._eventLayer.style.display = "none";
    
    var minDate = this._band.getMinDate();
    var maxDate = this._band.getMaxDate();
    
    var doc = this._timeline.getDocument();
    
    var p = this;
    var eventLayer = this._eventLayer;
    var highlightLayer = this._highlightLayer;
    
    var showText = this._showText;
    var theme = this._params.theme;
    var eventTheme = theme.event;
    var trackOffset = eventTheme.track.offset;
    var trackHeight = ("trackHeight" in this._params) ? this._params.trackHeight : eventTheme.track.height;
    var trackGap = ("trackGap" in this._params) ? this._params.trackGap : eventTheme.track.gap;
    
    /**
     * appendIcon
     */
    var appendIcon = function(evt, div) {
        var icon = evt.getIcon();
        var img = Timeline.Graphics.createTranslucentImage(
            doc, icon != null ? icon : eventTheme.instant.icon
        );
        img.style.height = trackHeight + "em";
        div.appendChild(img);
        div.style.cursor = "pointer";
        
        Timeline.DOM.registerEvent(div, "mousedown", function(elmt, domEvt, target) {
            p._onClickInstantEvent(img, domEvt, evt);
        });
    };
    
    /**
     * createHighlightDiv
     */
    var createHighlightDiv = function(highlightIndex, startPixel, length, highlightOffset, highlightWidth) {
        if (highlightIndex >= 0) {
            var color = eventTheme.highlightColors[Math.min(highlightIndex, eventTheme.highlightColors.length - 1)];
            
            var div = doc.createElement("div");
            div.style.position = "absolute";
            div.style.overflow = "hidden";
            div.style.left = (startPixel - 3) + "px";
            div.style.width = (length + 6) + "px";
            div.style.top = highlightOffset + "em";
            div.style.height = highlightWidth + "em";
            div.style.background = color;
            //Timeline.Graphics.setOpacity(div, 50);
            
            highlightLayer.appendChild(div);
        }
    };
    
    /**
     * createInstantDiv
     */
    var createInstantDiv = function(evt, startPixel, endPixel, streamOffset, highlightIndex, highlightOffset, highlightWidth) {
        if (evt.isImprecise()) { // imprecise time
            var length = Math.max(endPixel - startPixel, 1);
        
            var divImprecise = doc.createElement("div");
            divImprecise.style.position = "absolute";
            divImprecise.style.overflow = "hidden";
            
            divImprecise.style.top = streamOffset;
            divImprecise.style.height = trackHeight + "em";
            divImprecise.style.left = startPixel + "px";
            divImprecise.style.width = length + "px";
            
            divImprecise.style.background = eventTheme.instant.impreciseColor;
            if (eventTheme.instant.impreciseOpacity < 100) {
                Timeline.Graphics.setOpacity(divImprecise, eventTheme.instant.impreciseOpacity);
            }
            
            eventLayer.appendChild(divImprecise);
        }
        
        var div = doc.createElement("div");
        div.style.position = "absolute";
        div.style.overflow = "hidden";
        eventLayer.appendChild(div);
        
        var foreground = evt.getTextColor();
        var background = evt.getColor();
        
        var realign = -8; // shift left so that icon is centered on startPixel
        var length = 16;
        if (showText) {
            div.style.width = eventTheme.label.width + "px";
            div.style.color = foreground != null ? foreground : eventTheme.label.outsideColor;
            
            appendIcon(evt, div);
            div.appendChild(doc.createTextNode(evt.getText()));
        } else {
            if (p._showLineForNoText) {
                div.style.width = "1px";
                div.style.borderLeft = "1px solid " + (background != null ? background : eventTheme.instant.lineColor);
                realign = 0; // no shift
                length = 1;
            } else {
                appendIcon(evt, div);
            }
        }
        
        div.style.top = streamOffset;
        div.style.height = trackHeight + "em";
        div.style.left = (startPixel + realign) + "px";
        
        createHighlightDiv(highlightIndex, (startPixel + realign), length, highlightOffset, highlightWidth);
    };
    
    /**
     * Append end triangles to summary group
     */
    var appendGroupEnds = function(evt, div) {
        var icon = evt.getIcon();
        
        var leftimg = Timeline.Graphics.createTranslucentImage(
            doc, "/img/summary-left.png"
        );
        
        var rightimg = Timeline.Graphics.createTranslucentImage(
            doc, "/img/summary-right.png"
        );
        
        leftimg.style.position = "absolute";
        leftimg.style.top = (trackHeight / 2) + "em";
        leftimg.style.left = "0px";
        
        rightimg.style.position = "absolute";
        rightimg.style.top = (trackHeight / 2) + "em";
        rightimg.style.right = "0px";
        div.appendChild(leftimg);
        div.appendChild(rightimg);

    }; 
    
    /**
     * createSummaryDiv
     */
    var createSummaryDiv = function(evt, startPixel, endPixel, streamOffset) {  
        var length = Math.max(endPixel - startPixel, 1);
        
        var startPixel2 = startPixel;
        var endPixel2 = endPixel;
        
        var foreground = evt.getTextColor();
        var outside = true;
        
        var percentComplete = evt.getPercentComplete();
        
        if (startPixel2 <= endPixel2) {
            length = Math.max(endPixel2 - startPixel2, 1);
            outside = !(length > eventTheme.label.width);
            
            div = doc.createElement("div");
            div.style.position = "absolute";
            
            div.style.top = streamOffset;
            
            div.style.height = trackHeight / 2 + "em";
            //div.style.height = trackHeight + "em";
            div.style.left = startPixel2 + "px";
            div.style.width = length + "px";
            
            var background = evt.getColor();
            
            div.style.background = background != null ? background : eventTheme.summary.color;
            
            /* summary does not support opacity setting.
            */
            
            // Create progress div
            progressWidth = (length * (percentComplete / 100));
            
            pdiv = doc.createElement("div");
          	pdiv.style.position = "absolute";
          
            pdiv.style.height =  trackHeight / 4 + "em";
            pdiv.style.left = eventTheme.progress.offset + "em";
            pdiv.style.top = trackHeight / 8 + "em";
            pdiv.style.width = progressWidth + "px";
            pdiv.style.background = eventTheme.summary.progressColor;
            
            div.appendChild(pdiv);
            
            
            appendGroupEnds(evt, div);
            
            eventLayer.appendChild(div);
            
        } else {
        	// zero length task
            var temp = startPixel2;
            startPixel2 = endPixel2;
            endPixel2 = temp;
        }
        if (div == null) {
            console.log(evt);
        }
        //attachClickEvent(div);
        
        /* showtext block removed for testing */
        
        //createHighlightDiv(highlightIndex, startPixel, endPixel - startPixel, highlightOffset, highlightWidth);
    };
    
    var createDurationDiv = function(evt, startPixel, endPixel, streamOffset, highlightIndex, highlightOffset, highlightWidth, percentComplete) {
        var attachClickEvent = function(elmt) {
            elmt.style.cursor = "pointer";
            Timeline.DOM.registerEvent(elmt, "mousedown", function(elmt, domEvt, target) {
                p._onClickDurationEvent(domEvt, evt, target);
            });
        };
        
        var length = Math.max(endPixel - startPixel, 1);
        if (evt.isImprecise()) { // imprecise time
            var div = doc.createElement("div");
            div.style.position = "absolute";
            div.style.overflow = "hidden";
            
            div.style.top = streamOffset;
            div.style.height = trackHeight + "em";
            div.style.left = startPixel + "px";
            div.style.width = length + "px";
            
            div.style.background = eventTheme.duration.impreciseColor;
            if (eventTheme.duration.impreciseOpacity < 100) {
                Timeline.Graphics.setOpacity(div, eventTheme.duration.impreciseOpacity);
            }
            
            eventLayer.appendChild(div);
            
            var startDate = evt.getLatestStart();
            var endDate = evt.getEarliestEnd();
            
            var startPixel2 = Math.round(p._band.dateToPixelOffset(startDate));
            var endPixel2 = Math.round(p._band.dateToPixelOffset(endDate));
        } else {
            var startPixel2 = startPixel;
            var endPixel2 = endPixel;
        }
        
        var foreground = evt.getTextColor();
        var outside = true;
        if (startPixel2 <= endPixel2) {
            length = Math.max(endPixel2 - startPixel2, 1);
            outside = !(length > eventTheme.label.width);
            
            div = doc.createElement("div");
            div.style.position = "absolute";
            div.style.overflow = "hidden";
            
            div.style.top = streamOffset;
            div.style.height = trackHeight + "em";
            div.style.left = startPixel2 + "px";
            div.style.width = length + "px";
            
            var background = evt.getColor();
            
            div.style.background = background != null ? background : eventTheme.duration.color;
            if (eventTheme.duration.opacity < 100) {
                Timeline.Graphics.setOpacity(div, eventTheme.duration.opacity);
            }
            
            // Progress line
            //var percentComplete = 30;
            var plineHeight = trackHeight / 4;
            progressWidth = (length * (percentComplete / 100));
            pdiv = doc.createElement("div");
          	pdiv.style.position = "absolute";
            //pdiv.style.overflow = "hidden";
           
            //pdiv.style.top = (trackHeight / 2) - (plineHeight / 2) + "em";
            //pdiv.style.height = plineHeight + "em";
            pdiv.style.height = trackHeight + "em";
            pdiv.style.left = 0 + "px";
            pdiv.style.width = progressWidth + "px";
            pdiv.style.background = "#2870AC";
                         
			div.appendChild(pdiv);
            
            if (eventTheme.progress.showText == true) {
                pctxt = percentComplete + "%";
                pcel = doc.createTextNode(pctxt);                
                
                pcspan = doc.createElement("span");
                pcspan.style.position = "absolute";
                pcspan.style.overflow = "hidden";
                pcspan.style.left = "0.5em";
                if (eventTheme.progress.textSize < 1) {
                	//pcspan.style.top = "0.14em";
                	//spacing = 1 - eventTheme.progress.textSize - 0.05; // 0.05em padding
                	//pcspan.style.top = (spacing / 2) + "em";
                }
                // TODO transfer to CSS or theme
                pcspan.style.color = eventTheme.progress.textColor;
                pcspan.style.fontSize = trackHeight - (trackHeight / 8) + "em";
                pcspan.appendChild(pcel);
                
                div.appendChild(pcspan);
            }
            
            
            
            eventLayer.appendChild(div);
            
        } else {
            var temp = startPixel2;
            startPixel2 = endPixel2;
            endPixel2 = temp;
        }
        if (div == null) {
            console.log(evt);
        }
        attachClickEvent(div);
            
        if (showText) {
            var divLabel = doc.createElement("div");
            divLabel.style.position = "absolute";
            
            divLabel.style.top = streamOffset;
            divLabel.style.height = trackHeight + "em";
            divLabel.style.left = ((length > eventTheme.label.width) ? startPixel2 : endPixel2) + "px";
            divLabel.style.width = eventTheme.label.width + "px";
            divLabel.style.color = foreground != null ? foreground : (outside ? eventTheme.label.outsideColor : eventTheme.label.insideColor);
            divLabel.style.overflow = "hidden";
            divLabel.appendChild(doc.createTextNode(evt.getText()));
            
            eventLayer.appendChild(divLabel);
            attachClickEvent(divLabel);
        }
        
        createHighlightDiv(highlightIndex, startPixel, endPixel - startPixel, highlightOffset, highlightWidth);
    };
    
    var createEventDiv = function(evt, highlightIndex) {
        var startDate = evt.getStart();
        var endDate = evt.getEnd();
        
        var startPixel = Math.round(p._band.dateToPixelOffset(startDate));
        var endPixel = Math.round(p._band.dateToPixelOffset(endDate));
        
        var streamOffset = (trackOffset + 
            p._layout.getTrack(evt) * (trackHeight + trackGap));
            
        if (evt.isInstant()) {
            createInstantDiv(evt, startPixel, endPixel, streamOffset + "em", 
                highlightIndex, streamOffset - trackGap, trackHeight + 2 * trackGap);
        } else {
            if (evt.isSummary()) {
            	createSummaryDiv(evt, startPixel, endPixel, streamOffset + "em");       
            } else {
            	createDurationDiv(evt, startPixel, endPixel, streamOffset + "em",
                	highlightIndex, streamOffset - trackGap, trackHeight + 2 * trackGap, evt.getPercentComplete());
            }
            
        }
    };
    
    var filterMatcher = (this._filterMatcher != null) ? 
        this._filterMatcher :
        function(evt) { return true; };
    var highlightMatcher = (this._highlightMatcher != null) ? 
        this._highlightMatcher :
        function(evt) { return -1; };
    
    var iterator = eventSource.getEventIterator(minDate, maxDate);
    while (iterator.hasNext()) {
        var evt = iterator.next();
        if (filterMatcher(evt)) {
            createEventDiv(evt, highlightMatcher(evt));
        }
    }
    
    this._highlightLayer.style.display = "block";
    this._eventLayer.style.display = "block";
};

Timeline.Gantt.TaskPainter.prototype.softPaint = function() {
};

Timeline.Gantt.TaskPainter.prototype._onClickInstantEvent = function(icon, domEvt, evt) {
    domEvt.cancelBubble = true;
    
    var c = Timeline.DOM.getPageCoordinates(icon);
    this._showBubble(
        c.left + Math.ceil(icon.offsetWidth / 2), 
        c.top + Math.ceil(icon.offsetHeight / 2),
        evt
    );
};

Timeline.Gantt.TaskPainter.prototype._onClickDurationEvent = function(domEvt, evt, target) {
    domEvt.cancelBubble = true;
    if ("pageX" in domEvt) {
        var x = domEvt.pageX;
        var y = domEvt.pageY;
    } else {
        var c = Timeline.DOM.getPageCoordinates(target);
        var x = domEvt.offsetX + c.left;
        var y = domEvt.offsetY + c.top;
    }
    this._showBubble(x, y, evt);
};

Timeline.Gantt.TaskPainter.prototype._showBubble = function(x, y, evt) {
    var div = this._band.openBubbleForPoint(
        x, y,
        this._theme.event.bubble.width,
        this._theme.event.bubble.height
    );
    
    evt.fillInfoBubble(div, this._theme, this._band.getLabeller());
};

/*=====================================================
 *  Gantt Task Info Painter
 *=====================================================
 */
 
Timeline.Gantt.TaskInfoPainter = new Object();

Timeline.Gantt.TaskInfoPainter = function(params) {
    this._params = params;
    this._theme = params.theme;
    this._layout = params.layout;
    
    this._showText = params.showText;
    this._showLineForNoText = ("showLineForNoText" in params) ? 
        params.showLineForNoText : params.theme.event.instant.showLineForNoText;
        
    this._filterMatcher = null;
    this._highlightMatcher = null;
};

Timeline.Gantt.TaskInfoPainter.prototype.initialize = function(band, timeline) {
    this._band = band;
    this._timeline = timeline;

    this._layout.initialize(band, timeline);
    
    this._eventLayer = null;
    this._highlightLayer = null;
};

Timeline.Gantt.TaskInfoPainter.prototype.getLayout = function() {
    return this._layout;
};

Timeline.Gantt.TaskInfoPainter.prototype.setLayout = function(layout) {
    this._layout = layout;
};

Timeline.Gantt.TaskInfoPainter.prototype.getFilterMatcher = function() {
    return this._filterMatcher;
};

Timeline.Gantt.TaskInfoPainter.prototype.setFilterMatcher = function(filterMatcher) {
    this._filterMatcher = filterMatcher;
};

Timeline.Gantt.TaskInfoPainter.prototype.getHighlightMatcher = function() {
    return this._highlightMatcher;
};

Timeline.Gantt.TaskInfoPainter.prototype.setHighlightMatcher = function(highlightMatcher) {
    this._highlightMatcher = highlightMatcher;
};

Timeline.Gantt.TaskInfoPainter.prototype.paint = function() {
    var eventSource = this._band.getEventSource();

    if (eventSource == null) {
        return;
    }
    
    if (this._highlightLayer != null) {
        this._band.removeLayerDiv(this._highlightLayer);
    }
    this._highlightLayer = this._band.createLayerDiv(105);
    this._highlightLayer.setAttribute("name", "event-highlights");
    this._highlightLayer.style.display = "none";
    
    if (this._eventLayer != null) {
        this._band.removeLayerDiv(this._eventLayer);
    }
    this._eventLayer = this._band.createLayerDiv(110);
    this._eventLayer.setAttribute("name", "events");
    this._eventLayer.style.display = "none";
    
    /*
    var minDate = this._band.getMinDate();
    var maxDate = this._band.getMaxDate();
    */
    var minTask = this._band.getMinTask();
    var maxTask = this._band.getMaxTask();
    
    var doc = this._timeline.getDocument();
    
    var p = this;
    var eventLayer = this._eventLayer;
    var highlightLayer = this._highlightLayer;
    
    var showText = this._showText;
    var theme = this._params.theme;
    var eventTheme = theme.event;
    var trackOffset = eventTheme.track.offset;
    var trackHeight = ("trackHeight" in this._params) ? this._params.trackHeight : eventTheme.track.height;
    var trackGap = ("trackGap" in this._params) ? this._params.trackGap : eventTheme.track.gap;
    
    /**
     * appendIcon
     */
    var appendIcon = function(evt, div) {
        var icon = evt.getIcon();
        var img = Timeline.Graphics.createTranslucentImage(
            doc, icon != null ? icon : eventTheme.instant.icon
        );
        div.appendChild(img);
        div.style.cursor = "pointer";
        
        Timeline.DOM.registerEvent(div, "mousedown", function(elmt, domEvt, target) {
            p._onClickInstantEvent(img, domEvt, evt);
        });
    };
    
	var createTaskInfoItem = function(evt, streamOffset, ohnegap, highlight) {
		var div = doc.createElement("div");
        div.style.position = "absolute";
        div.style.overflow = "hidden";
        
        div.style.width = "100%";
        
        var foreground = evt.getTextColor();
        var background = evt.getColor();
        
        div.style.top = ohnegap + "em";
        div.style.height = trackHeight + trackGap + "em";
        
        var span = doc.createElement("span");
        span.className = "timeline-gantt-taskinfo-item";
        span.appendChild(doc.createTextNode(evt.getText()));	
        
        div.appendChild(span);
        eventLayer.appendChild(div);	
	}

    
    var createEventDiv = function(evt, highlightIndex) {
        console.log("creating event");
        
        //var startDate = evt.getStart();
        //var endDate = evt.getEnd();
        
        //var startPixel = Math.round(p._band.taskToPixelOffset(0));
        //var endPixel = Math.round(p._band.dateToPixelOffset(endDate));
        
        
        var streamOffset = (trackOffset + p._layout.getTrack(evt) * (trackHeight + trackGap));
        
        
        createTaskInfoItem(evt, streamOffset + "em", streamOffset - trackGap, trackHeight + 2 * trackGap);
    };
    
    var filterMatcher = (this._filterMatcher != null) ? 
        this._filterMatcher :
        function(evt) { return true; };
    var highlightMatcher = (this._highlightMatcher != null) ? 
        this._highlightMatcher :
        function(evt) { return -1; };
    
    var iterator = eventSource.getAllEventIterator();
    
    while (iterator.hasNext()) {
        var evt = iterator.next();
        
        if (filterMatcher(evt)) {
            createEventDiv(evt, highlightMatcher(evt));
        }
    }
    
    this._highlightLayer.style.display = "block";
    this._eventLayer.style.display = "block";
    this._eventLayer.style.display = "block";
};

Timeline.Gantt.TaskInfoPainter.prototype.softPaint = function() {
};