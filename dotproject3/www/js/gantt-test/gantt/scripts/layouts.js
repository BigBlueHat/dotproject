/*==================================================
 *  Static Track Based Layout
 *==================================================
 */
Timeline.Gantt.SequentialLayout = new Object();

Timeline.Gantt.SequentialLayout = function(params) {
    this._eventSource = params.eventSource;
    this._ether = params.ether;
    this._theme = params.theme;
    this._showText = ("showText" in params) ? params.showText : true;
    
    this._laidout = false;
    
    var layout = this;
    if (this._eventSource != null) {
        this._eventSource.addListener({
            onAddMany: function() {
                layout._laidout = false;
            }
        });
    }
};

Timeline.Gantt.SequentialLayout.prototype.initialize = function(timeline) {
    this._timeline = timeline;
};

Timeline.Gantt.SequentialLayout.prototype.getTrack = function(evt) {
    if (!this._laidout) {
        this._tracks = [];
        this._layout();
        this._laidout = true;
    }
    return this._tracks[evt.getID()];
};

Timeline.Gantt.SequentialLayout.prototype.getTrackCount = function() {
    if (!this._laidout) {
        this._tracks = [];
        this._layout();
        this._laidout = true;
    }
    return this._trackCount;
};

Timeline.Gantt.SequentialLayout.prototype._layout = function() {
    if (this._eventSource == null) {
        return;
    }
    
    var streams = [ Number.NEGATIVE_INFINITY ];
    var layout = this;
    var showText = this._showText;
    var theme = this._theme;
    var eventTheme = theme.event;
    
    var layoutInstant = function(evt, startPixel, endPixel, streamOffset) {
        var finalPixel = startPixel - 1;
        if (evt.isImprecise()) { // imprecise time
            finalPixel = endPixel;
        }
        if (showText) {
            finalPixel = Math.max(finalPixel, startPixel + eventTheme.label.width);
        }
        
        return finalPixel;
    };
    var layoutDuration = function(evt, startPixel, endPixel, streamOffset) {
        if (evt.isImprecise()) { // imprecise time
            var startDate = evt.getStart();
            var endDate = evt.getEnd();
                
            var startPixel2 = Math.round(layout._ether.dateToPixelOffset(startDate));
            var endPixel2 = Math.round(layout._ether.dateToPixelOffset(endDate));
        } else {
            var startPixel2 = startPixel;
            var endPixel2 = endPixel;
        }
        
        var finalPixel = endPixel2;
        var length = Math.max(endPixel2 - startPixel2, 1);
        
        
        if (showText) {
            if (length < eventTheme.label.width) {
                finalPixel = endPixel2 + eventTheme.label.width;
            }
        }
        
        return finalPixel;
    };
    var layoutEvent = function(evt) {
        var startDate = evt.getStart();
        var endDate = evt.getEnd();
        
        var startPixel = Math.round(layout._ether.dateToPixelOffset(startDate));
        var endPixel = Math.round(layout._ether.dateToPixelOffset(endDate));
        
        
        // find which vertical stream/track this object should be put into
        // Stream index starts at zero
        //var streamIndex = 0;
        var streamIndex = 0;
        // keep incrementing the stream until the current stream is lower than starting pixel, streams contains the final pixel of each element
        for (; streamIndex < streams.length; streamIndex++) {
        		//console.log("StartPixel: %d", startPixel);
            /*
            if (streams[streamIndex] < startPixel) { // if a stream is found with a final pixel less than our startpixel, we should use this stream
                console.log("Reached max stream: %d, which is lower than starting pixel: %d", streams[streamIndex], startPixel);
                break;
            }*/
        }
        // no streams yet, so push negative infinity which would force the first item to start at the first pixel
        if (streamIndex >= streams.length) {
            streams.push(Number.NEGATIVE_INFINITY);
        }
        streamIndex--;
        
        var streamOffset = (eventTheme.track.offset + 
            streamIndex * (eventTheme.track.height + eventTheme.track.gap)) + "em";
   
        //console.log("streamOffset: %s, streamIndex, %d", streamOffset, streamIndex);
        layout._tracks[evt.getID()] = streamIndex;
        
        if (evt.isInstant()) {
            streams[streamIndex] = layoutInstant(evt, startPixel, endPixel, streamOffset);
        } else {
            streams[streamIndex] = layoutDuration(evt, startPixel, endPixel, streamOffset);
        }
    };
    
    var iterator = this._eventSource.getAllEventIterator();
    while (iterator.hasNext()) {
        var evt = iterator.next();
        layoutEvent(evt);
    }
    
    this._trackCount = streams.length;
};


