webpackJsonp(["app/js/testpaper-manage/questions/index"],{

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

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esEventEmitter = __webpack_require__("63fff8fb24f3bd1f61cd");
	
	var _esEventEmitter2 = _interopRequireDefault(_esEventEmitter);
	
	__webpack_require__("b3c50df5d8bf6315aeba");
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _questionOperate = __webpack_require__("1e0cf618bc778b8ab554");
	
	var _questionOperate2 = _interopRequireDefault(_questionOperate);
	
	var _manage = __webpack_require__("65c0e24ccad86ee24949");
	
	var _manage2 = _interopRequireDefault(_manage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $testpaperItemsManager = $('#testpaper-items-manager');
	new _questionOperate2["default"]($testpaperItemsManager, $("#modal"));
	new _manage2["default"]($testpaperItemsManager);
	new _batchSelect2["default"]($testpaperItemsManager);

/***/ }),

/***/ "65c0e24ccad86ee24949":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	    value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionManage = function () {
	    function QuestionManage($element) {
	        _classCallCheck(this, QuestionManage);
	
	        this.$element = $element;
	        this.$button = this.$element.find('[data-role="pick-item"]');
	        this.$typeNav = this.$element.find('#testpaper-question-nav');
	        this.$modal = $('#testpaper-confirm-modal');
	        this.currentType = this.$typeNav.find('.active').children().data('type');
	        this.questions = [];
	        this._initEvent();
	    }
	
	    _createClass(QuestionManage, [{
	        key: '_initEvent',
	        value: function _initEvent() {
	            var _this = this;
	
	            this.$button.on('click', function (event) {
	                return _this._showPickerModal(event);
	            });
	            this.$typeNav.on('click', 'li', function (event) {
	                return _this._changeNav(event);
	            });
	            this.$element.on('click', '.js-request-save', function (event) {
	                return _this._confirmSave(event);
	            });
	            this.$modal.on('click', '.js-confirm-submit', function (event) {
	                return _this._submitSave(event);
	            });
	        }
	    }, {
	        key: '_showPickerModal',
	        value: function _showPickerModal(event) {
	            var excludeIds = [];
	            $('[data-type="' + this.currentType + '"]').find('[name="questionIds[]"]').each(function () {
	                excludeIds.push($(this).val());
	            });
	
	            var $modal = $("#modal").modal();
	            $modal.data('manager', this);
	            $.get(this.$button.data('url'), { excludeIds: excludeIds.join(','), type: this.currentType }, function (html) {
	                $modal.html(html);
	            });
	        }
	    }, {
	        key: '_changeNav',
	        value: function _changeNav(event) {
	            var $target = $(event.currentTarget);
	            var type = $target.children().data('type');
	            this.currentType = type;
	
	            this.$typeNav.find('li').removeClass('active');
	            $target.addClass('active');
	
	            this.$element.find('[data-role="question-body"]').addClass('hide');
	            this.$element.find('#testpaper-items-' + type).removeClass('hide');
	            this.$element.find('[data-role="batch-select"]').prop('checked', false);
	            this.$element.find('[data-role="batch-item"]').prop('checked', false);
	        }
	    }, {
	        key: '_confirmSave',
	        value: function _confirmSave(event) {
	            var isOk = this._validateScore();
	
	            if (!isOk) {
	                return;
	            }
	
	            if ($('[name="passedScore"]').length > 0) {
	                var passedScoreErrorMsg = $('.passedScoreDiv').siblings('.help-block').html();
	                if ($.trim(passedScoreErrorMsg) != '') {
	                    return;
	                }
	            }
	
	            var stats = this._calTestpaperStats();
	
	            if ($('[name="passedScore"]').length > 0) {
	                var passedScore = $('input[name="passedScore"]').val();
	                if (passedScore > stats.total.score) {
	                    (0, _notify2["default"])('danger', Translator.trans('activity.testpaper_manage.setting_pass_score_error_hint', { 'passedScore': passedScore, 'totalScore': stats.total.score }));
	                    return;
	                }
	            }
	
	            var html = '';
	            $.each(stats, function (index, statsItem) {
	                var tr = "<tr>";
	                tr += "<td>" + statsItem.name + "</td>";
	                tr += "<td>" + statsItem.count + "</td>";
	                tr += "<td>" + statsItem.score.toFixed(1) + "</td>";
	                tr += "</tr>";
	                html += tr;
	            });
	
	            this.$modal.find('.detail-tbody').html(html);
	
	            this.$modal.modal('show');
	        }
	    }, {
	        key: '_validateScore',
	        value: function _validateScore() {
	            var isOk = true;
	
	            if (this.$element.find('[name="scores[]"]').length == 0) {
	                (0, _notify2["default"])('danger', Translator.trans('activity.testpaper_manage.question_required_error_hint'));
	                isOk = false;
	            }
	
	            this.$element.find('input[type="text"][name="scores[]"]').each(function () {
	                var score = $(this).val();
	
	                if (score == '0') {
	                    (0, _notify2["default"])('danger', 'activity.testpaper_manage.question_score_empty_hint');
	                    isOk = false;
	                }
	
	                if (!/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(score)) {
	                    (0, _notify2["default"])('danger', Translator.trans('activity.testpaper_manage.question_score_error_hint'));
	                    $(this).focus();
	                    isOk = false;
	                }
	            });
	
	            return isOk;
	        }
	    }, {
	        key: '_calTestpaperStats',
	        value: function _calTestpaperStats() {
	            var stats = {};
	            var self = this;
	
	            this.$typeNav.find('li').each(function () {
	                var type = $(this).find('a').data('type'),
	                    name = $(this).find('a').data('name');
	
	                stats[type] = { name: name, count: 0, score: 0, missScore: 0 };
	
	                self.$element.find('#testpaper-items-' + type).find('[name="scores[]"]').each(function () {
	                    var itemType = $(this).closest('tr').data('type');
	                    var score = itemType == 'material' ? 0 : parseFloat($(this).val());
	                    var question = {};
	
	                    if (itemType != 'material') {
	                        stats[type]['count']++;
	                    }
	
	                    stats[type]['score'] += score;
	                    stats[type]['missScore'] = parseFloat($(this).data('miss-score'));
	
	                    var questionId = $(this).closest('tr').data('id');
	
	                    question['id'] = questionId;
	                    question['score'] = score;
	                    question['missScore'] = parseFloat($(this).data('miss-score'));
	                    question['type'] = type;
	
	                    self.questions.push(question);
	                });
	            });
	
	            var total = { name: Translator.trans('activity.testpaper_manage.question_total_score'), count: 0, score: 0 };
	            $.each(stats, function (index, statsItem) {
	                total.count += statsItem.count;
	                total.score += statsItem.score;
	            });
	
	            stats.total = total;
	
	            return stats;
	        }
	    }, {
	        key: '_submitSave',
	        value: function _submitSave(event) {
	            var passedScore = 0;
	            var $target = $(event.currentTarget);
	            if ($('input[name="passedScore"]:visible').length > 0) {
	                passedScore = $('input[name="passedScore"]').val();
	            }
	
	            $target.button('loading').addClass('disabled');
	
	            $.post(this.$element.attr('action'), { questions: this.questions, passedScore: passedScore }, function (result) {
	                if (result["goto"]) {
	                    window.location.href = result["goto"];
	                }
	            });
	        }
	    }]);
	
	    return QuestionManage;
	}();
	
	exports["default"] = QuestionManage;

/***/ })

});
//# sourceMappingURL=index.js.map