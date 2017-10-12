webpackJsonp(["app/js/index/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _swiper = __webpack_require__("370d3340744bf261df0e");
	
	var _swiper2 = _interopRequireDefault(_swiper);
	
	__webpack_require__("7840d638cc48059df0fc");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	echo.init();
	
	if ($(".es-poster .swiper-slide").length > 1) {
	  var swiper = new _swiper2["default"]('.es-poster.swiper-container', {
	    pagination: '.swiper-pager',
	    paginationClickable: true,
	    autoplay: 5000,
	    autoplayDisableOnInteraction: false,
	    loop: true,
	    calculateHeight: true,
	    roundLengths: true,
	    onInit: function onInit(swiper) {
	      $(".swiper-slide").removeClass('swiper-hidden');
	    }
	  });
	}
	
	$("body").on('click', '.js-course-filter', function () {
	  var $btn = $(this);
	  var courseType = $btn.data('type');
	  var text = $('.course-filter .visible-xs .active a').text();
	  $.get($btn.data('url'), function (html) {
	    $('#' + courseType + '-list-section').after(html).remove();
	    var parent = $btn.parent();
	    if (!parent.hasClass('course-sort')) {
	      text = $btn.find("a").text();
	    }
	    $('.course-filter .visible-xs .btn').html(text + " " + '<span class="caret"></span>');
	    // Lazyload.init();
	    echo.init();
	  });
	});

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