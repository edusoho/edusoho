webpackJsonp(["app/js/settings/avatar-crop/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import EsImageCrop from 'common/es-image-crop';
	
	var CoverCrop = function () {
	  function CoverCrop(props) {
	    _classCallCheck(this, CoverCrop);
	
	    this.element = props.element;
	    this.avatarCrop = props.avatarCrop;
	    this.saveBtn = props.saveBtn;
	    this.goBack = props.goBack;
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
	      var _this = this;
	
	      var $node = $(this.element);
	      $node.on('click', this.goBack, function (event) {
	        return _this.goBackEvent(event);
	      });
	
	      $node.on('click', this.saveBtn, function (event) {
	        event.stopPropagation();
	        imageCrop.crop({
	          imgs: {
	            large: [200, 200],
	            medium: [120, 120],
	            small: [48, 48]
	          }
	        });
	      });
	    }
	  }, {
	    key: 'goBackEvent',
	    value: function goBackEvent(event) {
	      var $element = $(event.currentTarget);
	      document.location.href = $element.data("gotoUrl");
	    }
	  }, {
	    key: 'imageCrop',
	    value: function imageCrop() {
	      var _this2 = this;
	
	      var imageCrop = new EsImageCrop({
	        element: this.avatarCrop,
	        cropedWidth: 200,
	        cropedHeight: 200
	      });
	
	      imageCrop.afterCrop = function (response) {
	        var $saveBtn = $(_this2.saveBtn);
	
	        var url = $saveBtn.data('url');
	
	        $.post(url, { images: response }, function () {
	          document.location.href = $saveBtn.data("gotoUrl");
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
	  saveBtn: '#upload-avatar-btn',
	  goBack: '.js-go-back'
	});

/***/ })
]);