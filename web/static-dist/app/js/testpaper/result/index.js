webpackJsonp(["app/js/testpaper/result/index"],{

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

/***/ "da32dea28c2b82c7aab1":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import postal from 'postal';
	import 'postal.federation';
	import 'postal.xframe';
	
	var ActivityEmitter = function () {
	  function ActivityEmitter() {
	    _classCallCheck(this, ActivityEmitter);
	
	    this.eventMap = {
	      receives: {}
	    };
	
	    this._registerIframeEvents();
	  }
	
	  _createClass(ActivityEmitter, [{
	    key: '_registerIframeEvents',
	    value: function _registerIframeEvents() {
	      postal.instanceId('activity');
	
	      postal.fedx.addFilter([{
	        channel: 'activity-events', //发送事件到task parent
	        topic: '#',
	        direction: 'out'
	      }, {
	        channel: 'task-events', //接收 task parent 的事件
	        topic: '#',
	        direction: 'in'
	      }]);
	
	      postal.fedx.signalReady();
	      this._registerReceiveTaskParentEvents();
	
	      return this;
	    }
	  }, {
	    key: '_registerReceiveTaskParentEvents',
	    value: function _registerReceiveTaskParentEvents() {
	      var _this = this;
	
	      postal.subscribe({
	        channel: 'task-events',
	        topic: '#',
	        callback: function callback(_ref) {
	          var event = _ref.event,
	              data = _ref.data;
	
	          var listeners = _this.eventMap.receives[event];
	          if (typeof listeners !== 'undefined') {
	            listeners.forEach(function (callback) {
	              return callback(data);
	            });
	          }
	        }
	      });
	    }
	
	    //发送事件到task
	
	  }, {
	    key: 'emit',
	    value: function emit(event, data) {
	      return new Promise(function (resolve, reject) {
	        var message = {
	          event: event,
	          data: data
	        };
	
	        postal.publish({
	          channel: 'activity-events',
	          topic: '#',
	          data: message
	        });
	
	        var channel = postal.channel('task-events');
	        var subscriber = channel.subscribe('#', function (data) {
	          if (data.error) {
	            reject(data.error);
	          } else {
	            resolve(data);
	          }
	          subscriber.unsubscribe();
	        });
	      });
	    }
	
	    //监听task的事件
	
	  }, {
	    key: 'receive',
	    value: function receive(event, callback) {
	      this.eventMap.receives[event] = this.eventMap.receives[event] || [];
	      this.eventMap.receives[event].push(callback);
	    }
	  }]);
	
	  return ActivityEmitter;
	}();
	
	export default ActivityEmitter;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _doTestBase = __webpack_require__("4428b108ee5aeb4e86ba");
	
	var _doTestBase2 = _interopRequireDefault(_doTestBase);
	
	var _part = __webpack_require__("f898520c5384ef4c819c");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	(0, _part.initScrollbar)();
	(0, _part.testpaperCardFixed)();
	(0, _part.testpaperCardLocation)();
	(0, _part.onlyShowError)();
	(0, _part.initWatermark)();
	
	new _doTestBase2.default($('.js-task-testpaper-body'));
	
	$('.js-testpaper-redo-timer').timer({
	  countdown: true,
	  duration: $('.js-testpaper-redo-timer').data('time'),
	  format: '%H:%M:%S',
	  callback: function callback() {
	    $('#finishPaper').attr('disabled', false);
	  },
	  repeat: true,
	  start: function start() {
	    self.usedTime = 0;
	  }
	});

/***/ }),

/***/ "45d3c796d523fa97ecd2":
/***/ (function(module, exports) {

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CopyDeny = function CopyDeny() {
	  var $element = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : $('html');
	
	  _classCallCheck(this, CopyDeny);
	
	  $element.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);
	};
	
	export default CopyDeny;

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
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
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
	        new _copyDeny2.default();
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
	        (0, _notify2.default)('error', response.error.message);
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
	
	      $.post(url, { data: values, usedTime: this.usedTime }).done(function (response) {}).error(function (response) {
	        (0, _notify2.default)('error', response.error.message);
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
	      var emitter = new _activityEmitter2.default();
	
	      $.post(url, { data: values, usedTime: this.usedTime }).done(function (response) {
	        if (response.result) {
	          emitter.emit('finish', { data: '' });
	        }
	
	        if (toUrl != '' || response.goto != '') {
	          window.location.href = toUrl;
	        } else if (response.goto != '') {
	          window.location.href = response.goto;
	        } else if (response.message != '') {
	          (0, _notify2.default)('error', response.message);
	        }
	      }).error(function (response) {
	        (0, _notify2.default)('error', response.error.message);
	      });
	    }
	  }, {
	    key: '_getAnswers',
	    value: function _getAnswers() {
	      var values = {};
	
	      $('*[data-type]').each(function (index) {
	        var questionId = $(this).attr('name');
	        var type = $(this).data('type');
	        var questionTypeBuilder = _questionTypeBuilder2.default.getTypeBuilder(type);
	        var answer = questionTypeBuilder.getAnswer(questionId);
	        values[questionId] = answer;
	      });
	
	      return JSON.stringify(values);
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
	          (0, _notify2.default)('success', currentTime + Translator.trans('testpaper.widget.save_success_hint'));
	        }, 3 * 60 * 1000);
	      }
	    }
	  }]);
	
	  return DoTestBase;
	}();
	
	//临时方案，libs/vendor.js这个方法没有起作用
	/*$(document).ajaxSend(function(a, b, c) {
	  if (c.type == 'POST') {
	    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
	  }
	});*/
	
	exports.default = DoTestBase;

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