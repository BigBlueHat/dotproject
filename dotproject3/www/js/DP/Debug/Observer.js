/**
 * Debug observer for debugging subject notifications
 */

DP.Debug = {};

DP.Debug.Observer = function() {
	
	
}

DP.Debug.Observer.prototype.update = function(subject) {
	alert(subject.getValue());
}