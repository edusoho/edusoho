webpackJsonp(["app/js/classroom/introduction/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	function postClassroomViewEvent() {
	    var $obj = $('#event-report');
	    var postData = $obj.data();
	    $.post($obj.data('url'), postData);
	}
	
	postClassroomViewEvent();

/***/ })
]);
//# sourceMappingURL=index.js.map