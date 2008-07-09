Timeline.Gantt.TasklistEtherPainter = function(params) {
    this._params = params;
    this._theme = params.theme;
    this._multiple = ("multiple" in params) ? params.multiple : 1;
};

Timeline.Gantt.TasklistEtherPainter.prototype.initialize = function(band, timeline) {
    this._band = band;
    this._timeline = timeline;
    
    this._backgroundLayer = band.createLayerDiv(0);
    this._backgroundLayer.setAttribute("name", "task-ether-background"); // for debugging
    this._backgroundLayer.style.background = this._theme.ether.backgroundColors[band.getIndex()];
};

Timeline.Gantt.TasklistEtherPainter.prototype.setHighlight = function(startDate, endDate) {
    
};

Timeline.Gantt.TasklistEtherPainter.prototype.paint = function() {
 
};

Timeline.Gantt.TasklistEtherPainter.prototype.softPaint = function() {
};