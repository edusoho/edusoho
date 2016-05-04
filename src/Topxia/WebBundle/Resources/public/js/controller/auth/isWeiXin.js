define(function(require, exports, module) {
	exports.run = function() {
		var ua = window.navigator.userAgent.toLowerCase(); 
		var inviteCode = $("#invite_code").val();
		var url = '/login';
		if(ua.match(/MicroMessenger/i) == 'micromessenger'){ 
			window.location.href = url + '?inviteCode='+inviteCode;
		} else {
			return false;
		}
	};
});