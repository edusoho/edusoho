webpackJsonp(["app/js/task/index"],{

/***/ "d14d05cad9e7abf02a5d":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var toggleIcon = exports.toggleIcon = function toggleIcon(target, $expandIconClass, $putIconClass) {
	  var $icon = target.find('.js-remove-icon');
	  var $text = target.find('.js-remove-text');
	  if ($icon.hasClass($expandIconClass)) {
	    $icon.removeClass($expandIconClass).addClass($putIconClass);
	    $text ? $text.text(Translator.trans('收起')) : '';
	  } else {
	    $icon.removeClass($putIconClass).addClass($expandIconClass);
	    $text ? $text.text(Translator.trans('展开')) : '';
	  }
	};
	
	var chapterAnimate = exports.chapterAnimate = function chapterAnimate() {
	  var delegateTarget = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
	  var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.js-task-chapter';
	  var $expandIconClass = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'es-icon-remove';
	  var $putIconClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'es-icon-anonymous-iconfont';
	
	  $(delegateTarget).on('click', target, function (event) {
	    var $this = $(event.currentTarget);
	    $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, "normal");
	    toggleIcon($this, $expandIconClass, $putIconClass);
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _task = __webpack_require__("2cb4f005d1a3626b7504");
	
	var _task2 = _interopRequireDefault(_task);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _task2["default"]({
	  element: $('body'),
	  mode: $('body').find('#js-hidden-data [name="mode"]').val()
	});

/***/ }),

/***/ "2cb4f005d1a3626b7504":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _sidebar = __webpack_require__("5f0af4efa4df981e1cb2");
	
	var _sidebar2 = _interopRequireDefault(_sidebar);
	
	var _taskUi = __webpack_require__("8564292a81bb30f6618a");
	
	var _taskUi2 = _interopRequireDefault(_taskUi);
	
	var _taskPipe = __webpack_require__("d1f69fe143d8968fb6c3");
	
	var _taskPipe2 = _interopRequireDefault(_taskPipe);
	
	var _esEventEmitter = __webpack_require__("63fff8fb24f3bd1f61cd");
	
	var _esEventEmitter2 = _interopRequireDefault(_esEventEmitter);
	
	var _esInfiniteScroll = __webpack_require__("e66ca5da7109f35e9051");
	
	var _esInfiniteScroll2 = _interopRequireDefault(_esInfiniteScroll);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var TaskShow = function (_Emitter) {
	  _inherits(TaskShow, _Emitter);
	
	  function TaskShow(_ref) {
	    var element = _ref.element,
	        mode = _ref.mode;
	
	    _classCallCheck(this, TaskShow);
	
	    var _this = _possibleConstructorReturn(this, (TaskShow.__proto__ || Object.getPrototypeOf(TaskShow)).call(this));
	
	    _this.element = $(element);
	    _this.mode = mode;
	
	    _this.ui = new _taskUi2["default"]({
	      element: '.js-task-dashboard-page'
	    });
	
	    _this.init();
	    return _this;
	  }
	
	  _createClass(TaskShow, [{
	    key: "init",
	    value: function init() {
	      this.initPlugin();
	      this.initSidebar();
	      if (this.mode != 'preview') {
	        this.initTaskPipe();
	        this.initLearnBtn();
	      }
	    }
	  }, {
	    key: "initPlugin",
	    value: function initPlugin() {
	      $('[data-toggle="tooltip"]').tooltip();
	      $('[data-toggle="popover"]').popover({
	        html: true,
	        trigger: 'hover'
	      });
	    }
	  }, {
	    key: "initLearnBtn",
	    value: function initLearnBtn() {
	      var _this2 = this;
	
	      this.element.on('click', '#learn-btn', function (event) {
	        $.post($('#learn-btn').data('url'), function (response) {
	          $('#modal').modal('show');
	          $('#modal').html(response);
	          $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
	          var $nextBtn = $('.js-next-mobile-btn');
	          if ($nextBtn.data('url')) {
	            $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
	          }
	          _this2.ui.learned();
	        });
	      });
	    }
	  }, {
	    key: "initTaskPipe",
	    value: function initTaskPipe() {
	      var _this3 = this;
	
	      this.eventEmitter = new _taskPipe2["default"](this.element.find('#task-content-iframe'));
	      this.eventEmitter.addListener('finish', function (response) {
	        _this3._receiveFinish(response);
	      });
	    }
	  }, {
	    key: "_receiveFinish",
	    value: function _receiveFinish(response) {
	      var _this4 = this;
	
	      if ($('input[name="task-result-status"]', $('#js-hidden-data')).val() != 'finish') {
	        $.get($(".js-learned-prompt").data('url'), function (html) {
	          $(".js-learned-prompt").attr('data-content', html);
	          _this4.ui.learnedWeakPrompt();
	          _this4.ui.learned();
	          _this4.sidebar.reload();
	          var $nextBtn = $('.js-next-mobile-btn');
	          if ($nextBtn.data('url')) {
	            $nextBtn.removeClass('disabled').attr('href', $nextBtn.data('url'));
	          }
	          $('input[name="task-result-status"]', $('#js-hidden-data')).val('finish');
	        });
	      }
	    }
	  }, {
	    key: "initSidebar",
	    value: function initSidebar() {
	      var _this5 = this;
	
	      this.sidebar = new _sidebar2["default"]({
	        element: this.element.find('#dashboard-sidebar'),
	        url: this.element.find('#js-hidden-data [name="plugins_url"]').val()
	      });
	      this.sidebar.on('popup', function (px, time) {
	        _this5.element.find('#dashboard-content').animate({
	          right: px
	        }, time);
	      }).on('fold', function (px, time) {
	        _this5.element.find('#dashboard-content').animate({
	          right: px
	        }, time);
	      }).on('task-list-loaded', function ($paneBody) {
	        var $box = $paneBody.parent();
	        var boxHeight = $box.height();
	        var bodyHeight = $paneBody.height();
	        var $activeItem = $paneBody.find('.task-item.active');
	        var top = $activeItem.position().top;
	        var standardPosition = (boxHeight - $activeItem.height()) / 2;
	
	        var infiniteScroll = new _esInfiniteScroll2["default"]({
	          context: document.getElementsByClassName('js-sidebar-pane ps-container')
	        });
	
	        if (bodyHeight - top < standardPosition) {
	          console.log('位置靠近底部，top偏移', top - standardPosition);
	          console.log(bodyHeight - boxHeight);
	          $box.scrollTop(bodyHeight - boxHeight);
	          return;
	        }
	        if (top > standardPosition) {
	          console.log('位置大于标准位置时，top偏移', top - standardPosition);
	          console.log(top, standardPosition);
	          $box.scrollTop(top - standardPosition);
	        }
	      });
	    }
	  }]);
	
	  return TaskShow;
	}(_esEventEmitter2["default"]);
	
	exports["default"] = TaskShow;

/***/ }),

