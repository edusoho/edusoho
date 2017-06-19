webpackJsonp(["app/js/settings/avatar-crop-modal/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esImageCrop = __webpack_require__("12695715cd021610570e");
	
	var _esImageCrop2 = _interopRequireDefault(_esImageCrop);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
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
	
	      var imageCrop = new _esImageCrop2.default({
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
	
	            (0, _notify2.default)('success', Translator.trans('上传成功'));
	          } else {
	            (0, _notify2.default)('danger', Translator.trans('上传失败,请重试'));
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

/***/ }),

/***/ "12695715cd021610570e":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import 'es-jcrop/js/Jcrop.js';
	import '!style-loader?insertAt=top!css-loader!nodeModulesDir/es-jcrop/css/Jcrop.min.css';
	
	var EsImageCrop = function () {
	  function EsImageCrop(config) {
	    _classCallCheck(this, EsImageCrop);
	
	    var self = this;
	    this.config = $.extend({
	      element: null,
	      group: 'default'
	    }, config);
	
	    this.element = $(this.config.element);
	    var $picture = this.element;
	    var scaledWidth = $picture.attr('width'),
	        scaledHeight = $picture.attr('height'),
	        naturalWidth = $picture.data('naturalWidth'),
	        naturalHeight = $picture.data('naturalHeight'),
	        cropedWidth = this.config.cropedWidth,
	        cropedHeight = this.config.cropedHeight,
	        ratio = cropedWidth / cropedHeight,
	        selectWidth = cropedWidth * (naturalWidth / scaledWidth),
	        selectHeight = cropedHeight * (naturalHeight / scaledHeight);
	
	    $picture.Jcrop({
	      trueSize: [naturalWidth, naturalHeight],
	      setSelect: [0, 0, selectWidth, selectHeight],
	      aspectRatio: ratio,
	      keySupport: false,
	      allowSelect: false,
	      onSelect: function onSelect(c) {
	        self.onSelect(c);
	      }
	    });
	
	    // $picture.css('height', scaledHeight);
	  }
	
	  _createClass(EsImageCrop, [{
	    key: 'crop',
	    value: function crop() {
	      var postData = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	
	      console.log('crop');
	      var self = this;
	      var cropImgUrl = app.imgCropUrl;
	      var newPostData = $.extend(self.element.data('Jcrop').ui.selection.last, postData, {
	        width: this.element.width(),
	        height: this.element.height(),
	        group: self.config.group
	      });
	
	      //由于小数精度问题，jcrop计算出的x、y初始坐标可能小于0，比如-2.842170943040401e-14, 应当修正此类非法数据
	      newPostData.x = newPostData.x > 0 ? newPostData.x : 0;
	      newPostData.y = newPostData.y > 0 ? newPostData.y : 0;
	      $.post(cropImgUrl, newPostData, function (response) {
	        self.afterCrop(response);
	      });
	    }
	  }, {
	    key: 'onSelect',
	    value: function onSelect(c) {
	      //override it
	    }
	  }, {
	    key: 'afterCrop',
	    value: function afterCrop(response) {
	      //override it
	    }
	  }]);
	
	  return EsImageCrop;
	}();
	
	export default EsImageCrop;

/***/ })

});