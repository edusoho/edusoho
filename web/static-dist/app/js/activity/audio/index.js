webpackJsonp(["app/js/activity/audio/index"],{

/***/ "e4d85ee087144e008b7d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("d5e8fa5f17ac5fe79c78");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var AudioRecorder = function () {
	  function AudioRecorder(container) {
	    _classCallCheck(this, AudioRecorder);
	
	    this.container = container;
	    this.interval = 120;
	  }
	
	  _createClass(AudioRecorder, [{
	    key: 'addAudioPlayerCounter',
	    value: function addAudioPlayerCounter(emitter, player) {
	      var $container = $(this.container);
	      var activityId = $container.data('id');
	      var playerCounter = store.get("activity_id_" + activityId + "_playing_counter");
	      if (!playerCounter) {
	        playerCounter = 0;
	      }
	      if (!(player && player.playing)) {
	        return false;
	      }
	      if (playerCounter >= this.interval) {
	        emitter.emit('watching', { watchTime: this.interval }).then(function () {})["catch"](function (error) {
	          console.error(error);
	        });
	        playerCounter = 0;
	      } else if (player.playing) {
	        playerCounter++;
	      }
	      store.set("activity_id_" + activityId + "_playing_counter", playerCounter);
	    }
	  }]);
	
	  return AudioRecorder;
	}();
	
	exports["default"] = AudioRecorder;

/***/ }),

/***/ "cc7472fb8f4ce5a74874":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _activityEmitter = __webpack_require__("da32dea28c2b82c7aab1");
	
	var _activityEmitter2 = _interopRequireDefault(_activityEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var AudioPlay = function () {
	  function AudioPlay(elment, recorder) {
	    _classCallCheck(this, AudioPlay);
	
	    this.dom = $(elment);
	    this.data = this.dom.data();
	    this.recorder = recorder;
	    this.player = {};
	    this.emitter = new _activityEmitter2["default"]();
	  }
	
	  _createClass(AudioPlay, [{
	    key: 'record',
	    value: function record() {
	      var _this = this;
	
	      this.intervalId = setInterval(function () {
	        _this.recorder.addAudioPlayerCounter(_this.emitter, _this.player);
	      }, 1000);
	    }
	  }, {
	    key: 'play',
	    value: function play() {
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
	      });
	
	      messenger.on("timechange", function (msg) {});
	
	      this.record();
	    }
	  }, {
	    key: '_onFinishLearnTask',
	    value: function _onFinishLearnTask(msg) {
	      this.emitter.emit('finish', { data: msg }).then(function () {
	        console.log('audio.finish');
	      })["catch"](function (error) {
	        console.error(error);
	      });
	    }
	  }]);
	
	  return AudioPlay;
	}();
	
	exports["default"] = AudioPlay;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _audio = __webpack_require__("cc7472fb8f4ce5a74874");
	
	var _audio2 = _interopRequireDefault(_audio);
	
	var _audioRecorder = __webpack_require__("e4d85ee087144e008b7d");
	
	var _audioRecorder2 = _interopRequireDefault(_audioRecorder);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var recorder = new _audioRecorder2["default"]('#audio-content');
	var audioPlay = new _audio2["default"]('#audio-content', recorder);
	audioPlay.play();

/***/ })

});
//# sourceMappingURL=index.js.map