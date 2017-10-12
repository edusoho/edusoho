webpackJsonp(["app/js/material-lib/ppt-player/index"],{

/***/ "f3c7e4fbf91afda92bf3":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esEventEmitter = __webpack_require__("63fff8fb24f3bd1f61cd");
	
	var _esEventEmitter2 = _interopRequireDefault(_esEventEmitter);
	
	var _screenfull = __webpack_require__("56b32877cbcf8d29840e");
	
	var _screenfull2 = _interopRequireDefault(_screenfull);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var PPT = function (_Emitter) {
	  _inherits(PPT, _Emitter);
	
	  function PPT(_ref) {
	    var element = _ref.element,
	        slides = _ref.slides,
	        watermark = _ref.watermark;
	
	    _classCallCheck(this, PPT);
	
	    var _this = _possibleConstructorReturn(this, (PPT.__proto__ || Object.getPrototypeOf(PPT)).call(this));
	
	    _this.element = $(element);
	    _this.slides = slides || [];
	    _this.watermark = watermark || '';
	    _this._KEY_ACTION_MAP = {
	      37: _this._onPrev, // ←
	      39: _this._onNext, // →
	      38: _this._onLast, // ↑
	      40: _this._onFirst // ↓
	    };
	    _this.total = _this.slides.length;
	    _this._page = 0;
	    _this.placeholder = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC";
	    _this._init();
	    return _this;
	  }
	
	  _createClass(PPT, [{
	    key: "_render",
	    value: function _render() {
	      var html = "\n      <div class=\"slide-player\">\n        <div class=\"slide-player-body loading-background\"></div>\n        <div class=\"slide-notice\">\n          <div class=\"header\">{{ 'site.data_last_picture'|trans }}\n            <button type=\"button\" class=\"close\">\xD7</button>\n          </div>\n        </div>\n      \n        <div class=\"slide-player-control clearfix\">\n          <a href=\"javascript:\" class=\"goto-first\">\n            <span class=\"glyphicon glyphicon-step-backward\"></span>\n          </a>\n          <a href=\"javascript:\" class=\"goto-prev\">\n            <span class=\"glyphicon glyphicon-chevron-left\"></span>\n          </a>\n          <a href=\"javascript:\" class=\"goto-next\">\n            <span class=\"glyphicon glyphicon-chevron-right\"></span>\n          </a>\n          <a href=\"javascript:\" class=\"goto-last\">\n            <span class=\"glyphicon glyphicon-step-forward\"></span>\n          </a>\n          <a href=\"javascript:\" class=\"fullscreen\">\n            <span class=\"glyphicon glyphicon-fullscreen\"></span>\n          </a>\n          <div class=\"goto-page-input\">\n            <input type=\"text\" class=\"goto-page form-control input-sm\" value=\"1\">&nbsp;/&nbsp;\n              <span class=\"total\"></span>\n          </div>\n        </div>\n      </div>";
	
	      this.element.html(html);
	
	      this.element.find('.total').text(this.total);
	
	      var slidesHTML = this.slides.reduce(function (html, src, index) {
	        html += "<img data-src=\"" + src + "\" class=\"slide\" data-page=\"" + (index + 1) + "\">";
	        return html;
	      }, '');
	
	      this.element.find('.slide-player-body').html(slidesHTML);
	      this.watermark && this.element.append("<div class=\"slide-player-watermark\">" + this.watermark + "</div>");
	    }
	  }, {
	    key: "_init",
	    value: function _init() {
	      this._render();
	      this._bindEvents();
	      this._onFirst();
	    }
	  }, {
	    key: "_lazyLoad",
	    value: function _lazyLoad(page) {
	      for (var currentPage = page; currentPage < page + 4; currentPage++) {
	        if (currentPage > this.total) {
	          break;
	        }
	
	        var $slide = this._getSlide(currentPage);
	        $slide.attr('src') || $slide.attr('src', $slide.data('src'));
	      }
	    }
	  }, {
	    key: "_getSlide",
	    value: function _getSlide(page) {
	      return this.element.find('.slide-player-body .slide').eq(page - 1);
	    }
	  }, {
	    key: "_bindEvents",
	    value: function _bindEvents() {
	      var _this2 = this;
	
	      $(document).on('keydown', function (event) {
	        _this2._KEY_ACTION_MAP[event.keyCode] && _this2._KEY_ACTION_MAP[event.keyCode].call(_this2);
	      });
	
	      this.element.on('click', '.goto-next', function (event) {
	        return _this2._onNext(event);
	      });
	      this.element.on('click', '.goto-prev', function (event) {
	        return _this2._onPrev(event);
	      });
	      this.element.on('click', '.goto-first', function (event) {
	        return _this2._onFirst(event);
	      });
	      this.element.on('click', '.goto-last', function (event) {
	        return _this2._onLast(event);
	      });
	      this.element.on('click', '.fullscreen', function (event) {
	        return _this2._onFullScreen(event);
	      });
	      this.element.on('change', '.goto-page', function (event) {
	        return _this2._onChangePage(event);
	      });
	      var self = this;
	      this.on('change', function (_ref2) {
	        var current = _ref2.current,
	            before = _ref2.before;
	
	        if (current == self.total) {
	          self.emit('end', { page: _this2.total });
	        }
	      });
	    }
	  }, {
	    key: "_onNext",
	    value: function _onNext() {
	      if (this.page === this.total) {
	        this.emit('end', { page: this.total });
	        return;
	      }
	
	      this.page++;
	    }
	  }, {
	    key: "_onPrev",
	    value: function _onPrev() {
	      if (this.page == 1) {
	        return;
	      }
	
	      this.page--;
	    }
	  }, {
	    key: "_onFirst",
	    value: function _onFirst() {
	      this.page = 1;
	    }
	  }, {
	    key: "_onLast",
	    value: function _onLast() {
	      this.page = this.total;
	    }
	  }, {
	    key: "_onFullScreen",
	    value: function _onFullScreen() {
	      if (!_screenfull2["default"].enabled) {
	        return;
	      }
	      if (_screenfull2["default"].isFullscreen) {
	        _screenfull2["default"].toggle();
	      } else {
	        _screenfull2["default"].request();
	      }
	    }
	  }, {
	    key: "_onChangePage",
	    value: function _onChangePage(e) {
	      this.page = $(e.target).val();
	    }
	  }, {
	    key: "page",
	    get: function get() {
	      return this._page;
	    },
	    set: function set(newPage) {
	      var _this3 = this;
	
	      var beforePage = this.page;
	      var currentPage = newPage;
	
	      if (currentPage > this.total) {
	        this.element.find('.goto-page').val(currentPage);
	        this._page = currentPage;
	      }
	
	      if (currentPage < 1) {
	        this.element.find('.goto-page').val(beforePage);
	        this._page = beforePage;
	      }
	
	      if (beforePage) {
	        this.element.find('.slide-player-body .slide').eq(beforePage - 1).removeClass('active');
	      }
	
	      var $currentSlide = this._getSlide(currentPage);
	
	      if ($currentSlide.attr('src')) {
	        $currentSlide.addClass('active');
	      } else {
	        $currentSlide.load(function () {
	          if (_this3._page != $currentSlide.data('page')) {
	            return;
	          }
	          $currentSlide.addClass('active');
	        });
	        $currentSlide.attr('src', $currentSlide.data('src'));
	      }
	
	      this._lazyLoad(currentPage);
	
	      this.element.find('.goto-page').val(currentPage);
	
	      this._page = currentPage;
	
	      this.emit('change', {
	        current: currentPage,
	        before: beforePage
	      });
	    }
	  }]);
	
	  return PPT;
	}(_esEventEmitter2["default"]);
	
	exports["default"] = PPT;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _pptPlayer = __webpack_require__("f3c7e4fbf91afda92bf3");
	
	var _pptPlayer2 = _interopRequireDefault(_pptPlayer);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $player = $("#ppt-player");
	var params = $player.data('params');
	new _pptPlayer2["default"]({
	  element: '#ppt-player',
	  slides: params.images
	});

/***/ }),

