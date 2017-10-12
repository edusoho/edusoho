webpackJsonp(["app/js/activity/video/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _videoRecorder = __webpack_require__("9a49bb678fea4ca10409");
	
	var _videoRecorder2 = _interopRequireDefault(_videoRecorder);
	
	var _videoPlay = __webpack_require__("3152735b7d14aa57d929");
	
	var _videoPlay2 = _interopRequireDefault(_videoPlay);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var recorder = new _videoRecorder2["default"]('#video-content');
	var videoplay = new _videoPlay2["default"](recorder);
	videoplay.play();

/***/ }),

/***/ "3152735b7d14aa57d929":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esSwfobject = __webpack_require__("c04c1b91e3806f24595a");
	
	var _esSwfobject2 = _interopRequireDefault(_esSwfobject);
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _activityEmitter = __webpack_require__("da32dea28c2b82c7aab1");
	
	var _activityEmitter2 = _interopRequireDefault(_activityEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var VideoPlay = function () {
	  function VideoPlay(recorder) {
	    _classCallCheck(this, VideoPlay);
	
	    this.player = {};
	    this.intervalId = null;
	    this.recorder = recorder;
	    this.emitter = new _activityEmitter2["default"]();
	  }
	
	  _createClass(VideoPlay, [{
	    key: 'play',
	    value: function play() {
	      if ($('#swf-player').length) {
	        this._playerSwf();
	      } else {
	        this._playVideo();
	      }
	      this.record();
	    }
	  }, {
	    key: 'record',
	    value: function record() {
	      var _this = this;
	
	      this.intervalId = setInterval(function () {
	        _this.recorder.addVideoPlayerCounter(_this.emitter, _this.player);
	      }, 1000);
	    }
	  }, {
	    key: 'getPlay',
	    value: function getPlay() {
	      return this.player;
	    }
	  }, {
	    key: '_playerSwf',
	    value: function _playerSwf() {
	      var swf_dom = 'swf-player';
	      _esSwfobject2["default"].embedSWF($('#' + swf_dom).data('url'), swf_dom, '100%', '100%', "9.0.0", null, null, {
	        wmode: 'opaque',
	        allowFullScreen: 'true'
	      });
	    }
	  }, {
	    key: '_playVideo',
	    value: function _playVideo() {
	      var _this2 = this;
	
	      var messenger = new _messenger2["default"]({
	        name: 'partner',
	        project: 'PlayerProject',
	        children: [],
	        type: 'parent'
	      });
	
	      messenger.on("ended", function (msg) {
	        _this2.player.playing = false;
	        _this2._onFinishLearnTask(msg);
	      });
	
	      messenger.on("playing", function (msg) {
	        _this2.player.playing = true;
	      });
	
	      messenger.on("paused", function (msg) {
	        _this2.player.playing = false;
	        _this2.recorder.watching(_this2.emitter);
	      });
	
	      messenger.on("timechange", function (msg) {
	        _this2.player.currentTime = msg.currentTime;
	      });
	    }
	  }, {
	    key: '_onFinishLearnTask',
	    value: function _onFinishLearnTask(msg) {
	      var _this3 = this;
	
	      this.emitter.emit('finish', { data: msg }).then(function () {
	        clearInterval(_this3.intervalId);
	      })["catch"](function (error) {
	        console.error(error);
	      });
	    }
	  }]);
	
	  return VideoPlay;
	}();
	
	exports["default"] = VideoPlay;

/***/ }),

/***/ "9a49bb678fea4ca10409":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("d5e8fa5f17ac5fe79c78");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var VideoRecorder = function () {
		function VideoRecorder(container) {
			_classCallCheck(this, VideoRecorder);
	
			this.container = container;
			this.interval = 120;
			this.playerCounter = 0;
			this.activityId = $(this.container).data('id');
		}
	
		_createClass(VideoRecorder, [{
			key: 'addVideoPlayerCounter',
			value: function addVideoPlayerCounter(emitter, player) {
				var playerCounter = store.get("activity_id_" + this.activityId + "_playing_counter");
				if (!playerCounter) {
					this.playerCounter = 0;
				}
				if (!(player && player.playing)) {
					return false;
				}
				if (playerCounter >= this.interval) {
					this.watching(emitter);
				} else if (player.playing) {
					this.playerCounter++;
				}
				store.set("activity_id_" + this.activityId + "_playing_counter", this.playerCounter);
			}
		}, {
			key: 'watching',
			value: function watching(emitter) {
				var watchTime = store.get("activity_id_" + this.activityId + "_playing_counter");
				console.log(watchTime);
				emitter.emit('watching', { watchTime: watchTime }).then(function () {
					var url = $("#video-content").data('watchUrl');
					$.post(url, function (response) {
						if (response && response.status == 'error') {
							window.location.reload();
						}
					});
				})["catch"](function (error) {
					console.error(error);
				});
				this.playerCounter = 0;
			}
		}]);
	
		return VideoRecorder;
	}();
	
	exports["default"] = VideoRecorder;

/***/ })

});
//# sourceMappingURL=index.js.map