/***/ "5f0af4efa4df981e1cb2":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _componentEmitter = __webpack_require__("17c25dd7d9d2615bc1d9");
	
	var _componentEmitter2 = _interopRequireDefault(_componentEmitter);
	
	var _chapterAnimate = __webpack_require__("d14d05cad9e7abf02a5d");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var TaskSidebar = function (_Emitter) {
	  _inherits(TaskSidebar, _Emitter);
	
	  function TaskSidebar(_ref) {
	    var element = _ref.element,
	        url = _ref.url;
	
	    _classCallCheck(this, TaskSidebar);
	
	    var _this = _possibleConstructorReturn(this, (TaskSidebar.__proto__ || Object.getPrototypeOf(TaskSidebar)).call(this));
	
	    _this.url = url;
	    _this.isManualOperation = true;
	    _this.element = $(element);
	    _this.init();
	    return _this;
	  }
	
	  _createClass(TaskSidebar, [{
	    key: 'init',
	    value: function init() {
	      var _this2 = this;
	
	      this.fixIconInChrome();
	      this.fetchPlugins().then(function (plugins) {
	        _this2.plugins = plugins;
	        _this2.renderToolbar();
	        _this2.renderPane();
	        _this2.element.hide().show();
	        _this2.bindEvent();
	      }).fail(function (error) {});
	    }
	  }, {
	    key: 'fetchPlugins',
	    value: function fetchPlugins() {
	      return $.post(this.url);
	    }
	
	    // 修复字体图标在chrome下，加载两次从而不能显示的问题
	
	  }, {
	    key: 'fixIconInChrome',
	    value: function fixIconInChrome() {
	      var html = '<i class="es-icon es-icon-chevronleft"></i>';
	      this.element.html(html);
	    }
	  }, {
	    key: 'renderToolbar',
	    value: function renderToolbar() {
	      var html = '\n    <div class="dashboard-toolbar">\n      <ul class="dashboard-toolbar-nav" id="dashboard-toolbar-nav">\n        ' + this.plugins.reduce(function (html, plugin) {
	        return html += '<li data-plugin="' + plugin.code + '" data-url="' + plugin.url + '"><a href="#"><div class="mbs es-icon ' + plugin.icon + '"></div>' + plugin.name + '</a></li>';
	      }, '') + '\n      </ul>\n    </div>';
	      this.element.html(html);
	    }
	  }, {
	    key: 'renderPane',
	    value: function renderPane() {
	      var html = this.plugins.reduce(function (html, plugin) {
	        return html += '<div data-pane="' + plugin.code + '" class=" ' + plugin.code + '-pane js-sidebar-pane" ><div class="' + plugin.code + '-pane-body js-sidebar-pane-body"></div></div>';
	      }, '');
	      this.element.append(html);
	    }
	  }, {
	    key: 'bindEvent',
	    value: function bindEvent() {
	      var _this3 = this;
	
	      this.element.find('#dashboard-toolbar-nav').on('click', 'li', function (event) {
	        var $btn = $(event.currentTarget);
	        var pluginCode = $btn.data('plugin');
	        var url = $btn.data('url');
	        var $pane = _this3.element.find('[data-pane="' + pluginCode + '"]');
	        var $paneBody = $pane.find('.js-sidebar-pane-body');
	        if (pluginCode === undefined || url === undefined) {
	          return;
	        }
	
	        if (_this3.isManualOperation) {
	          _this3.operationContent($btn);
	        }
	
	        if ($btn.data('loaded')) {
	          return;
	        }
	
	        $.get(url).then(function (html) {
	          $paneBody.html(html);
	          $pane.perfectScrollbar();
	          $btn.data('loaded', true);
	          _this3.listEvent();
	          _this3.isManualOperation = true;
	          _this3.emit($btn.data('plugin') + '-loaded', $paneBody);
	        });
	      });
	    }
	  }, {
	    key: 'operationContent',
	    value: function operationContent($btn) {
	      if ($btn.hasClass('active')) {
	        this.foldContent();
	        $btn.removeClass('active');
	        $('.dashboard-sidebar').removeClass('spread');
	      } else {
	        this.element.find('#dashboard-toolbar-nav li').removeClass('active');
	        $btn.addClass('active');
	        this.element.find('[data-pane]').hide();
	        this.element.find('[data-pane="' + $btn.data('plugin') + '"]').show();
	        this.popupContent();
	        $('.dashboard-sidebar').addClass('spread');
	      }
	    }
	  }, {
	    key: 'popupContent',
	    value: function popupContent() {
	      var time = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 500;
	
	      var side_right = '0px';
	      var width = $('#dashboard-sidebar').width();
	
	      var content_right = width + 35 + 'px';
	
	      this.emit('popup', content_right, time);
	      this.element.animate({
	        right: side_right
	      }, time);
	    }
	  }, {
	    key: 'foldContent',
	    value: function foldContent() {
	      var time = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 500;
	
	      var side_right = '-' + this.element.width() + 'px';
	      var content_right = '35px';
	
	      this.emit('fold', content_right, time);
	      this.element.animate({
	        right: side_right
	      }, time);
	    }
	  }, {
	    key: 'reload',
	    value: function reload() {
	      this.isManualOperation = false;
	      var $currentPane = this.element.find('.js-sidebar-pane:visible');
	      var pluginCode = $currentPane.data('pane');
	      $currentPane.undelegate();
	      this.element.find('#dashboard-toolbar-nav').children('[data-plugin="' + pluginCode + '"]').data('loaded', false).click();
	    }
	  }, {
	    key: 'listEvent',
	    value: function listEvent() {
	      if ($('.js-sidebar-pane:visible .task-list-pane-body').length) {
	        (0, _chapterAnimate.chapterAnimate)();
	      }
	    }
	  }]);
	
	  return TaskSidebar;
	}(_componentEmitter2["default"]);
	
	exports["default"] = TaskSidebar;

