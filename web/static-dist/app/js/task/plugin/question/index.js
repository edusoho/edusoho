webpackJsonp(["app/js/task/plugin/question/index"],{

/***/ "00e30a58f14c77ba6f94":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var _class = function () {
	  function _class() {
	    _classCallCheck(this, _class);
	
	    this.$element = $('#task-question-plugin-form');
	    this.editor = null;
	    this.validator = null;
	    this.initEvent();
	  }
	
	  _createClass(_class, [{
	    key: "initEvent",
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('focusin', '.expand-form-trigger', function (event) {
	        return _this.expand();
	      });
	      this.$element.on('click', '.btn-primary', function (event) {
	        return _this.save(event);
	      });
	      this.$element.on('click', '.collapse-form-btn', function (event) {
	        return _this.collapse();
	      });
	    }
	  }, {
	    key: "save",
	    value: function save(event) {
	      var _this2 = this;
	
	      event.preventDefault();
	
	      if (!this.validator || !this.validator.form()) {
	        return;
	      }
	      var $btn = $(event.currentTarget);
	      $btn.attr('disabled', 'disabled');
	
	      var channel = _postal2["default"].channel('task.plugin.question');
	
	      $.post(this.$element.attr('action'), this.$element.serialize()).done(function (html) {
	        (0, _notify2["default"])('success', Translator.trans('task.plugin_question_post_success_hint'));
	        channel.publish("form.save", {
	          html: html
	        });
	        $btn.removeAttr('disabled');
	        _this2.collapse();
	      }).fail(function (error) {
	        (0, _notify2["default"])('danger', error);
	      });
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      var _this3 = this;
	
	      if (this.$element.hasClass('form-expanded')) {
	        return;
	      }
	
	      this.$element.addClass('form-expanded');
	
	      var editor = CKEDITOR.replace('question_content', {
	        toolbar: 'Simple',
	        filebrowserImageUploadUrl: this.$element.find('#question_content').data('imageUploadUrl')
	      });
	
	      this.editor = editor;
	
	      this.validator = this.$element.validate({
	        rules: {
	          'question[title]': 'required',
	          'question[content]': 'required'
	        },
	        messages: {
	          'question[title]': Translator.trans('task.plugin_question_add.title_required_error_hint'),
	          'question[content]': Translator.trans('task.plugin_question_add.content_required_error_hint')
	        }
	      });
	
	      editor.on('change', function () {
	        _this3.$element.find('[name="question[content]"]').val(editor.getData());
	      });
	      editor.on('blur', function () {
	        _this3.$element.find('[name="question[content]"]').val(editor.getData());
	      });
	
	      this.$element.find('.js-detail-form-group').removeClass('hide');
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      this.$element.removeClass('form-expanded');
	      this.editor && this.editor.destroy();
	      this.$element.removeData("validator");
	      this.clear();
	      this.$element.find('.js-detail-form-group').addClass('hide');
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.$element.find('input[type=text],textarea').each(function () {
	        $(this).val('');
	      });
	    }
	  }]);

	  return _class;
	}();

	exports["default"] = _class;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	var _form = __webpack_require__("00e30a58f14c77ba6f94");
	
	var _form2 = _interopRequireDefault(_form);
	
	var _question = __webpack_require__("d8c8273d605150f97e6e");
	
	var _question2 = _interopRequireDefault(_question);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionPlugin = function () {
	  function QuestionPlugin() {
	    _classCallCheck(this, QuestionPlugin);
	
	    this.$element = $('.question-pane');
	    this.$list = this.$element.find('.question-list-block');
	    this.$detail = this.$element.find('.question-detail-block');
	    this.form = new _form2["default"]();
	    this.question = null;
	    this.initEvent();
	  }
	
	  _createClass(QuestionPlugin, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.js-redirect-question-detail', function (event) {
	        return _this.onRedirectQuestion(event);
	      });
	
	      var channel = _postal2["default"].channel('task.plugin.question');
	
	      channel.subscribe('form.save', function (data, envelope) {
	        _this.$element.find('[data-role="list"]').prepend(data.html);
	        _this.$element.find('.empty-item').remove();
	      });
	
	      channel.subscribe('back-to-list', function () {
	        return _this.onBackList();
	      });
	
	      $("[data-toggle='popover']").popover();
	    }
	  }, {
	    key: 'onRedirectQuestion',
	    value: function onRedirectQuestion(event) {
	      var $target = $(event.currentTarget);
	      var url = $target.data('url');
	      this.question = new _question2["default"](url);
	      this.$list.hide();
	      this.$detail.show();
	    }
	  }, {
	    key: 'onBackList',
	    value: function onBackList() {
	      this.question && this.question.destroy();
	      this.$list.show();
	      this.$detail.hide();
	    }
	  }]);
	
	  return QuestionPlugin;
	}();
	
	new QuestionPlugin();

/***/ }),

/***/ "d8c8273d605150f97e6e":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var _class = function () {
	  function _class(url) {
	    _classCallCheck(this, _class);
	
	    this.url = url;
	    this.$element = $('.question-detail-block');
	    this.$form = null;
	    this.validator = null;
	    this.channel = _postal2["default"].channel('task.plugin.question');
	    this.render();
	  }
	
	  _createClass(_class, [{
	    key: "initEvent",
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.back-to-list', function () {
	        _this.channel.publish('back-to-list');
	      });
	
	      this.$form.on('click', '.btn-primary', function (event) {
	        return _this.onSavePost(event);
	      });
	    }
	  }, {
	    key: "onSavePost",
	    value: function onSavePost(event) {
	      var _this2 = this;
	
	      event.preventDefault();
	
	      if (!this.validator || !this.validator.form()) {
	        return;
	      }
	
	      $.post(this.$form.attr('action'), this.$form.serialize()).done(function (html) {
	        _this2.$element.find('[data-role=post-list]').append(html);
	        var number = parseInt(_this2.$element.find('[data-role=post-number]').text());
	        _this2.$element.find('[data-role=post-number]').text(number + 1);
	        _this2.$form.find('textarea').val('');
	      }).error(function (response) {
	        Notify.danger(response.error.message);
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this3 = this;
	
	      $.get(this.url).done(function (html) {
	        _this3.$element.html(html);
	
	        _this3.$form = _this3.$element.find('.post-form');
	        _this3.validator = _this3.$form.validate({
	          rules: {
	            'post[content]': 'required'
	          },
	          messages: {
	            'post[content]': Translator.trans('task.plugin_question_replay.content_required_error_hint')
	          }
	        });
	
	        _this3.initEvent();
	      }).fail(function (error) {
	        (0, _notify2["default"])('danger', 'error');
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.$element.html('');
	      this.$element.undelegate();
	    }
	  }]);

	  return _class;
	}();

	exports["default"] = _class;

/***/ })

});
//# sourceMappingURL=index.js.map