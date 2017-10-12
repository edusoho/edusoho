webpackJsonp(["app/js/classroom-manage/set-picture-crop/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	var _esImageCrop = __webpack_require__("12695715cd021610570e");
	
	var _esImageCrop2 = _interopRequireDefault(_esImageCrop);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var imageCrop = new _esImageCrop2["default"]({
	    element: "#classroom-picture-crop",
	    group: "course",
	    cropedWidth: 525,
	    cropedHeight: 350
	});
	
	imageCrop.afterCrop = function (response) {
	    var url = $("#upload-picture-btn").data("url");
	    $.post(url, { images: response }, function () {
	        document.location.href = $("#upload-picture-btn").data("gotoUrl");
	    });
	};
	
	$("#upload-picture-btn").click(function (e) {
	    e.stopPropagation();
	    imageCrop.crop({
	        imgs: {
	            large: [525, 350],
	            middle: [345, 230],
	            small: [213, 142]
	        }
	    });
	});
	
	$('.go-back').click(function () {
	    history.go(-1);
	});

/***/ })
]);
//# sourceMappingURL=index.js.map