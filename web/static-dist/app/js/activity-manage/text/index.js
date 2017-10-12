webpackJsonp(["app/js/activity-manage/text/index"],{

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
	
	var _text = __webpack_require__("1d0e3cb29c694c31b1b6");
	
	var _text2 = _interopRequireDefault(_text);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _text2["default"]();

/***/ }),

/***/ "1d0e3cb29c694c31b1b6":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _editor = __webpack_require__("6ff75de42f89cafb6c75");
	
	__webpack_require__("d5e8fa5f17ac5fe79c78");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Text = function () {
	  function Text(props) {
	    _classCallCheck(this, Text);
	
	    this._init();
	  }
	
	  _createClass(Text, [{
	    key: "_init",
	    value: function _init() {
	      var _this = this;
	
	      this._inItStep2form();
	      this._inItStep3form();
	      this._lanuchAutoSave();
	
	      $('.js-continue-edit').on('click', function (event) {
	        var $btn = $(event.currentTarget);
	        var content = $btn.data('content');
	        _this.editor.setData(content);
	        $btn.remove();
	      });
	    }
	  }, {
	    key: "_inItStep2form",
	    value: function _inItStep2form() {
	      var $step2_form = $('#step2-form');
	      var validator = $step2_form.data('validator');
	      validator = $step2_form.validate({
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          content: {
	            required: true,
	            trim: true
	          }
	        }
	      });
	      var $content = $('[name="content"]');
	      this.editor = (0, _editor.initEditor)($content, validator);
	      this._contentCache = $content.val();
	    }
	  }, {
	    key: "_lanuchAutoSave",
	    value: function _lanuchAutoSave() {
	      var _this2 = this;
	
	      var $title = $('#modal .modal-title', parent.document);
	      this._originTitle = $title.text();
	      setInterval(function () {
	        _this2._saveDraft();
	      }, 5000);
	    }
	  }, {
	    key: "_saveDraft",
	    value: function _saveDraft() {
	      var _this3 = this;
	
	      var content = this.editor.getData();
	      var needSave = content !== this._contentCache;
	      if (!needSave) {
	        return;
	      }
	      var $content = $('[name="content"]');
	      $.post($content.data('saveDraftUrl'), { content: content }).done(function () {
	        var date = new Date(); //日期对象
	        var $title = $('#modal .modal-title', parent.document);
	        var now = Translator.trans('site.date_format_his', { 'hours': date.getHours(), 'minutes': date.getMinutes(), 'seconds': date.getSeconds() });
	        $title.text(_this3._originTitle + Translator.trans('activity.text_manage.save_draft_hint', { createdTime: now }));
	        _this3._contentCache = content;
	      });
	    }
	  }, {
	    key: "_inItStep3form",
	    value: function _inItStep3form() {
	      var $step3_form = $('#step3-form');
	      var validator = $step3_form.data('validator');
	      validator = $step3_form.validate({
	        rules: {
	          finishDetail: {
	            required: true,
	            positive_integer: true,
	            max: 300
	          }
	        },
	        messages: {
	          finishDetail: {
	            required: Translator.trans('activity.text_manage.finish_detail_required_error_hint')
	          }
	        }
	      });
	    }
	  }]);
	
	  return Text;
	}();
	
	exports["default"] = Text;

/***/ })

});
//# sourceMappingURL=index.js.map