/***/ }),

/***/ "d1f69fe143d8968fb6c3":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	__webpack_require__("ee979a31290c346a6f6f");
	
	__webpack_require__("0f47cc4efffe23ee2a60");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var TaskPipe = function () {
	  function TaskPipe(element) {
	    _classCallCheck(this, TaskPipe);
	
	    this.element = $(element);
	    this.eventUrl = this.element.data('eventUrl');
	
	    if (this.eventUrl === undefined) {
	      throw Error('task event url is undefined');
	    }
	
	    this.eventDatas = {};
	    this.intervalId = null;
	    this.lastTime = this.element.data('lastTime');
	    this.eventMap = {
	      receives: {}
	    };
	
	    this._registerChannel();
	
	    if (this.element.data('eventEnable') == 1) {
	      this._initInterval();
	    }
	  }
	
	  _createClass(TaskPipe, [{
	    key: '_registerChannel',
	    value: function _registerChannel() {
	      var _this = this;
	
	      _postal2["default"].instanceId('task');
	
	      _postal2["default"].fedx.addFilter([{
	        channel: 'activity-events', //接收 activity iframe的事件
	        topic: '#',
	        direction: 'in'
	      }, {
	        channel: 'task-events', // 发送事件到activity iframe
	        topic: '#',
	        direction: 'out'
	      }]);
	
	      _postal2["default"].subscribe({
	        channel: 'activity-events',
	        topic: '#',
	        callback: function callback(_ref) {
	          var event = _ref.event,
	              data = _ref.data;
	
	          _this.eventDatas[event] = data;
	          _this._flush();
	        }
	      });
	
	      return this;
	    }
	  }, {
	    key: '_initInterval',
	    value: function _initInterval() {
	      var _this2 = this;
	
	      window.onbeforeunload = function () {
	        _this2._clearInterval();
	        _this2._flush();
	      };
	      this._clearInterval();
	      var minute = 60 * 1000;
	      this.intervalId = setInterval(function () {
	        return _this2._flush();
	      }, minute);
	    }
	  }, {
	    key: '_clearInterval',
	    value: function _clearInterval() {
	      clearInterval(this.intervalId);
	    }
	  }, {
	    key: '_flush',
	    value: function _flush() {
	      var _this3 = this;
	
	      var ajax = $.post(this.eventUrl, { data: { lastTime: this.lastTime, events: this.eventDatas } }).done(function (response) {
	        _this3._publishResponse(response);
	        _this3.eventDatas = {};
	        _this3.lastTime = response.lastTime;
	        if (response && response.result && response.result.status) {
	          var listners = _this3.eventMap.receives[response.result.status];
	          if (listners) {
	            for (var i = listners.length - 1; i >= 0; i--) {
	              var listner = listners[i];
	              listner(response);
	            }
	          }
	        }
	      }).fail(function (error) {});
	
	      return ajax;
	    }
	  }, {
	    key: '_publishResponse',
	    value: function _publishResponse(response) {
	      _postal2["default"].publish({
	        channel: 'task-events',
	        topic: '#',
	        data: { event: response.event, data: response.data }
	      });
	    }
	  }, {
	    key: 'addListener',
	    value: function addListener(event, callback) {
	      this.eventMap.receives[event] = this.eventMap.receives[event] || [];
	      this.eventMap.receives[event].push(callback);
	    }
	  }]);
	
	  return TaskPipe;
	}();
	
	exports["default"] = TaskPipe;

