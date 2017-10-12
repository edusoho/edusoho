webpackJsonp(["app/js/activity-manage/flash/index"],{

/***/ "1ea1bdf6f0570f25248a":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	var _chooserUi = __webpack_require__("f324dbdea53170d5000f");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Flash = function () {
	  function Flash() {
	    _classCallCheck(this, Flash);
	
	    this.$mediaId = $('[name="mediaId"]');
	    this.validator2 = null;
	    this.init();
	  }
	
	  _createClass(Flash, [{
	    key: 'init',
	    value: function init() {
	      (0, _chooserUi.showChooserType)(this.$mediaId);
	      this.initStep2Form();
	      this.initStep3Form();
	      this.initFileChooser();
	    }
	  }, {
	    key: 'initStep2Form',
	    value: function initStep2Form() {
	      var $form = $('#step2-form');
	      this.validator2 = $form.validate({
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          mediaId: 'required'
	        },
	        messages: {
	          mediaId: {
	            required: Translator.trans('activity.flash_manage.media_error_hint')
	          }
	        }
	      });
	
	      $form.data('validator', this.validator2);
	    }
	  }, {
	    key: 'initStep3Form',
	    value: function initStep3Form() {
	      var $step3_form = $("#step3-form");
	
	      var validator = $step3_form.validate({
	        onkeyup: false,
	        rules: {
	          finishDetail: {
	            required: true,
	            positive_integer: true,
	            max: 300,
	            min: 1
	          }
	        },
	        messages: {
	          finishDetail: {
	            required: Translator.trans('activity.flash_manage.finish_detail_required_error_hint')
	          }
	        }
	      });
	
	      $step3_form.data('validator', validator);
	    }
	  }, {
	    key: 'initFileChooser',
	    value: function initFileChooser() {
	      var _this = this;
	
	      var fileChooser = new _fileChoose2["default"]();
	      fileChooser.on('select', function (file) {
	        (0, _chooserUi.chooserUiClose)();
	        _this.$mediaId.val(file.id);
	        $("#step2-form").valid();
	        $('[name="media"]').val(JSON.stringify(file));
	        if (_this.validator2) {
	          _this.validator2.form();
	        }
	      });
	    }
	  }]);
	
	  return Flash;
	}();
	
	exports["default"] = Flash;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _flash = __webpack_require__("1ea1bdf6f0570f25248a");
	
	var _flash2 = _interopRequireDefault(_flash);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _flash2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map