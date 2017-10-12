webpackJsonp(["app/js/testpaper-manage/check/index"],{

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
	
	var _questionTypeBuilder = __webpack_require__("2d148eb38b93bb0ef45c");
	
	var _questionTypeBuilder2 = _interopRequireDefault(_questionTypeBuilder);
	
	var _part = __webpack_require__("f898520c5384ef4c819c");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	$.validator.addMethod("score", function (value, element) {
	  var isFloat = /^\d+(\.\d)?$/.test(value);
	  if (!isFloat) {
	    return false;
	  }
	
	  if (Number(value) <= Number($(element).data('score'))) {
	    return true;
	  } else {
	    return false;
	  }
	}, $.validator.format(Translator.trans('activity.testpaper_manage.marking_validate_error_hint')));
	
	var CheckTest = function () {
	  function CheckTest($container) {
	    _classCallCheck(this, CheckTest);
	
	    this.$container = $container;
	    this.checkContent = {};
	    this.$form = $container.find('form');
	    this.$dialog = $container.find('#testpaper-checked-dialog');
	    this.validator = null;
	    this._initEvent();
	    this._init();
	    this._initValidate();
	    (0, _part.testpaperCardFixed)();
	  }
	
	  _createClass(CheckTest, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$container.on('focusin', 'textarea', function (event) {
	        return _this._showEssayInputEditor(event);
	      });
	      this.$container.on('click', '[data-role="check-submit"]', function (event) {
	        return _this._submitValidate(event);
	      });
	      this.$container.on('click', '*[data-anchor]', function (event) {
	        return _this._quick2Question(event);
	      });
	      this.$dialog.on('click', '[data-role="finish-check"]', function (event) {
	        return _this._submit(event);
	      });
	      this.$dialog.on('change', 'select', function (event) {
	        return _this._teacherSayFill(event);
	      });
	    }
	  }, {
	    key: '_init',
	    value: function _init() {}
	  }, {
	    key: '_showEssayInputEditor',
	    value: function _showEssayInputEditor(event) {
	      var $shortTextarea = $(event.currentTarget);
	
	      if ($shortTextarea.hasClass('essay-teacher-say-short')) {
	
	        event.preventDefault();
	        event.stopPropagation();
	        $(this).blur();
	        var $longTextarea = $shortTextarea.siblings('.essay-teacher-say-long');
	        var $textareaBtn = $longTextarea.siblings('.essay-teacher-say-btn');
	
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
	    key: '_initValidate',
	    value: function _initValidate(event) {
	      this.validator = this.$form.validate();
	
	      if ($('*[data-score]:visible').length > 0) {
	        $('*[data-score]:visible').each(function (index) {
	          $(this).rules('add', {
	            required: true,
	            score: true,
	            min: 0,
	            messages: {
	              required: Translator.trans('activity.testpaper_manage.required_error_hint')
	            }
	          });
	        });
	      }
	    }
	  }, {
	    key: '_quick2Question',
	    value: function _quick2Question(event) {
	      var $target = $(event.currentTarget);
	      var position = $($target.data('anchor')).offset();
	      $(document).scrollTop(position.top - 55);
	    }
	  }, {
	    key: '_submitValidate',
	    value: function _submitValidate(event) {
	      var $target = $(event.currentTarget);
	      var scoreTotal = 0;
	
	      if (this.validator == undefined || this.validator.form()) {
	        var self = this;
	        $('*[data-score]').each(function () {
	          var content = {};
	          var questionId = $(this).data('id');
	
	          content['score'] = Number($(this).val());
	          content['teacherSay'] = $('[name="teacherSay_' + questionId + '"]').val();
	
	          self.checkContent[questionId] = content;
	          scoreTotal = scoreTotal + Number($(this).val());
	        });
	
	        var subjectiveScore = Number(this.$dialog.find('[name="objectiveScore"]').val());
	        var totalScore = Number(scoreTotal) + subjectiveScore;
	
	        this.$dialog.find('#totalScore').html(totalScore);
	        this.$dialog.modal('show');
	      }
	    }
	  }, {
	    key: '_submit',
	    value: function _submit(event) {
	
	      var $target = $(event.currentTarget);
	      var teacherSay = this.$dialog.find('textarea').val();
	      var passedStatus = this.$dialog.find('[name="passedStatus"]:checked').val();
	
	      $target.button('loading');
	      $.post($target.data('postUrl'), { result: this.checkContent, teacherSay: teacherSay, passedStatus: passedStatus }, function (response) {
	        window.location.reload();
	      });
	    }
	  }, {
	    key: '_teacherSayFill',
	    value: function _teacherSayFill(event) {
	      var $target = $(event.currentTarget);
	      var $option = $target.find('option:selected');
	
	      if ($option.val() == '') {
	        this.$dialog.find('textarea').val('');
	      } else {
	        this.$dialog.find('textarea').val($option.text());
	      }
	    }
	  }]);
	
	  return CheckTest;
	}();
	
	new CheckTest($('.container'));

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