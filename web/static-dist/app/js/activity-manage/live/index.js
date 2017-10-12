webpackJsonp(["app/js/activity-manage/live/index"],{

/***/ "6ff75de42f89cafb6c75":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var initEditor = exports.initEditor = function initEditor($item, validator) {
	
	  var editor = CKEDITOR.replace('text-content-field', {
	    toolbar: 'Task',
	    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
	    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
	    allowedContent: true,
	    height: 280
	  });
	
	  editor.on('change', function () {
	    console.log('change');
	    $item.val(editor.getData());
	    if (validator) {
	      validator.form();
	    }
	  });
	
	  //fix ie11 中文输入
	  editor.on('blur', function () {
	    console.log('blur');
	    $item.val(editor.getData());
	    if (validator) {
	      validator.form();
	    }
	  });
	
	  return editor;
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _live = __webpack_require__("6fc36d688eaf991f2202");
	
	var _live2 = _interopRequireDefault(_live);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _live2["default"]();

/***/ }),

/***/ "6fc36d688eaf991f2202":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _editor = __webpack_require__("6ff75de42f89cafb6c75");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Live = function () {
	  function Live(props) {
	    _classCallCheck(this, Live);
	
	    this.$startTime = $('#startTime');
	    this._init();
	  }
	
	  _createClass(Live, [{
	    key: '_init',
	    value: function _init() {
	      this.initStep2Form();
	      this._timePickerHide();
	    }
	  }, {
	    key: 'initStep2Form',
	    value: function initStep2Form() {
	      jQuery.validator.addMethod('show_overlap_time_error', function (value, element) {
	        return this.optional(element) || !$(element).data('showError');
	      }, '所选时间已经有直播了，请换个时间');
	      var $step2_form = $("#step2-form");
	      this.validator2 = $step2_form.validate({
	        onkeyup: false,
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            open_live_course_title: true
	          },
	          startTime: {
	            required: true,
	            DateAndTime: true,
	            after_now: true
	          },
	          length: {
	            required: true,
	            digits: true,
	            max: 300,
	            min: 1,
	            show_overlap_time_error: true
	          },
	          remark: {
	            maxlength: 1000
	          }
	        }
	      });
	      (0, _editor.initEditor)($('[name="remark"]'), this.validator2);
	      $step2_form.data('validator', this.validator2);
	      this.dateTimePicker(this.validator2);
	      var that = this;
	      $step2_form.find('#startTime').change(function () {
	        that.checkOverlapTime($step2_form);
	      });
	
	      $step2_form.find('#length').change(function () {
	        that.checkOverlapTime($step2_form);
	      });
	    }
	  }, {
	    key: 'checkOverlapTime',
	    value: function checkOverlapTime($step2_form) {
	      if ($step2_form.find('#startTime').val() && $step2_form.find('#length').val()) {
	        var showError = 1;
	        var params = {
	          startTime: $step2_form.find('#startTime').val(),
	          length: $step2_form.find('#length').val(),
	          mediaType: 'live'
	        };
	        $.ajax({
	          url: $step2_form.find('#length').data('url'),
	          async: false,
	          type: 'POST',
	          data: params,
	          dataType: 'json',
	          success: function success(resp) {
	            showError = resp.success === 0;
	          }
	        });
	
	        $step2_form.find('#length').data('showError', showError);
	      }
	    }
	  }, {
	    key: 'dateTimePicker',
	    value: function dateTimePicker(validator) {
	      var $starttime = this.$startTime;
	      $starttime.datetimepicker({
	        format: 'yyyy-mm-dd hh:ii',
	        language: document.documentElement.lang,
	        autoclose: true,
	        endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
	      }).on('hide', function () {
	        validator.form();
	      });
	      $starttime.datetimepicker('setStartDate', new Date());
	    }
	  }, {
	    key: '_timePickerHide',
	    value: function _timePickerHide() {
	      var $starttime = this.$startTime;
	      parent.$('#modal', window.parent.document).on('afterNext', function () {
	        $starttime.datetimepicker('hide');
	      });
	    }
	  }]);
	
	  return Live;
	}();
	
	exports["default"] = Live;

/***/ })

});
//# sourceMappingURL=index.js.map