webpackJsonp(["app/js/settings/profile-avatar-crop-modal/index"],{

/***/ "52b19b3eb1faf0ddf85d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esImageCrop = __webpack_require__("12695715cd021610570e");
	
	var _esImageCrop2 = _interopRequireDefault(_esImageCrop);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CoverCrop = function () {
	  function CoverCrop(props) {
	    _classCallCheck(this, CoverCrop);
	
	    this.avatarCrop = props.avatarCrop;
	    this.saveBtn = props.saveBtn;
	    this.$uploadInput = $('.js-upload-image.active');
	    this.init();
	  }
	
	  _createClass(CoverCrop, [{
	    key: 'init',
	    value: function init() {
	      this.imageInit();
	      var imageCrop = this.imageCrop();
	      this.initEvent(imageCrop);
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent(imageCrop) {
	      $(this.saveBtn).on('click', function (event) {
	        event.stopPropagation();
	        var $this = $(event.currentTarget);
	        console.log('start crop');
	        imageCrop.crop({
	          imgs: {
	            large: [200, 200],
	            medium: [120, 120],
	            small: [48, 48]
	          },
	          post: false
	        });
	
	        $this.button('loading');
	      });
	    }
	  }, {
	    key: 'imageInit',
	    value: function imageInit() {
	      var sourceImg = $('.upload-source-img.active');
	
	      $(this.avatarCrop).attr({
	        'src': sourceImg.attr('src'),
	        'width': sourceImg.attr('width'),
	        'height': sourceImg.attr('height'),
	        'data-natural-width': sourceImg.data('natural-width'),
	        'data-natural-height': sourceImg.data('natural-height')
	      });
	
	      sourceImg.remove();
	    }
	  }, {
	    key: 'imageCrop',
	    value: function imageCrop() {
	      var _this = this;
	
	      console.log('init');
	      var imageCrop = new _esImageCrop2["default"]({
	        element: this.avatarCrop,
	        cropedWidth: 200,
	        cropedHeight: 200,
	        group: 'user'
	      });
	
	      imageCrop.afterCrop = function (res) {
	        _this.$uploadInput.trigger('upload-image', res);
	      };
	
	      return imageCrop;
	    }
	  }]);
	
	  return CoverCrop;
	}();
	
	exports["default"] = CoverCrop;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _coverCrop = __webpack_require__("52b19b3eb1faf0ddf85d");
	
	var _coverCrop2 = _interopRequireDefault(_coverCrop);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _coverCrop2["default"]({
	  avatarCrop: '#avatar-crop',
	  saveBtn: '#save-btn'
	});

/***/ })

});
//# sourceMappingURL=index.js.map