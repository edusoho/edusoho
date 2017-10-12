webpackJsonp(["app/js/activity/live/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _live = __webpack_require__("c6797855be84e924b7d5");
	
	var _live2 = _interopRequireDefault(_live);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	window.liveShow = new _live2["default"]();

/***/ }),

/***/ "c6797855be84e924b7d5":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	    value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _activityEmitter = __webpack_require__("da32dea28c2b82c7aab1");
	
	var _activityEmitter2 = _interopRequireDefault(_activityEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var LiveShow = function () {
	    function LiveShow() {
	        _classCallCheck(this, LiveShow);
	
	        this.interval = 1;
	        this.emitter = new _activityEmitter2["default"]();
	        this.startEvent();
	        this.finishByReplay();
	        this.countdownEvent();
	    }
	
	    _createClass(LiveShow, [{
	        key: 'finishByReplay',
	        value: function finishByReplay() {
	            var self = this;
	            $('.js-replay').on('click', function () {
	                var triggerUrl = $(this).data('finish');
	                $.post(triggerUrl, function (res) {
	                    self.emitter.emit('finish');
	                });
	            });
	        }
	    }, {
	        key: 'startEvent',
	        value: function startEvent() {
	            var self = this;
	            $(".js-start-live").on("click", function () {
	                if (!self.started) {
	                    this.started = true;
	                    self.emitter.emit('start', {}).then(function () {
	                        console.log('live.start');
	                    })["catch"](function (error) {
	                        console.error(error);
	                    });
	                }
	            });
	        }
	    }, {
	        key: 'countdownEvent',
	        value: function countdownEvent() {
	            var _this = this;
	
	            var $countdown = $('#countdown');
	            if ($countdown.length == 0) return;
	
	            this.timeRemain = $countdown.data('timeRemain');
	            this._countdown($countdown, this.interval);
	
	            this.iId = setInterval(function () {
	                _this._countdown($countdown, _this.interval);
	            }, this.interval * 1000);
	        }
	    }, {
	        key: '_countdown',
	        value: function _countdown($countdown, interval) {
	            var timeRemain = this.timeRemain;
	            var days = Math.floor(timeRemain / (60 * 60 * 24));
	            var modulo = timeRemain % (60 * 60 * 24);
	            var hours = Math.floor(modulo / (60 * 60));
	            modulo = modulo % (60 * 60);
	            var minutes = Math.floor(modulo / 60);
	            var seconds = modulo % 60;
	            var context = '';
	            context += days ? days + '天' : '';
	            context += hours ? hours + '时' : '';
	            context += minutes ? minutes + '分' : '';
	            context += seconds ? seconds + '秒' : '';
	            this.timeRemain = timeRemain - interval;
	            $countdown.text(context);
	            if (this.timeRemain <= 0) {
	                $countdown.text('直播已经开始');
	                window.clearInterval(this.iId);
	            }
	        }
	    }]);
	
	    return LiveShow;
	}();
	
	exports["default"] = LiveShow;

/***/ })

});
//# sourceMappingURL=index.js.map