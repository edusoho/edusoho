webpackJsonp(["app/js/activity-manage/doc/index"],{

/***/ "96a7449142c5cc41e885":
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
	
	var Document = function () {
	  function Document() {
	    _classCallCheck(this, Document);
	
	    this.$mediaId = $('[name="mediaId"]');
	    this.init();
	  }
	
	  _createClass(Document, [{
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
	      var $step2_form = $("#step2-form");
	      var validator = $step2_form.data('validator');
	      validator = $step2_form.validate({
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
	            required: Translator.trans('activity.document_manage.media_error_hint')
	          }
	        }
	      });
	    }
	  }, {
	    key: 'initStep3Form',
	    value: function initStep3Form() {
	      var $step3_form = $("#step3-form");
	      var validator = $step3_form.validate({
	        onkeyup: false,
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50
	          },
	          finishDetail: {
	            required: true,
	            positive_integer: true,
	            max: 300,
	            min: 1
	          }
	        },
	        messages: {
	          finishDetail: {
	            required: Translator.trans("activity.audio_manage.finish_detail_required_error_hint"),
	            digits: Translator.trans("activity.audio_manage.finish_detail_digits_error_hint")
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
	      });
	
	      $('#condition-select').on('change', function (event) {
	        var conditionsType = $(event.currentTarget).children('option:selected').val();
	
	        var $conditionsDetail = $("#condition-group");
	        if (conditionsType !== 'time') {
	          $conditionsDetail.addClass('hidden');
	        } else {
	          onConditionTimeType();
	        }
	      });
	    }
	  }]);
	
	  return Document;
	}();
	
	exports["default"] = Document;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _doc = __webpack_require__("96a7449142c5cc41e885");
	
	var _doc2 = _interopRequireDefault(_doc);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _doc2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map