/***/ }),

/***/ "8564292a81bb30f6618a":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var TaskUi = function () {
	  function TaskUi(option) {
	    _classCallCheck(this, TaskUi);
	
	    this.element = $(option.element);
	    this.learningPrompt = this.element.find('.js-learning-prompt');
	    this.learnedPrompt = this.element.find('.js-learned-prompt');
	    this.learnprompt = this.element.find('.js-learn-prompt');
	    this.btnLearn = this.element.find('.js-btn-learn');
	  }
	
	  _createClass(TaskUi, [{
	    key: 'learnedWeakPrompt',
	    value: function learnedWeakPrompt() {
	      var _this = this;
	
	      this.learnprompt.removeClass('open');
	      this.learningPrompt.addClass('moveup');
	      window.setTimeout(function () {
	        _this.learningPrompt.removeClass('moveup');
	        _this.learnedPrompt.addClass('moveup');
	        _this.learnedPrompt.popover('show');
	
	        window.setTimeout(function () {
	          _this.learnedPrompt.popover('hide');
	        }, 2000);
	      }, 2000);
	    }
	  }, {
	    key: 'learned',
	    value: function learned() {
	      this.btnLearn.addClass('active');
	    }
	  }]);
	
	  return TaskUi;
	}();
	
	exports["default"] = TaskUi;

