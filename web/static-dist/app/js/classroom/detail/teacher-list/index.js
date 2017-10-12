webpackJsonp(["app/js/classroom/detail/teacher-list/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("7840d638cc48059df0fc");

/***/ }),

/***/ "7840d638cc48059df0fc":
/***/ (function(module, exports) {

	'use strict';
	
	$('body').on('click', '.teacher-item .follow-btn', function () {
	  var $btn = $(this);
	
	  $.post($btn.data('url'), function () {
	    var loggedin = $btn.data('loggedin');
	
	    if (loggedin === 1) {
	      $btn.hide();
	      $btn.closest('.teacher-item').find('.unfollow-btn').show();
	    }
	  });
	}).on('click', '.unfollow-btn', function () {
	  var $btn = $(this);
	
	  $.post($btn.data('url'), function () {}).always(function () {
	    $btn.hide();
	    $btn.closest('.teacher-item').find('.follow-btn').show();
	  });
	});

/***/ })

});
//# sourceMappingURL=index.js.map