webpackJsonp(["app/js/courseset-manage/cover-crop/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import EsImageCrop from 'common/es-image-crop.js';
	
	var CoverCrop = function () {
	  function CoverCrop() {
	    _classCallCheck(this, CoverCrop);
	
	    this.init();
	  }
	
	  _createClass(CoverCrop, [{
	    key: "init",
	    value: function init() {
	      var imageCrop = new EsImageCrop({
	        element: "#courseset-picture-crop",
	        cropedWidth: 480,
	        cropedHeight: 270
	      });
	      imageCrop.afterCrop = function (response) {
	        var url = $("#upload-picture-btn").data("url");
	        $.post(url, { images: JSON.stringify(response) }, function () {
	          document.location.href = $("#upload-picture-btn").data("gotoUrl");
	        });
	      };
	
	      $("#upload-picture-btn").click(function (event) {
	        event.stopPropagation();
	        $(event.currentTarget).button('loading');
	        imageCrop.crop({
	          imgs: {
	            large: [480, 270],
	            middle: [304, 171],
	            small: [96, 54]
	          }
	        });
	      });
	
	      $('.go-back').click(function () {
	        history.go(-1);
	      });
	    }
	  }]);
	
	  return CoverCrop;
	}();
	
	new CoverCrop();

/***/ })
]);