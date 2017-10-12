webpackJsonp(["app/js/testpaper/do-test/index"],{

/***/ "8492817a6b6ebd299565":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var ChoiceQuesiton = function () {
		function ChoiceQuesiton() {
			_classCallCheck(this, ChoiceQuesiton);
		}
	
		_createClass(ChoiceQuesiton, [{
			key: 'getAnswer',
			value: function getAnswer(questionId) {
				var answers = [];
				$('input[name=' + questionId + ']:checked').each(function () {
					answers.push($(this).val());
				});
	
				return answers;
			}
		}]);
	
		return ChoiceQuesiton;
	}();
	
	exports["default"] = ChoiceQuesiton;

/***/ }),

/***/ "3515d355d43c1a043be1":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DetermineQuestion = function () {
		function DetermineQuestion() {
			_classCallCheck(this, DetermineQuestion);
		}
	
		_createClass(DetermineQuestion, [{
			key: 'getAnswer',
			value: function getAnswer(questionId) {
				var answers = [];
	
				$('input[name=' + questionId + ']:checked').each(function () {
					answers.push($(this).val());
				});
	
				return answers;
			}
		}]);
	
		return DetermineQuestion;
	}();
	
	exports["default"] = DetermineQuestion;

/***/ }),

/***/ "d43f35b4f73d35eb967a":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var EssayQuestion = function () {
		function EssayQuestion() {
			_classCallCheck(this, EssayQuestion);
		}
	
		_createClass(EssayQuestion, [{
			key: 'getAnswer',
			value: function getAnswer(questionId) {
				var answers = [];
				var value = $('[name=' + questionId + ']').val();
				answers.push(value);
	
				return answers;
			}
		}, {
			key: 'getAttachment',
			value: function getAttachment(questionId) {
				var attachment = [];
				var fileId = $('[name=' + questionId + ']').parent().find('[data-role="fileId"]').val();
	
				if (fileId != '') {
					attachment.push(fileId);
				}
	
				return attachment;
			}
		}]);
	
		return EssayQuestion;
	}();
	
	exports["default"] = EssayQuestion;

/***/ }),

/***/ "936bfc70bea5be864cc4":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var FillQuestion = function () {
		function FillQuestion() {
			_classCallCheck(this, FillQuestion);
		}
	
		_createClass(FillQuestion, [{
			key: 'getAnswer',
			value: function getAnswer(questionId) {
				var answers = [];
				$('input[name=' + questionId + ']').each(function () {
					answers.push($(this).val());
				});
	
				return answers;
			}
		}]);
	
		return FillQuestion;
	}();
	
	exports["default"] = FillQuestion;

/***/ }),

