webpackJsonp(["app/js/activity-manage/homework/index"],{

/***/ "1e0cf618bc778b8ab554":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _questionSubjective = __webpack_require__("71e1df85d5928925f4b1");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionOperate = function () {
	  function QuestionOperate($form, $modal) {
	    _classCallCheck(this, QuestionOperate);
	
	    this.$form = $form;
	    this.$modal = $modal;
	    this.initEvent();
	  }
	
	  _createClass(QuestionOperate, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$form.on('click', '[data-role="item-delete-btn"]', function (event) {
	        return _this.deleteQuestion(event);
	      });
	      this.$form.on('click', '[data-role="replace-item"]', function (event) {
	        return _this.replaceQuestion(event);
	      });
	      this.$form.on('click', '[data-role="preview-btn"]', function (event) {
	        return _this.previewQuestion(event);
	      });
	      this.$form.on('click', '[data-role="batch-delete-btn"]', function (event) {
	        return _this.batchDelete(event);
	      });
	      this.initSortList();
	    }
	  }, {
	    key: 'initSortList',
	    value: function initSortList() {
	      var _this2 = this;
	
	      this.$form.find('tbody').sortable({
	        containerPath: '> tr',
	        containerSelector: 'tbody',
	        itemSelector: 'tr.is-question',
	        placeholder: '<tr class="placeholder"/>',
	        exclude: '.notMoveHandle',
	        onDrop: function onDrop(item, container, _super) {
	          _super(item, container);
	          if (item.hasClass('have-sub-questions')) {
	            var $tbody = item.parents('tbody');
	            $tbody.find('tr.is-question').each(function () {
	              var $tr = $(this);
	              $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
	            });
	          }
	          _this2.refreshSeqs();
	        }
	      });
	    }
	  }, {
	    key: 'replaceQuestion',
	    value: function replaceQuestion(event) {
	      var _this3 = this;
	
	      var $target = $(event.currentTarget);
	      var excludeIds = [];
	      var $tbody = this.$form.find("tbody:visible");
	
	      $tbody.find('[name="questionIds[]"]').each(function () {
	        excludeIds.push($(this).val());
	      });
	
	      this.$modal.data('manager', this).modal();
	      $.get($target.data('url'), { excludeIds: excludeIds.join(','), type: $tbody.data('type') }, function (html) {
	        _this3.$modal.html(html);
	      });
	    }
	  }, {
	    key: 'deleteQuestion',
	    value: function deleteQuestion(event) {
	      event.stopPropagation();
	      var $target = $(event.currentTarget);
	      var id = $target.closest('tr').data('id');
	      var $tbody = $target.closest('tbody');
	      $tbody.find('[data-parent-id="' + id + '"]').remove();
	      $target.closest('tr').remove();
	      (0, _questionSubjective.questionSubjectiveRemask)(this.$form);
	      this.refreshSeqs();
	    }
	  }, {
	    key: 'batchDelete',
	    value: function batchDelete(event) {
	      if (this.$form.find('[data-role="batch-item"]:checked').length == 0) {
	        var $redmine = this.$form.find('.js-help-redmine');
	        if ($redmine) {
	          $redmine.text(Translator.trans('activity.testpaper_manage.question_required_error_hint')).show();;
	          setTimeout(function () {
	            $redmine.slideUp();
	          }, 3000);
	        } else {
	          (0, _notify2["default"])('danger', Translator.trans('activity.testpaper_manage.question_required_error_hint'));
	        }
	      }
	      var self = this;
	
	      this.$form.find('[data-role="batch-item"]:checked').each(function (index, item) {
	        var questionId = $(this).val();
	
	        if ($(this).closest('tr').data('type') == 'material') {
	          self.$form.find('[data-parent-id="' + questionId + '"]').remove();
	        }
	        $(this).closest('tr').remove();
	      });
	      (0, _questionSubjective.questionSubjectiveRemask)(this.$form);
	    }
	  }, {
	    key: 'previewQuestion',
	    value: function previewQuestion(event) {
	      event.preventDefault();
	      window.open($(event.currentTarget).data('url'), '_blank', "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
	    }
	  }, {
	    key: 'refreshSeqs',
	    value: function refreshSeqs() {
	      var seq = 1;
	      this.$form.find("tbody tr").each(function () {
	        var $tr = $(this);
	
	        if (!$tr.hasClass('have-sub-questions')) {
	          $tr.find('td.seq').html(seq);
	          seq++;
	        }
	      });
	
	      this.$form.find('[name="questionLength"]').val(seq - 1 > 0 ? seq - 1 : null);
	    }
	  }]);
	
	  return QuestionOperate;
	}();
	
	exports["default"] = QuestionOperate;

/***/ }),