/***/ "56b32877cbcf8d29840e":
/***/ (function(module, exports) {

	/*!
	* screenfull
	* v3.0.0 - 2015-11-24
	* (c) Sindre Sorhus; MIT License
	*/
	(function () {
		'use strict';
	
		var isCommonjs = typeof module !== 'undefined' && module.exports;
		var keyboardAllowed = typeof Element !== 'undefined' && 'ALLOW_KEYBOARD_INPUT' in Element;
	
		var fn = (function () {
			var val;
			var valLength;
	
			var fnMap = [
				[
					'requestFullscreen',
					'exitFullscreen',
					'fullscreenElement',
					'fullscreenEnabled',
					'fullscreenchange',
					'fullscreenerror'
				],
				// new WebKit
				[
					'webkitRequestFullscreen',
					'webkitExitFullscreen',
					'webkitFullscreenElement',
					'webkitFullscreenEnabled',
					'webkitfullscreenchange',
					'webkitfullscreenerror'
	
				],
				// old WebKit (Safari 5.1)
				[
					'webkitRequestFullScreen',
					'webkitCancelFullScreen',
					'webkitCurrentFullScreenElement',
					'webkitCancelFullScreen',
					'webkitfullscreenchange',
					'webkitfullscreenerror'
	
				],
				[
					'mozRequestFullScreen',
					'mozCancelFullScreen',
					'mozFullScreenElement',
					'mozFullScreenEnabled',
					'mozfullscreenchange',
					'mozfullscreenerror'
				],
				[
					'msRequestFullscreen',
					'msExitFullscreen',
					'msFullscreenElement',
					'msFullscreenEnabled',
					'MSFullscreenChange',
					'MSFullscreenError'
				]
			];
	
			var i = 0;
			var l = fnMap.length;
			var ret = {};
	
			for (; i < l; i++) {
				val = fnMap[i];
				if (val && val[1] in document) {
					for (i = 0, valLength = val.length; i < valLength; i++) {
						ret[fnMap[0][i]] = val[i];
					}
					return ret;
				}
			}
	
			return false;
		})();
	
		var screenfull = {
			request: function (elem) {
				var request = fn.requestFullscreen;
	
				elem = elem || document.documentElement;
	
				// Work around Safari 5.1 bug: reports support for
				// keyboard in fullscreen even though it doesn't.
				// Browser sniffing, since the alternative with
				// setTimeout is even worse.
				if (/5\.1[\.\d]* Safari/.test(navigator.userAgent)) {
					elem[request]();
				} else {
					elem[request](keyboardAllowed && Element.ALLOW_KEYBOARD_INPUT);
				}
			},
			exit: function () {
				document[fn.exitFullscreen]();
			},
			toggle: function (elem) {
				if (this.isFullscreen) {
					this.exit();
				} else {
					this.request(elem);
				}
			},
			raw: fn
		};
	
		if (!fn) {
			if (isCommonjs) {
				module.exports = false;
			} else {
				window.screenfull = false;
			}
	
			return;
		}
	
		Object.defineProperties(screenfull, {
			isFullscreen: {
				get: function () {
					return Boolean(document[fn.fullscreenElement]);
				}
			},
			element: {
				enumerable: true,
				get: function () {
					return document[fn.fullscreenElement];
				}
			},
			enabled: {
				enumerable: true,
				get: function () {
					// Coerce to boolean in case of old WebKit
					return Boolean(document[fn.fullscreenEnabled]);
				}
			}
		});
	
		if (isCommonjs) {
			module.exports = screenfull;
		} else {
			window.screenfull = screenfull;
		}
	})();


/***/ })

});
//# sourceMappingURL=index.js.map