/***/ "2a56fd48b3e3533b8e82":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var SingleChoiceQuestion = function () {
		function SingleChoiceQuestion() {
			_classCallCheck(this, SingleChoiceQuestion);
		}
	
		_createClass(SingleChoiceQuestion, [{
			key: 'getAnswer',
			value: function getAnswer(questionId) {
				var answers = [];
				$('input[name=' + questionId + ']:checked').each(function () {
					answers.push($(this).val());
				});
	
				return answers;
			}
		}]);
	
		return SingleChoiceQuestion;
	}();
	
	exports["default"] = SingleChoiceQuestion;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _doTestBase = __webpack_require__("4428b108ee5aeb4e86ba");
	
	var _doTestBase2 = _interopRequireDefault(_doTestBase);
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	var _part = __webpack_require__("f898520c5384ef4c819c");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var DoTestpaper = function (_DoTestBase) {
	  _inherits(DoTestpaper, _DoTestBase);
	
	  function DoTestpaper($container) {
	    _classCallCheck(this, DoTestpaper);
	
	    var _this = _possibleConstructorReturn(this, (DoTestpaper.__proto__ || Object.getPrototypeOf(DoTestpaper)).call(this, $container));
	
	    _this.$timePauseDialog = _this.$container.find('#time-pause-dialog');
	    _this.$timer = $container.find('.js-testpaper-timer');
	    _this._init();
	    return _this;
	  }
	
	  _createClass(DoTestpaper, [{
	    key: '_init',
	    value: function _init() {
	      var _this2 = this;
	
	      (0, _part.initScrollbar)();
	      (0, _part.initWatermark)();
	      (0, _part.testpaperCardFixed)();
	      (0, _part.testpaperCardLocation)();
	      (0, _part.onlyShowError)();
	      this._initTimer();
	      this.$container.on('click', '.js-btn-pause', function (event) {
	        return _this2._clickBtnPause(event);
	      });
	      this.$container.on('click', '.js-btn-resume', function (event) {
	        return _this2._clickBtnReume(event);
	      });
	    }
	  }, {
	    key: '_initTimer',
	    value: function _initTimer() {
	      var _this3 = this;
	
	      if (this.$timer) {
	        this.$timer.timer({
	          countdown: true,
	          duration: this.$timer.data('time'),
	          format: '%H:%M:%S',
	          callback: function callback() {
	            _this3.$container.find('#time-finish-dialog').modal('show');
	            clearInterval(_this3.$usedTimer);
	            _this3.usedTime = _this3.$timer.data('time') / 60;
	            if ($('input[name="preview"]').length == 0) {
	              _this3._submitTest(_this3.$container.find('[data-role="paper-submit"]').data('url'));
	            }
	          },
	          repeat: true,
	          start: function start() {
	            _this3.usedTime = 0;
	          }
	        });
	      }
	    }
	  }, {
	    key: '_clickBtnPause',
	    value: function _clickBtnPause(event) {
	      var $btn = $(event.currentTarget).toggleClass('active');
	      if ($btn.hasClass('active')) {
	        this.$timer.timer('pause');
	        clearInterval(this.$usedTimer);
	        this.$timePauseDialog.modal('show');
	      } else {
	        this.$timer.timer('resume');
	        this._initUsedTimer();
	        this.$timePauseDialog.modal('hide');
	      }
	    }
	  }, {
	    key: '_clickBtnReume',
	    value: function _clickBtnReume(event) {
	      this.$timer.timer('resume');
	      this._initUsedTimer();
	      this.$container.find('.js-btn-pause').removeClass('active');
	      this.$timePauseDialog.modal('hide');
	    }
	  }]);
	
	  return DoTestpaper;
	}(_doTestBase2["default"]);
	
	new DoTestpaper($('.js-task-testpaper-body'));
	new _attachmentActions2["default"]($('.js-task-testpaper-body'));

/***/ }),

/***/ "45d3c796d523fa97ecd2":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CopyDeny = function CopyDeny() {
	  var $element = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : $('html');
	
	  _classCallCheck(this, CopyDeny);
	
	  $element.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);
	};
	
	exports["default"] = CopyDeny;

/***/ }),

