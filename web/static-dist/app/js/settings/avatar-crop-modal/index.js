webpackJsonp(["app/js/settings/avatar-crop-modal/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esImageCrop = __webpack_require__("12695715cd021610570e");
	
	var _esImageCrop2 = _interopRequireDefault(_esImageCrop);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CoverCrop = function () {
	  function CoverCrop(props) {
	    _classCallCheck(this, CoverCrop);
	
	    this.element = props.element;
	    this.avatarCrop = props.avatarCrop;
	    this.saveBtn = props.saveBtn;
	    this.init();
	  }
	
	  _createClass(CoverCrop, [{
	    key: 'init',
	    value: function init() {
	      var imageCrop = this.imageCrop();
	      this.initEvent(imageCrop);
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent(imageCrop) {
	      $(this.saveBtn).on('click', function (event) {
	        event.stopPropagation();
	        var $this = $(event.currentTarget);
	
	        imageCrop.crop({
	          imgs: {
	            large: [200, 200],
	            medium: [120, 120],
	            small: [48, 48]
	          }
	        });
	
	        $this.button('loading');
	      });
	    }
	  }, {
	    key: 'imageCrop',
	    value: function imageCrop() {
	      var _this = this;
	
	      var imageCrop = new _esImageCrop2["default"]({
	        element: this.avatarCrop,
	        cropedWidth: 200,
	        cropedHeight: 200
	      });
	
	      imageCrop.afterCrop = function (response) {
	        var $saveBtn = $(_this.saveBtn);
	
	        var url = $saveBtn.data('url');
	
	        $.post(url, { images: response }, function (response) {
	          if (response.status === 'success') {
	            $("#profile_avatar").val(response.avatar);
	            $("#user-profile-form img").attr('src', response.avatar);
	            $("#profile_avatar").blur();
	            $("#modal").modal('hide');
	
	            (0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'));
	          } else {
	            (0, _notify2["default"])('danger', Translator.trans('upload_fail_retry_hint'));
	            $saveBtn.button('reset');
	          }
	        });
	      };
	      return imageCrop;
	    }
	  }]);
	
	  return CoverCrop;
	}();
	
	new CoverCrop({
	  element: '#avatar-crop-form',
	  avatarCrop: '#avatar-crop',
	  saveBtn: '#upload-avatar-btn'
	});

/***/ })
]);
//# sourceMappingURL=index.js.map