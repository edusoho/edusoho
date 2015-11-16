define(function(require, exports, module) {
	exports.run = function() {
		var ua = window.navigator.userAgent.toLowerCase(); 
		if(ua.match(/MicroMessenger/i) == 'micromessenger'){ 
			window.location.href = ('/');
		} else { 
			return false; 
		}
	};
});