/***/ "4428b108ee5aeb4e86ba":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _questionTypeBuilder = __webpack_require__("2d148eb38b93bb0ef45c");
	
	var _questionTypeBuilder2 = _interopRequireDefault(_questionTypeBuilder);
	
	var _copyDeny = __webpack_require__("45d3c796d523fa97ecd2");
	
	var _copyDeny2 = _interopRequireDefault(_copyDeny);
	
	var _activityEmitter = __webpack_require__("da32dea28c2b82c7aab1");
	
	var _activityEmitter2 = _interopRequireDefault(_activityEmitter);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DoTestBase = function () {
	  function DoTestBase($container) {
	    _classCallCheck(this, DoTestBase);
	
	    this.$container = $container;
	    this.answers = {};
	    this.usedTime = 0;
	    this.$form = $container.find('form');
	    this._initEvent();
	    this._initUsedTimer();
	    this._isCopy();
	    this._alwaysSave();
	  }
	
	  _createClass(DoTestBase, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$container.on('focusin', 'textarea', function (event) {
	        return _this._showEssayInputEditor(event);
	      });
	      this.$container.on('click', '[data-role="test-suspend"],[data-role="paper-submit"]', function (event) {
	        return _this._btnSubmit(event);
	      });
	      this.$container.on('click', '.js-testpaper-question-list li', function (event) {
	        return _this._choiceList(event);
	      });
	      this.$container.on('click', '*[data-anchor]', function (event) {
	        return _this._quick2Question(event);
	      });
	      this.$container.find('.js-testpaper-question-label').on('click', 'input', function (event) {
	        return _this._choiceLable(event);
	      });
	      this.$container.on('click', '.js-marking', function (event) {
	        return _this._markingToggle(event);
	      });
	      this.$container.on('click', '.js-favorite', function (event) {
	        return _this._favoriteToggle(event);
	      });
	      this.$container.on('click', '.js-analysis', function (event) {
	        return _this._analysisToggle(event);
	      });
	      this.$container.on('blur', '[data-type="fill"]', function (event) {
	        return _this.fillChange(event);
	      });
	    }
	  }, {
	    key: '_isCopy',
	    value: function _isCopy() {
	      var isCopy = this.$container.find('.js-testpaper-body').data('copy');
	      if (isCopy) {
	        new _copyDeny2["default"]();
	      }
	    }
	  }, {
	    key: 'fillChange',
	    value: function fillChange(event) {
	      var $input = $(event.currentTarget);
	      this._renderBtnIndex($input.attr('name'), $input.val() ? true : false);
	    }
	  }, {
	    key: '_markingToggle',
	    value: function _markingToggle(event) {
	      var $current = $(event.currentTarget).addClass('hidden');
	      $current.siblings('.js-marking.hidden').removeClass('hidden');
	      var id = $current.closest('.js-testpaper-question').attr('id');
	
	      $('[data-anchor="#' + id + '"]').find('.js-marking-card').toggleClass("hidden");
	    }
	  }, {
	    key: '_favoriteToggle',
	    value: function _favoriteToggle(event) {
	      var $current = $(event.currentTarget);
	      var targetType = $current.data('targetType');
	      var targetId = $current.data('targetId');
	
	      $.post($current.data('url'), { targetType: targetType, targetId: targetId }, function (response) {
	        $current.addClass('hidden').siblings('.js-favorite.hidden').data('url', response.url);
	        $current.addClass('hidden').siblings('.js-favorite.hidden').removeClass('hidden');
	      }).error(function (response) {
	        (0, _notify2["default"])('error', response.error.message);
	      });
	    }
	  }, {
	    key: '_analysisToggle',
	    value: function _analysisToggle(event) {
	      var $current = $(event.currentTarget);
	      $current.addClass('hidden');
	      $current.siblings('.js-analysis.hidden').removeClass('hidden');
	      $current.closest('.js-testpaper-question').find('.js-testpaper-question-analysis').slideToggle();
	    }
	  }, {
	    key: '_initUsedTimer',
	    value: function _initUsedTimer() {
	      var self = this;
	      this.$usedTimer = window.setInterval(function () {
	        self.usedTime += 1;
	      }, 1000);
	    }
	  }, {
	    key: '_choiceLable',
	    value: function _choiceLable(event) {
	      var $target = $(event.currentTarget);
	      var $lableContent = $target.closest('.js-testpaper-question-label');
	      this.changeInput($lableContent, $target);
	    }
	  }, {
	    key: '_choiceList',
	    value: function _choiceList(event) {
	      var $target = $(event.currentTarget);
	      var index = $target.index();
	      var $lableContent = $target.closest('.js-testpaper-question').find('.js-testpaper-question-label');
	      var $input = $lableContent.find('label').eq(index).find('input');
	      $input.prop('checked', !$input.prop('checked')).change();
	      this.changeInput($lableContent, $input);
	    }
	  }, {
	    key: 'changeInput',
	    value: function changeInput($lableContent, $input) {
	      var num = 0;
	      $lableContent.find('label').each(function (index, item) {
	        if ($(item).find('input').prop('checked')) {
	          $(item).addClass('active');
	          num++;
	        } else {
	          $(item).removeClass('active');
	        }
	      });
	      var questionId = $input.attr('name');
	      this._renderBtnIndex(questionId, num > 0 ? true : false);
	    }
	  }, {
	    key: '_renderBtnIndex',
	    value: function _renderBtnIndex(idNum) {
	      var done = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var doing = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	
	      var $btn = $('[data-anchor="#question' + idNum + '"]');
	      if (done) {
	        $btn.addClass('done');
	      } else {
	        $btn.removeClass('done');
	      }
	      if (doing) {
	        $btn.addClass('doing').siblings('.doing').removeClass('doing');
	      } else {
	        $btn.removeClass('doing');
	      }
	    }
	  }, {
	    key: '_showEssayInputEditor',
	    value: function _showEssayInputEditor(event) {
	      var _this2 = this;
	
	      var $shortTextarea = $(event.currentTarget);
	
	      if ($shortTextarea.hasClass('essay-input-short')) {
	
	        event.preventDefault();
	        event.stopPropagation();
	        $(this).blur();
	        var $longTextarea = $shortTextarea.siblings('.essay-input-long');
	        var $textareaBtn = $longTextarea.siblings('.essay-input-btn');
	
	        $shortTextarea.hide();
	        $longTextarea.show();
	        $textareaBtn.show();
	
	        var editor = CKEDITOR.replace($longTextarea.attr('id'), {
	          toolbar: 'Minimal',
	          filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
	        });
	
	        editor.on('blur', function (e) {
	          editor.updateElement();
	          setTimeout(function () {
	            $longTextarea.val(editor.getData());
	            $longTextarea.change();
	            $longTextarea.val() ? _this2._renderBtnIndex($longTextarea.attr('name'), true) : _this2._renderBtnIndex($longTextarea.attr('name'), false);
	          }, 1);
	        });
	
	        editor.on('instanceReady', function (e) {
	          this.focus();
	
	          $textareaBtn.one('click', function () {
	            $shortTextarea.val($(editor.getData()).text());
	            editor.destroy();
	            $longTextarea.hide();
	            $textareaBtn.hide();
	            $shortTextarea.show();
	          });
	        });
	
	        editor.on('key', function () {
	          editor.updateElement();
	          setTimeout(function () {
	            $longTextarea.val(editor.getData());
	            $longTextarea.change();
	          }, 1);
	        });
	
	        editor.on('insertHtml', function (e) {
	          editor.updateElement();
	          setTimeout(function () {
	            $longTextarea.val(editor.getData());
	            $longTextarea.change();
	          }, 1);
	        });
	      }
	    }
	  }, {
	    key: '_quick2Question',
	    value: function _quick2Question(event) {
	      var $target = $(event.currentTarget);
	      window.location.hash = $target.data('anchor');
	    }
	  }, {
	    key: '_suspendSubmit',
	    value: function _suspendSubmit(url) {
	      var values = this._getAnswers();
	      var attachments = this._getAttachments();
	
	      $.post(url, { data: values, usedTime: this.usedTime, attachments: attachments }).done(function (response) {}).error(function (response) {
	        (0, _notify2["default"])('error', response.error.message);
	      });
	    }
	  }, {
	    key: '_btnSubmit',
	    value: function _btnSubmit(event) {
	      var $target = $(event.currentTarget);
	      $target.button('loading');
	      this._submitTest($target.data('url'), $target.data('goto'));
	    }
	  }, {
	    key: '_submitTest',
	    value: function _submitTest(url) {
	      var toUrl = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	
	      var values = this._getAnswers();
	      var emitter = new _activityEmitter2["default"]();
	      var attachments = this._getAttachments();
	
	      $.post(url, { data: values, usedTime: this.usedTime, attachments: attachments }).done(function (response) {
	        if (response.result) {
	          emitter.emit('finish', { data: '' });
	        }
	
	        if (toUrl != '' || response["goto"] != '') {
	          window.location.href = toUrl;
	        } else if (response["goto"] != '') {
	          window.location.href = response["goto"];
	        } else if (response.message != '') {
	          (0, _notify2["default"])('error', response.message);
	        }
	      }).error(function (response) {
	        (0, _notify2["default"])('error', response.error.message);
	      });
	    }
	  }, {
	    key: '_getAnswers',
	    value: function _getAnswers() {
	      var values = {};
	
	      $('*[data-type]').each(function (index) {
	        var questionId = $(this).attr('name');
	        var type = $(this).data('type');
	        var questionTypeBuilder = _questionTypeBuilder2["default"].getTypeBuilder(type);
	        var answer = questionTypeBuilder.getAnswer(questionId);
	        values[questionId] = answer;
	      });
	
	      return JSON.stringify(values);
	    }
	  }, {
	    key: '_getAttachments',
	    value: function _getAttachments() {
	      var attachments = {};
	
	      $('[data-type="essay"]').each(function (index) {
	        var questionId = $(this).attr('name');
	        var questionTypeBuilder = _questionTypeBuilder2["default"].getTypeBuilder('essay');
	
	        var attachment = questionTypeBuilder.getAttachment(questionId);
	        attachments[questionId] = attachment;
	      });
	
	      return attachments;
	    }
	  }, {
	    key: '_alwaysSave',
	    value: function _alwaysSave() {
	      if ($('input[name="testSuspend"]').length > 0) {
	        var self = this;
	        var url = $('input[name="testSuspend"]').data('url');
	        setInterval(function () {
	          self._suspendSubmit(url);
	          var currentTime = new Date().getHours() + ':' + new Date().getMinutes() + ':' + new Date().getSeconds();
	          (0, _notify2["default"])('success', currentTime + Translator.trans('testpaper.widget.save_success_hint'));
	        }, 3 * 60 * 1000);
	      }
	    }
	  }]);
	
	  return DoTestBase;
	}();
	
	exports["default"] = DoTestBase;

