webpackJsonp(["app/js/task/preview/index"],{

/***/ "584608d4ce1895020bac":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	var buyBtn = exports.buyBtn = function buyBtn($element) {
	  $element.on('click', function (event) {
	    $.post($(event.currentTarget).data('url'), function (resp) {
	      if ((typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) === 'object') {
	        window.location.href = resp.url;
	      } else {
	        $('#modal').modal('show').html(resp);
	      }
	    });
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _btnUtil = __webpack_require__("584608d4ce1895020bac");
	
	(0, _btnUtil.buyBtn)($('.js-task-preview-buy-btn'));
	
	$('#modal').on('hidden.bs.modal', function () {
	    $("#viewerIframe").attr('src', '');
	});
	$("#js-buy-btn").on('click', function () {
	    $('#modal').modal('hide');
	});
	
	function postCoursePreviewEvent() {
	    var $obj = $('#modal-event-report');
	    var postData = $obj.data();
	    $.post($obj.data('url'), postData);
	}
	
	postCoursePreviewEvent();

/***/ })

});
//# sourceMappingURL=index.js.map