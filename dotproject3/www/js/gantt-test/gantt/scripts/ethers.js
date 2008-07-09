/*==================================================
 *  Task Ether
 *==================================================
 */

Timeline.Gantt.TaskEther = new Object();

Timeline.Gantt.TaskEther = function(params) {
    this._params = params;
    this._interval = params.interval;
    this._pixelsPerInterval = params.pixelsPerInterval;
};

Timeline.Gantt.TaskEther.prototype.initialize = function(timeline) {
    this._timeline = timeline;
    this._unit = timeline.getUnit();
    
    if ("startsOn" in this._params) {
        this._start = this._unit.parseFromObject(this._params.startsOn);
    } else if ("endsOn" in this._params) {
        this._start = this._unit.parseFromObject(this._params.endsOn);
        this.shiftPixels(-this._timeline.getPixelLength());
    } else if ("centersOn" in this._params) {
        this._start = this._unit.parseFromObject(this._params.centersOn);
        this.shiftPixels(-this._timeline.getPixelLength() / 2);
    } else {
        this._start = this._unit.makeDefaultValue();
        this.shiftPixels(-this._timeline.getPixelLength() / 2);
    }
};

Timeline.Gantt.TaskEther.prototype.setDate = function(date) {
    this._start = this._unit.cloneValue(date);
};

Timeline.Gantt.TaskEther.prototype.shiftPixels = function(pixels) {
    var numeric = this._interval * pixels / this._pixelsPerInterval;
    this._start = this._unit.change(this._start, numeric);
};


Timeline.Gantt.TaskEther.prototype.taskToPixelOffset = function(taskIndex) {
	console.log("getting task to pixel offset for task index: %d", taskIndex);
	console.trace();
	var numeric = taskIndex;
	return this._pixelsPerInterval * numeric / this._interval;
}

Timeline.Gantt.TaskEther.prototype.pixelOffsetToTask = function(pixels) {
	var numeric = pixels * this._interval / this._pixelsPerInterval;
	return numeric;	
}

// TODO - find task with starting date and return pixel offset
/*
Timeline.Gantt.TaskEther.prototype.dateToPixelOffset = function(date) {
    var numeric = this._unit.compare(date, this._start);
    return this._pixelsPerInterval * numeric / this._interval;
};

Timeline.Gantt.TaskEther.prototype.pixelOffsetToDate = function(pixels) {
    var numeric = pixels * this._interval / this._pixelsPerInterval;
    return this._unit.change(this._start, numeric);
};
*/