/***/ }),

/***/ "f898520c5384ef4c819c":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	exports.initWatermark = exports.onlyShowError = exports.testpaperCardLocation = exports.testpaperCardFixed = exports.initScrollbar = undefined;
	
	__webpack_require__("9a5c59a43068776403d1");
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var initScrollbar = exports.initScrollbar = function initScrollbar() {
		var $paneCard = $('.js-panel-card');
		$paneCard.perfectScrollbar();
		$paneCard.perfectScrollbar('update');
	};
	
	var testpaperCardFixed = exports.testpaperCardFixed = function testpaperCardFixed() {
		console.log('ok');
		if ((0, _utils.isMobileDevice)()) return;
	
		var $testpaperCard = $(".js-testpaper-card");
		if ($testpaperCard.length <= 0) {
			return;
		}
		var testpaperCard_top = $testpaperCard.offset().top;
		$(window).scroll(function (event) {
			var scrollTop = $(window).scrollTop();
			if (scrollTop >= testpaperCard_top) {
				$testpaperCard.addClass('affix');
			} else {
				$testpaperCard.removeClass('affix');
			}
		});
	};
	
	var testpaperCardLocation = exports.testpaperCardLocation = function testpaperCardLocation() {
		$('.js-btn-index').click(function (event) {
			var $btn = $(event.currentTarget);
			if ($('.js-testpaper-heading').length <= 0) {
				$btn.addClass('doing').siblings('.doing').removeClass('doing');
			}
		});
	};
	
	var onlyShowError = exports.onlyShowError = function onlyShowError() {
		$('#showWrong').change(function (event) {
			var $current = $(event.currentTarget);
			$('.js-answer-notwrong').each(function (index, item) {
				var $item = $($(item).data('anchor'));
				var $itemParent = $item.closest('.js-testpaper-question-block');
				if ($current.prop('checked')) {
					$item.hide();
					if ($itemParent.find('.js-testpaper-question:visible').length <= 0) {
						$itemParent.hide();
					}
				} else {
					$item.show();
					$itemParent.show();
				}
			});
			initScrollbar();
		});
	};
	
	var initWatermark = exports.initWatermark = function initWatermark() {
		var $testpaperWatermark = $('.js-testpaper-watermark');
		if ($testpaperWatermark.length > 0) {
			$.get($testpaperWatermark.data('watermark-url'), function (response) {
				$testpaperWatermark.each(function () {
					$(this).WaterMark({
						'yPosition': 'center',
						'style': { 'font-size': 10 },
						'opacity': 0.6,
						'contents': response
					});
				});
			});
		}
	};

