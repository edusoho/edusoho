webpackJsonp(["app/js/group/background-crop/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esImageCrop = __webpack_require__("12695715cd021610570e");
	
	var _esImageCrop2 = _interopRequireDefault(_esImageCrop);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var imageCrop = new _esImageCrop2["default"]({
	  element: "#logo-crop",
	  group: 'group',
	  cropedWidth: 1140,
	  cropedHeight: 150
	});
	
	imageCrop.afterCrop = function (response) {
	  var url = $("#upload-picture-btn").data("url");
	  $.post(url, { images: response }, function () {
	    document.location.href = $("#upload-picture-btn").data("reloadUrl");
	  });
	};
	
	$("#upload-picture-btn").click(function (e) {
	  e.stopPropagation();
	  imageCrop.crop({
	    imgs: {
	      backgroundLogo: [1140, 150]
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map