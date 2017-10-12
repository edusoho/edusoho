webpackJsonp(["app/js/material-lib/preview/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var playerDiv = $('#material-preview-player');
	var url = playerDiv.data('url');
	
	if (playerDiv.length > 0) {
	  var html = '<iframe src=\'' + url + '\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
	
	  playerDiv.html(html);
	}
	
	var $modal = $('#modal');
	$modal.on('hidden.bs.modal', function () {
	  if (playerDiv.length > 0) {
	    playerDiv.html('');
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map