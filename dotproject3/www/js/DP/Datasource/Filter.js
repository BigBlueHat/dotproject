

DP.Datasource.Filter = function() {
	this._text = "";
	this._observers = [];
}

DP.Datasource.Filter.String = function() {
	this._text = "";
	this._observers = [];
}

DP.Datasource.Filter.prototype.setValue = function(v) {
	this._text = v;
	this.notify();
}

DP.Datasource.Filter.prototype.getValue = function() {
	return this._text;
}

// TODO: move observer pattern to prototype and extend this object with its methods.

// Attach observer
DP.Datasource.Filter.prototype.attach = function(observer) {
	for (i in this._observers) {
		if (this._observers[i] == observer) {
			return false;
		}
	}

	this._observers.push(observer);
	return true;
}

// Detach observer
DP.Datasource.Filter.prototype.detach = function(observer) {
	for (i in this._observers) {
		if (this._observers[i] == observer) {
			// detach observer
			return true;
		}
	}
	
	return false;
}

DP.Datasource.Filter.prototype.notify = function() {
	// Notify observer list
	for (i in this._observers) {
		this._observers[i].update(this);
	}
}

DP.Datasource.Filter.factory = function(ftype) {
	return new DP.Datasource.Filter();
}