/***/ "71e1df85d5928925f4b1":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var questionSubjectiveRemask = exports.questionSubjectiveRemask = function questionSubjectiveRemask($element) {
	  var hasSubjective = false;
	  var html = '';
	  var $subjectiveRemask = $(".js-subjective-remask");
	
	  $element.find('tbody tr').each(function () {
	    var type = $(this).data('type');
	    console.log(type);
	    if (type == 'essay') {
	      hasSubjective = true;
	    }
	  });
	  console.log(hasSubjective);
	  if (hasSubjective || $element.find('tbody tr').length == 0) {
	    $subjectiveRemask.html('');
	    return;
	  }
	
	  console.log($subjectiveRemask);
	
	  if ($subjectiveRemask.data('type') == 'homework') {
	    html = Translator.trans('activity.homework_manage.objective_question_hint');
	  } else {
	    html = Translator.trans('activity.homework_manage.pass_objective_question_hint');
	  }
	  $subjectiveRemask.html(html).removeClass('hidden');
	};

/***/ }),

/***/ "a12be7d90ead2917b889":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Homework = function () {
	  function Homework($iframeContent) {
	    _classCallCheck(this, Homework);
	
	    this.$homeworkModal = $('#modal', window.parent.document);
	    this.$questionPickedModal = $('#attachment-modal', window.parent.document);
	    this.$element = $iframeContent;
	    this.$step2_form = this.$element.find('#step2-form');
	    this.$step3_form = this.$element.find('#step3-form');
	    this.validator2 = null;
	    this.init();
	  }
	
	  _createClass(Homework, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      this.setValidateRule();
	      this.inItStep2form();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '[data-role="pick-item"]', function (event) {
	        return _this.showPickQuestion(event);
	      });
	      this.$questionPickedModal.on('shown.bs.modal', function () {
	        _this.$homeworkModal.hide();
	      });
	      this.$questionPickedModal.on('hidden.bs.modal', function () {
	        _this.$homeworkModal.show();
	        _this.$questionPickedModal.html('');
	        if (_this.validator2) {
	          _this.validator2.form();
	        }
	      });
	    }
	  }, {
	    key: 'initCkeditor',
	    value: function initCkeditor(validator) {
	      var editor = CKEDITOR.replace('homework-about-field', {
	        toolbar: 'Task',
	        filebrowserImageUploadUrl: $('#homework-about-field').data('imageUploadUrl')
	      });
	      editor.on('change', function () {
	        $('#homework-about-field').val(editor.getData());
	      });
	      editor.on('blur', function () {
	        validator.form();
	      });
	    }
	  }, {
	    key: 'showPickQuestion',
	    value: function showPickQuestion(event) {
	      var _this2 = this;
	
	      event.preventDefault();
	      var $btn = $(event.currentTarget);
	      var excludeIds = [];
	      $("#question-table-tbody").find('[name="questionIds[]"]').each(function () {
	        excludeIds.push($(this).val());
	      });
	      this.$questionPickedModal.modal().data('manager', this);
	      $.get($btn.data('url'), {
	        excludeIds: excludeIds.join(',')
	      }, function (html) {
	        _this2.$questionPickedModal.html(html);
	      });
	    }
	  }, {
	    key: 'inItStep2form',
	    value: function inItStep2form() {
	      var validator = this.$step2_form.validate({
	        onkeyup: false,
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          description: {
	            required: true
	          },
	          content: 'required',
	          'questionLength': {
	            required: true
	          }
	        },
	        messages: {
	          description: Translator.trans("activity.homework_manage.question_homework_hint"),
	          questionLength: Translator.trans("activity.homework_manage.question_required_error_hint")
	        }
	      });
	      this.validator2 = validator;
	      this.initCkeditor(validator);
	      this.$step2_form.data('validator', validator);
	    }
	  }, {
	    key: 'setValidateRule',
	    value: function setValidateRule() {
	      $.validator.addMethod("arithmeticFloat", function (value, element) {
	        return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
	      }, $.validator.format(Translator.trans("activity.homework_manage.arithmetic_float_error_hint")));
	
	      $.validator.addMethod("positiveInteger", function (value, element) {
	        return this.optional(element) || /^[1-9]\d*$/.test(value);
	      }, $.validator.format(Translator.trans("activity.homework_manage.positive_integer_error_hint")));
	
	      $.validator.addMethod("DateAndTime", function (value, element) {
	        var reg = /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/;
	        return this.optional(element) || reg.test(value);
	      }, $.validator.format(Translator.trans("activity.homework_manage.date_and_time_error_hint:mm")));
	    }
	  }]);
	
	  return Homework;
	}();
	
	exports["default"] = Homework;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _questionOperate = __webpack_require__("1e0cf618bc778b8ab554");
	
	var _questionOperate2 = _interopRequireDefault(_questionOperate);
	
	var _create = __webpack_require__("a12be7d90ead2917b889");
	
	var _create2 = _interopRequireDefault(_create);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $from = $('#step2-form');
	new _create2["default"]($('#iframe-content'));
	new _batchSelect2["default"]($from);
	new _questionOperate2["default"]($from, $("#attachment-modal", window.parent.document));

/***/ })

});
//# sourceMappingURL=index.js.map