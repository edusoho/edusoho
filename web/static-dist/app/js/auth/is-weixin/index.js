webpackJsonp(["app/js/auth/is-weixin/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var ua = window.navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
	  var url = '/login';
	  var inviteCode = $("#invite_code");
	  if (inviteCode.length > 0) {
	    url = url + '?inviteCode=' + inviteCode.val();
	  }
	
	  window.location.href = url;
	}

/***/ })
]);
//# sourceMappingURL=index.js.map