/***/ }),

/***/ "2d148eb38b93bb0ef45c":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _choiceQuestion = __webpack_require__("8492817a6b6ebd299565");
	
	var _choiceQuestion2 = _interopRequireDefault(_choiceQuestion);
	
	var _determineQuestion = __webpack_require__("3515d355d43c1a043be1");
	
	var _determineQuestion2 = _interopRequireDefault(_determineQuestion);
	
	var _essayQuestion = __webpack_require__("d43f35b4f73d35eb967a");
	
	var _essayQuestion2 = _interopRequireDefault(_essayQuestion);
	
	var _fillQuestion = __webpack_require__("936bfc70bea5be864cc4");
	
	var _fillQuestion2 = _interopRequireDefault(_fillQuestion);
	
	var _singleChoiceQuestion = __webpack_require__("2a56fd48b3e3533b8e82");
	
	var _singleChoiceQuestion2 = _interopRequireDefault(_singleChoiceQuestion);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionTypeBuilder = function () {
		function QuestionTypeBuilder(type) {
			_classCallCheck(this, QuestionTypeBuilder);
	
			this.type = type;
		}
	
		_createClass(QuestionTypeBuilder, null, [{
			key: 'getTypeBuilder',
			value: function getTypeBuilder(type) {
				var questionBuilder = null;
				switch (type) {
					case 'choice':
						questionBuilder = new _choiceQuestion2["default"]();
						break;
					case 'determine':
						questionBuilder = new _determineQuestion2["default"]();
						break;
					case 'essay':
						questionBuilder = new _essayQuestion2["default"]();
						break;
					case 'fill':
						questionBuilder = new _fillQuestion2["default"]();
						break;
					case 'single_choice':
						questionBuilder = new _singleChoiceQuestion2["default"]();
						break;
					case 'uncertain_choice':
						questionBuilder = new _singleChoiceQuestion2["default"]();
						break;
					default:
						questionBuilder = null;
				}
	
				return questionBuilder;
			}
		}]);
	
		return QuestionTypeBuilder;
	}();
	
	exports["default"] = QuestionTypeBuilder;

/***/ })

});
//# sourceMappingURL=index.js.map