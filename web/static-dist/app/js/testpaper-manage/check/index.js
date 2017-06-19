webpackJsonp(["app/js/testpaper-manage/check/index"],{

/***/ "9a5c59a43068776403d1":
/***/ (function(module, exports) {

	(function ($) {
	  $.fn.WaterMark = function (options) {
	    var settings = $.extend({
	      'duringTime': 5 * 60 * 1000,
	      'interval': 10 * 60 * 1000,
	      'isAlwaysShow': true,
	      'xPosition': 'center',
	      'yPosition': 'top',
	      'isUseRandomPos': false,
	      'opacity': 0.8,
	      'rotate': 45,
	      'style': {},
	      'contents': ''
	    }, options);
	
	    var showTimer;
	    var $thiz = $(this);
	    var minTopOffset = 40;
	    var minLeftOffset = 15;
	    var topOffset = minTopOffset;
	    var leftOffset = minLeftOffset;
	    var $watermarkDiv = null;
	
	    function genereateDiv() {
	      var IEversion = getInternetExplorerVersion();
	      $watermarkDiv = $('<div id="waterMark" class="watermark"></div>');
	      var rotate = 'rotate(' + settings.rotate + 'deg)';
	      $watermarkDiv.addClass('active');
	      $watermarkDiv.css({
	        opacity: settings.opacity,
	        '-webkit-transform': rotate,
	        '-moz-transform': rotate,
	        '-ms-transform': rotate,
	        '-o-transform': rotate,
	        'transform': rotate,
	        'filter': "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678, sizingMethod='auto expand')"
	      });
	      $watermarkDiv.css(settings.style);
	      if (IEversion >= 8 && IEversion < 9) {
	        $watermarkDiv.css({
	          'height': 60,
	          'filter': "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678, sizingMethod='auto expand')progid:DXImageTransform.Microsoft.Alpha(opacity=" + parseFloat(settings.opacity) * 100 + ")"
	        });
	      }
	      $watermarkDiv.html(settings.contents);
	      return $watermarkDiv;
	    }
	
	    function alwaysShow() {
	      displayWaterMark();
	    }
	
	    function displayWaterMark() {
	      getOffset();
	      $watermarkDiv.css({
	        'top': topOffset,
	        'left': leftOffset
	      });
	      $watermarkDiv.show();
	    }
	
	    function timeingShow() {
	      displayWaterMark();
	      showTimer = setInterval(function () {
	        displayWaterMark();
	        setTimeout(function () {
	          $watermarkDiv.hide();
	        }, settings.duringTime);
	      }, settings.interval);
	    }
	
	    function getOffset() {
	      if (settings.isUseRandomPos) {
	        setOffsetRandom();
	      } else {
	        setOffsetByPosition();
	      }
	    }
	
	    function setOffsetRandom() {
	      var maxTopOffset = $thiz.height() - $watermarkDiv.height() - minTopOffset;
	      var maxLeftOffset = $thiz.width() - $watermarkDiv.width() - minLeftOffset;
	
	      topOffset = Math.random() * maxTopOffset + minTopOffset;
	      leftOffset = Math.random() * maxLeftOffset;
	    }
	
	    function setOffsetByPosition() {
	      if (settings.xPosition == "left") {
	        leftOffset = minLeftOffset;
	      }
	      if (settings.xPosition == "center") {
	        leftOffset = ($thiz.width() - $watermarkDiv.width()) / 2;
	      }
	      if (settings.xPosition == "right") {
	        leftOffset = $thiz.width() - $watermarkDiv.width() - minLeftOffset;
	      }
	      if (settings.yPosition == "top") {
	        topOffset = minTopOffset;
	      }
	      if (settings.yPosition == "center") {
	        topOffset = ($thiz.height() - $watermarkDiv.height()) / 2 + minTopOffset;
	      }
	      if (settings.yPosition == "bottom") {
	        topOffset = $thiz.height() - $watermarkDiv.height() - minTopOffset;
	      }
	    }
	
	    function startShow() {
	      if (settings.isAlwaysShow) {
	        alwaysShow();
	      } else {
	        timeingShow();
	      }
	    }
	
	    function getInternetExplorerVersion() {
	      var rv = -1; // Return value assumes failure.
	      if (navigator.appName == 'Microsoft Internet Explorer') {
	        var ua = navigator.userAgent;
	        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
	        if (re.exec(ua) != null) rv = parseFloat(RegExp.$1);
	      }
	      return rv;
	    }
	
	    function init() {
	      $thiz.append(genereateDiv());
	
	      startShow();
	    }
	
	    init();
	  };
	})($);

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _questionTypeBuilder = __webpack_require__("2d148eb38b93bb0ef45c");
	
	var _questionTypeBuilder2 = _interopRequireDefault(_questionTypeBuilder);
	
	var _part = __webpack_require__("f898520c5384ef4c819c");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
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
	}, $.validator.format("分数只能是<=题目分数、且>=0的整数或者1位小数"));
	
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
	              required: "请输入分数"
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
	        window.location.href = $target.data('goto');
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
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import ChoiceQuesiton from '../../question/type/choice-question';
	import DetermineQuestion from '../../question/type/determine-question';
	import EssayQuestion from '../../question/type/essay-question';
	import FillQuestion from '../../question/type/fill-question';
	import SingleChoiceQuestion from '../../question/type/single-choice-question';
	import UncertainChoiceQuesiton from '../../question/type/single-choice-question';
	
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
						questionBuilder = new ChoiceQuesiton();
						break;
					case 'determine':
						questionBuilder = new DetermineQuestion();
						break;
					case 'essay':
						questionBuilder = new EssayQuestion();
						break;
					case 'fill':
						questionBuilder = new FillQuestion();
						break;
					case 'single_choice':
						questionBuilder = new SingleChoiceQuestion();
						break;
					case 'uncertain_choice':
						questionBuilder = new UncertainChoiceQuesiton();
						break;
					default:
						questionBuilder = null;
				}
	
				return questionBuilder;
			}
		}]);
	
		return QuestionTypeBuilder;
	}();
	
	export default QuestionTypeBuilder;

/***/ })

});