webpackJsonp(["app/js/activity-manage/ppt/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _ppt = __webpack_require__("44eb0bf1fd106b2fb0b0");
	
	var _ppt2 = _interopRequireDefault(_ppt);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _ppt2["default"]();

/***/ }),

/***/ "44eb0bf1fd106b2fb0b0":
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
	
	var PPT = function () {
	  function PPT() {
	    _classCallCheck(this, PPT);
	
	    this.$mediaId = $('[name="mediaId"]');
	    this.validator3 = null;
	    this.init();
	  }
	
	  _createClass(PPT, [{
	    key: 'init',
	    value: function init() {
	      (0, _chooserUi.showChooserType)(this.$mediaId);
	      this.initStep2Form();
	      this.initSelect();
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
	            required: Translator.trans('activity.ppt_manage.media_error_hint')
	          }
	        }
	      });
	    }
	  }, {
	    key: 'initStep3Form',
	    value: function initStep3Form() {
	      var $step3_form = $("#step3-form");
	      this.validator3 = $step3_form.validate({
	        rules: {
	          finishDetail: {
	            required: function required() {
	              return $('#condition-select').children('option:selected').val() === 'time';
	            },
	            positive_integer: true,
	            max: 300,
	            min: 1
	          }
	        },
	        messages: {
	          finishDetail: {
	            required: Translator.trans('activity.ppt_manage.finish_detail_required_error_hint')
	          }
	        }
	      });
	      $step3_form.data('validator', this.validator3);
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
	    }
	  }, {
	    key: 'initSelect',
	    value: function initSelect() {
	      var _this2 = this;
	
	      var $select = $('#condition-select');
	      if ($select.children('option:selected').val() === 'time') {
	        this.initStep3Form();
	      }
	
	      $select.on('change', function (event) {
	        var conditionsType = $(event.currentTarget).children('option:selected').val();
	        var $conditionsDetail = $("#condition-group");
	        if (conditionsType !== 'time') {
	          $conditionsDetail.addClass('hidden');
	          return;
	        } else {
	          $conditionsDetail.removeClass('hidden');
	        }
	        if (!_this2.validator3) {
	          _this2.initStep3Form();
	        }
	      });
	    }
	  }]);
	
	  return PPT;
	}();
	
	exports["default"] = PPT;

/***/ })

});
//# sourceMappingURL=index.js.map