/***/ }),

/***/ "e66ca5da7109f35e9051":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("8f3ec98312b1f1f6bafb");
	
	__webpack_require__("c5e642028fa5ee5a3554");
	
	var _esEventEmitter = __webpack_require__("63fff8fb24f3bd1f61cd");
	
	var _esEventEmitter2 = _interopRequireDefault(_esEventEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var ESInfiniteScroll = function (_Emitter) {
	  _inherits(ESInfiniteScroll, _Emitter);
	
	  function ESInfiniteScroll(options) {
	    _classCallCheck(this, ESInfiniteScroll);
	
	    var _this = _possibleConstructorReturn(this, (ESInfiniteScroll.__proto__ || Object.getPrototypeOf(ESInfiniteScroll)).call(this));
	
	    _this.options = options;
	
	    _this.initDownInfinite();
	    _this.initUpLoading();
	    return _this;
	  }
	
	  _createClass(ESInfiniteScroll, [{
	    key: 'initUpLoading',
	    value: function initUpLoading() {
	      $('.js-up-more-link').on('click', function (event) {
	        var $target = $(event.currentTarget);
	        $.ajax({
	          method: 'GET',
	          url: $target.data('url'),
	          async: false,
	          success: function success(html) {
	            $(html).find('.infinite-item').prependTo($('.infinite-container'));
	            var $upLink = $(html).find('.js-up-more-link');
	            if ($upLink.length > 0) {
	              $target.data('url', $upLink.data('url'));
	            } else {
	              $target.remove();
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: 'initDownInfinite',
	    value: function initDownInfinite() {
	      var defaultDownOptions = {
	        element: $('.infinite-container')[0]
	      };
	
	      defaultDownOptions = Object.assign(defaultDownOptions, this.options);
	
	      this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
	    }
	  }]);
	
	  return ESInfiniteScroll;
	}(_esEventEmitter2["default"]);
	
	exports["default"] = ESInfiniteScroll;

/***/ }),

/***/ "8f3ec98312b1f1f6bafb":
/***/ (function(module, exports) {

	/*!
	Waypoints - 4.0.1
	Copyright © 2011-2016 Caleb Troughton
	Licensed under the MIT license.
	https://github.com/imakewebthings/waypoints/blob/master/licenses.txt
	*/
	!function(){"use strict";function t(o){if(!o)throw new Error("No options passed to Waypoint constructor");if(!o.element)throw new Error("No element option passed to Waypoint constructor");if(!o.handler)throw new Error("No handler option passed to Waypoint constructor");this.key="waypoint-"+e,this.options=t.Adapter.extend({},t.defaults,o),this.element=this.options.element,this.adapter=new t.Adapter(this.element),this.callback=o.handler,this.axis=this.options.horizontal?"horizontal":"vertical",this.enabled=this.options.enabled,this.triggerPoint=null,this.group=t.Group.findOrCreate({name:this.options.group,axis:this.axis}),this.context=t.Context.findOrCreateByElement(this.options.context),t.offsetAliases[this.options.offset]&&(this.options.offset=t.offsetAliases[this.options.offset]),this.group.add(this),this.context.add(this),i[this.key]=this,e+=1}var e=0,i={};t.prototype.queueTrigger=function(t){this.group.queueTrigger(this,t)},t.prototype.trigger=function(t){this.enabled&&this.callback&&this.callback.apply(this,t)},t.prototype.destroy=function(){this.context.remove(this),this.group.remove(this),delete i[this.key]},t.prototype.disable=function(){return this.enabled=!1,this},t.prototype.enable=function(){return this.context.refresh(),this.enabled=!0,this},t.prototype.next=function(){return this.group.next(this)},t.prototype.previous=function(){return this.group.previous(this)},t.invokeAll=function(t){var e=[];for(var o in i)e.push(i[o]);for(var n=0,r=e.length;r>n;n++)e[n][t]()},t.destroyAll=function(){t.invokeAll("destroy")},t.disableAll=function(){t.invokeAll("disable")},t.enableAll=function(){t.Context.refreshAll();for(var e in i)i[e].enabled=!0;return this},t.refreshAll=function(){t.Context.refreshAll()},t.viewportHeight=function(){return window.innerHeight||document.documentElement.clientHeight},t.viewportWidth=function(){return document.documentElement.clientWidth},t.adapters=[],t.defaults={context:window,continuous:!0,enabled:!0,group:"default",horizontal:!1,offset:0},t.offsetAliases={"bottom-in-view":function(){return this.context.innerHeight()-this.adapter.outerHeight()},"right-in-view":function(){return this.context.innerWidth()-this.adapter.outerWidth()}},window.Waypoint=t}(),function(){"use strict";function t(t){window.setTimeout(t,1e3/60)}function e(t){this.element=t,this.Adapter=n.Adapter,this.adapter=new this.Adapter(t),this.key="waypoint-context-"+i,this.didScroll=!1,this.didResize=!1,this.oldScroll={x:this.adapter.scrollLeft(),y:this.adapter.scrollTop()},this.waypoints={vertical:{},horizontal:{}},t.waypointContextKey=this.key,o[t.waypointContextKey]=this,i+=1,n.windowContext||(n.windowContext=!0,n.windowContext=new e(window)),this.createThrottledScrollHandler(),this.createThrottledResizeHandler()}var i=0,o={},n=window.Waypoint,r=window.onload;e.prototype.add=function(t){var e=t.options.horizontal?"horizontal":"vertical";this.waypoints[e][t.key]=t,this.refresh()},e.prototype.checkEmpty=function(){var t=this.Adapter.isEmptyObject(this.waypoints.horizontal),e=this.Adapter.isEmptyObject(this.waypoints.vertical),i=this.element==this.element.window;t&&e&&!i&&(this.adapter.off(".waypoints"),delete o[this.key])},e.prototype.createThrottledResizeHandler=function(){function t(){e.handleResize(),e.didResize=!1}var e=this;this.adapter.on("resize.waypoints",function(){e.didResize||(e.didResize=!0,n.requestAnimationFrame(t))})},e.prototype.createThrottledScrollHandler=function(){function t(){e.handleScroll(),e.didScroll=!1}var e=this;this.adapter.on("scroll.waypoints",function(){(!e.didScroll||n.isTouch)&&(e.didScroll=!0,n.requestAnimationFrame(t))})},e.prototype.handleResize=function(){n.Context.refreshAll()},e.prototype.handleScroll=function(){var t={},e={horizontal:{newScroll:this.adapter.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.adapter.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};for(var i in e){var o=e[i],n=o.newScroll>o.oldScroll,r=n?o.forward:o.backward;for(var s in this.waypoints[i]){var a=this.waypoints[i][s];if(null!==a.triggerPoint){var l=o.oldScroll<a.triggerPoint,h=o.newScroll>=a.triggerPoint,p=l&&h,u=!l&&!h;(p||u)&&(a.queueTrigger(r),t[a.group.id]=a.group)}}}for(var c in t)t[c].flushTriggers();this.oldScroll={x:e.horizontal.newScroll,y:e.vertical.newScroll}},e.prototype.innerHeight=function(){return this.element==this.element.window?n.viewportHeight():this.adapter.innerHeight()},e.prototype.remove=function(t){delete this.waypoints[t.axis][t.key],this.checkEmpty()},e.prototype.innerWidth=function(){return this.element==this.element.window?n.viewportWidth():this.adapter.innerWidth()},e.prototype.destroy=function(){var t=[];for(var e in this.waypoints)for(var i in this.waypoints[e])t.push(this.waypoints[e][i]);for(var o=0,n=t.length;n>o;o++)t[o].destroy()},e.prototype.refresh=function(){var t,e=this.element==this.element.window,i=e?void 0:this.adapter.offset(),o={};this.handleScroll(),t={horizontal:{contextOffset:e?0:i.left,contextScroll:e?0:this.oldScroll.x,contextDimension:this.innerWidth(),oldScroll:this.oldScroll.x,forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:e?0:i.top,contextScroll:e?0:this.oldScroll.y,contextDimension:this.innerHeight(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};for(var r in t){var s=t[r];for(var a in this.waypoints[r]){var l,h,p,u,c,d=this.waypoints[r][a],f=d.options.offset,w=d.triggerPoint,y=0,g=null==w;d.element!==d.element.window&&(y=d.adapter.offset()[s.offsetProp]),"function"==typeof f?f=f.apply(d):"string"==typeof f&&(f=parseFloat(f),d.options.offset.indexOf("%")>-1&&(f=Math.ceil(s.contextDimension*f/100))),l=s.contextScroll-s.contextOffset,d.triggerPoint=Math.floor(y+l-f),h=w<s.oldScroll,p=d.triggerPoint>=s.oldScroll,u=h&&p,c=!h&&!p,!g&&u?(d.queueTrigger(s.backward),o[d.group.id]=d.group):!g&&c?(d.queueTrigger(s.forward),o[d.group.id]=d.group):g&&s.oldScroll>=d.triggerPoint&&(d.queueTrigger(s.forward),o[d.group.id]=d.group)}}return n.requestAnimationFrame(function(){for(var t in o)o[t].flushTriggers()}),this},e.findOrCreateByElement=function(t){return e.findByElement(t)||new e(t)},e.refreshAll=function(){for(var t in o)o[t].refresh()},e.findByElement=function(t){return o[t.waypointContextKey]},window.onload=function(){r&&r(),e.refreshAll()},n.requestAnimationFrame=function(e){var i=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||t;i.call(window,e)},n.Context=e}(),function(){"use strict";function t(t,e){return t.triggerPoint-e.triggerPoint}function e(t,e){return e.triggerPoint-t.triggerPoint}function i(t){this.name=t.name,this.axis=t.axis,this.id=this.name+"-"+this.axis,this.waypoints=[],this.clearTriggerQueues(),o[this.axis][this.name]=this}var o={vertical:{},horizontal:{}},n=window.Waypoint;i.prototype.add=function(t){this.waypoints.push(t)},i.prototype.clearTriggerQueues=function(){this.triggerQueues={up:[],down:[],left:[],right:[]}},i.prototype.flushTriggers=function(){for(var i in this.triggerQueues){var o=this.triggerQueues[i],n="up"===i||"left"===i;o.sort(n?e:t);for(var r=0,s=o.length;s>r;r+=1){var a=o[r];(a.options.continuous||r===o.length-1)&&a.trigger([i])}}this.clearTriggerQueues()},i.prototype.next=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints),o=i===this.waypoints.length-1;return o?null:this.waypoints[i+1]},i.prototype.previous=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints);return i?this.waypoints[i-1]:null},i.prototype.queueTrigger=function(t,e){this.triggerQueues[e].push(t)},i.prototype.remove=function(t){var e=n.Adapter.inArray(t,this.waypoints);e>-1&&this.waypoints.splice(e,1)},i.prototype.first=function(){return this.waypoints[0]},i.prototype.last=function(){return this.waypoints[this.waypoints.length-1]},i.findOrCreate=function(t){return o[t.axis][t.name]||new i(t)},n.Group=i}(),function(){"use strict";function t(t){this.$element=e(t)}var e=window.jQuery,i=window.Waypoint;e.each(["innerHeight","innerWidth","off","offset","on","outerHeight","outerWidth","scrollLeft","scrollTop"],function(e,i){t.prototype[i]=function(){var t=Array.prototype.slice.call(arguments);return this.$element[i].apply(this.$element,t)}}),e.each(["extend","inArray","isEmptyObject"],function(i,o){t[o]=e[o]}),i.adapters.push({name:"jquery",Adapter:t}),i.Adapter=t}(),function(){"use strict";function t(t){return function(){var i=[],o=arguments[0];return t.isFunction(arguments[0])&&(o=t.extend({},arguments[1]),o.handler=arguments[0]),this.each(function(){var n=t.extend({},o,{element:this});"string"==typeof n.context&&(n.context=t(this).closest(n.context)[0]),i.push(new e(n))}),i}}var e=window.Waypoint;window.jQuery&&(window.jQuery.fn.waypoint=t(window.jQuery)),window.Zepto&&(window.Zepto.fn.waypoint=t(window.Zepto))}();

/***/ }),

/***/ "c5e642028fa5ee5a3554":
/***/ (function(module, exports) {

	/*!
	Waypoints Infinite Scroll Shortcut - 4.0.1
	Copyright © 2011-2016 Caleb Troughton
	Licensed under the MIT license.
	https://github.com/imakewebthings/waypoints/blob/master/licenses.txt
	*/
	!function(){"use strict";function t(n){this.options=i.extend({},t.defaults,n),this.container=this.options.element,"auto"!==this.options.container&&(this.container=this.options.container),this.$container=i(this.container),this.$more=i(this.options.more),this.$more.length&&(this.setupHandler(),this.waypoint=new o(this.options))}var i=window.jQuery,o=window.Waypoint;t.prototype.setupHandler=function(){this.options.handler=i.proxy(function(){this.options.onBeforePageLoad(),this.destroy(),this.$container.addClass(this.options.loadingClass),i.get(i(this.options.more).attr("href"),i.proxy(function(t){var n=i(i.parseHTML(t)),e=n.find(this.options.more),s=n.find(this.options.items);s.length||(s=n.filter(this.options.items)),this.$container.append(s),this.$container.removeClass(this.options.loadingClass),e.length||(e=n.filter(this.options.more)),e.length?(this.$more.replaceWith(e),this.$more=e,this.waypoint=new o(this.options)):this.$more.remove(),this.options.onAfterPageLoad(s)},this))},this)},t.prototype.destroy=function(){this.waypoint&&this.waypoint.destroy()},t.defaults={container:"auto",items:".infinite-item",more:".infinite-more-link",offset:"bottom-in-view",loadingClass:"infinite-loading",onBeforePageLoad:i.noop,onAfterPageLoad:i.noop},o.Infinite=t}();

/***/ })

});
//# sourceMappingURL=index.js.map