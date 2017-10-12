webpackJsonp(["app/js/question-manage/picker/index"],{

/***/ "b7747c79a9f58b90eaab":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _questionSubjective = __webpack_require__("71e1df85d5928925f4b1");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionPicker = function () {
	  function QuestionPicker($questionPickerBody, $questionAppendForm) {
	    _classCallCheck(this, QuestionPicker);
	
	    this.$questionPickerBody = $questionPickerBody;
	    this.$questionPickerModal = this.$questionPickerBody.closest('.modal');
	    this.$questionAppendForm = $questionAppendForm;
	    this._initEvent();
	  }
	
	  _createClass(QuestionPicker, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$questionPickerBody.find('[data-role="search-btn"]').on('click', function (event) {
	        return _this.searchQuestion(event);
	      });
	      this.$questionPickerBody.find('[data-role="picked-item"]').on('click', function (event) {
	        return _this.pickItem(event);
	      });
	      this.$questionPickerBody.find('[data-role="preview-btn"]').on('click', function (event) {
	        return _this.questionPreview(event);
	      });
	      this.$questionPickerBody.find('.pagination a').on('click', function (event) {
	        return _this.pagination(event);
	      });
	
	      var $batchSelectSave = $('[data-role="batch-select-save"]', window.parent.document);
	      $batchSelectSave.on('click', function (event) {
	        return _this.batchSelectSave(event);
	      });
	    }
	  }, {
	    key: 'pagination',
	    value: function pagination(event) {
	      var _this2 = this;
	
	      var $btn = $(event.currentTarget);
	      $.get($btn.attr('href'), function (html) {
	        _this2.$questionPickerModal.html(html);
	      });
	      return false;
	    }
	  }, {
	    key: 'searchQuestion',
	    value: function searchQuestion(event) {
	      var _this3 = this;
	
	      event.preventDefault();
	      var $this = $(event.currentTarget);
	      var $form = $this.closest('form');
	      $.get($form.attr('action'), $form.serialize(), function (html) {
	        _this3.$questionPickerModal.html(html);
	      });
	    }
	  }, {
	    key: 'pickItem',
	    value: function pickItem(event) {
	      var $target = $(event.currentTarget);
	      var replace = parseInt($target.data('replace'));
	      var questionId = $target.data('questionId');
	      var questionIds = [];
	      questionIds.push(questionId);
	
	      this.pickItemPost($target.data('url'), questionIds, replace);
	    }
	  }, {
	    key: 'pickItemPost',
	    value: function pickItemPost(url, questionIds) {
	      var _this4 = this;
	
	      var replace = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	
	
	      $.post(url, { questionIds: questionIds }, function (html) {
	        if (replace) {
	          _this4.$questionAppendForm.find('tr[data-id="' + replace + '"]').replaceWith(html);
	          _this4.$questionAppendForm.find('tr[data-parent-id="' + replace + '"]').remove();
	        } else {
	          var $tbody = _this4.$questionAppendForm.find('tbody:visible');
	          //fix Firefox
	          if ($tbody.length <= 0) {
	            $tbody = _this4.$questionAppendForm.find('tbody');
	          }
	          $tbody.append(html).removeClass('hide');
	        }
	        _this4._refreshSeqs();
	        (0, _questionSubjective.questionSubjectiveRemask)(_this4.$questionAppendForm);
	        _this4.$questionPickerModal.modal('hide');
	      });
	    }
	  }, {
	    key: 'questionPreview',
	    value: function questionPreview(event) {
	      window.open($(event.currentTarget).data('url'), '_blank', "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
	    }
	  }, {
	    key: 'batchSelectSave',
	    value: function batchSelectSave(event) {
	      var $target = $(event.currentTarget);
	      var questionIds = [];
	      var url = $target.data('url');
	
	      if (this.$questionPickerBody.find('[data-role="batch-item"]:checked').length == 0) {
	        $('.js-choice-notice', window.parent.document).show();
	        return;
	      }
	
	      this.$questionPickerBody.find('[data-role="batch-item"]:checked').each(function (index, item) {
	        var questionId = $(this).data('questionId');
	        questionIds.push(questionId);
	      });
	
	      this.pickItemPost(url, questionIds, null);
	    }
	  }, {
	    key: '_refreshSeqs',
	    value: function _refreshSeqs() {
	      var seq = 1;
	      this.$questionAppendForm.find('tbody tr').each(function (index, item) {
	        var $tr = $(item);
	
	        if (!$tr.hasClass('have-sub-questions')) {
	          $tr.find('td.seq').html(seq);
	          seq++;
	        }
	      });
	      this.$questionAppendForm.find('[name="questionLength"]').val(seq - 1 > 0 ? seq - 1 : null);
	    }
	  }]);
	
	  return QuestionPicker;
	}();
	
	exports["default"] = QuestionPicker;

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

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _questionPicker = __webpack_require__("b7747c79a9f58b90eaab");
	
	var _questionPicker2 = _interopRequireDefault(_questionPicker);
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _selectLinkage = __webpack_require__("1be2a74362f00ba903a0");
	
	var _selectLinkage2 = _interopRequireDefault(_selectLinkage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _questionPicker2["default"]($('#question-picker-body'), $('#question-checked-form'));
	new _batchSelect2["default"]($('#question-picker-body'));
	
	new _selectLinkage2["default"]($('[name="courseId"]'), $('[name="lessonId"]'));

/***/ })

});
//# sourceMappingURL=index.js.map