/*==================================================
 *  Task List Layout
 *==================================================
 */
Timeline.Gantt.TaskLayout = new Object();

Timeline.Gantt.TaskLayout = function(params) {
    this._eventSource = params.eventSource;
    this._ether = params.ether;
    this._theme = params.theme;
    this._showText = ("showText" in params) ? params.showText : true;
    
    this._laidout = false;
    
    var layout = this;
    if (this._eventSource != null) {
        this._eventSource.addListener({
            onAddMany: function() {
                layout._laidout = false;
            }
        });
    }
};

Timeline.Gantt.TaskLayout.prototype.initialize = function(timeline) {
    this._timeline = timeline;
};

Timeline.Gantt.TaskLayout.prototype.getTrack = function(evt) {
    if (!this._laidout) {
        this._tracks = [];
        this._layout();
        this._laidout = true;
    }
    return this._tracks[evt.getID()];
};

Timeline.Gantt.TaskLayout.prototype.getTrackCount = function() {
    if (!this._laidout) {
        this._tracks = [];
        this._layout();
        this._laidout = true;
    }
    return this._trackCount;
};

Timeline.Gantt.TaskLayout.prototype._layout = function() {
    if (this._eventSource == null) {
        return;
    }
    
    var streams = [ Number.NEGATIVE_INFINITY ];
    var layout = this;
    var showText = this._showText;
    var theme = this._theme;
    var eventTheme = theme.event;
    
    var layoutInstant = function(evt, startPixel, endPixel, streamOffset) {
        var finalPixel = startPixel - 1;
        if (evt.isImprecise()) { // imprecise time
            finalPixel = endPixel;
        }
        if (showText) {
            finalPixel = Math.max(finalPixel, startPixel + eventTheme.label.width);
        }
        
        return finalPixel;
    };
    
    var layoutDuration = function(evt, startPixel, endPixel, streamOffset) {
        /*
        if (evt.isImprecise()) { // imprecise time
            var startDate = evt.getStart();
            var endDate = evt.getEnd();
                
            var startPixel2 = Math.round(layout._ether.dateToPixelOffset(startDate));
            var endPixel2 = Math.round(layout._ether.dateToPixelOffset(endDate));
        } else {
            var startPixel2 = startPixel;
            var endPixel2 = endPixel;
        }
        
        var finalPixel = endPixel2;
        var length = Math.max(endPixel2 - startPixel2, 1);
        
        
        if (showText) {
            if (length < eventTheme.label.width) {
                finalPixel = endPixel2 + eventTheme.label.width;
            }
        }
        
        return finalPixel;
        */
        return 0;
    };
    var layoutEvent = function(evt) {
        var startDate = evt.getStart();
        var endDate = evt.getEnd();
        /*
        var startPixel = Math.round(layout._ether.dateToPixelOffset(startDate));
        var endPixel = Math.round(layout._ether.dateToPixelOffset(endDate));
        */
        var startPixel = 0;
        var endPixel = 400;
        // find which vertical stream/track this object should be put into
        // Stream index starts at zero
        //var streamIndex = 0;
        var streamIndex = 0;
        // keep incrementing the stream until the current stream is lower than starting pixel, streams contains the final pixel of each element
        for (; streamIndex < streams.length; streamIndex++) {
        		//console.log("StartPixel: %d", startPixel);
            /*
            if (streams[streamIndex] < startPixel) { // if a stream is found with a final pixel less than our startpixel, we should use this stream
                console.log("Reached max stream: %d, which is lower than starting pixel: %d", streams[streamIndex], startPixel);
                break;
            }*/
        }
        // no streams yet, so push negative infinity which would force the first item to start at the first pixel
        if (streamIndex >= streams.length) {
            streams.push(Number.NEGATIVE_INFINITY);
        }
        streamIndex--;
        
        var streamOffset = (eventTheme.track.offset + 
            streamIndex * (eventTheme.track.height + eventTheme.track.gap)) + "em";
   
        //console.log("streamOffset: %s, streamIndex, %d", streamOffset, streamIndex);
        layout._tracks[evt.getID()] = streamIndex;
        
        if (evt.isInstant()) {
            streams[streamIndex] = layoutInstant(evt, startPixel, endPixel, streamOffset);
        } else {
            streams[streamIndex] = layoutDuration(evt, startPixel, endPixel, streamOffset);
        }
    };
    
    var iterator = this._eventSource.getAllEventIterator();
    while (iterator.hasNext()) {
        var evt = iterator.next();
        layoutEvent(evt);
    }
    
    this._trackCount = streams.length;
};