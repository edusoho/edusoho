webpackJsonp(["app/js/player/index"],{

/***/ "b30415350b581ef5a73d":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	/**
	 * Created by Simon on 2016/11/18.
	 */
	import 'store';
	
	var DurationStorage = function () {
	  function DurationStorage() {
	    _classCallCheck(this, DurationStorage);
	  }
	
	  _createClass(DurationStorage, null, [{
	    key: "set",
	    value: function set(userId, fileId, duration) {
	      var durations = store.get("durations", {});
	      if (!durations || !(durations instanceof Array)) {
	        durations = new Array();
	      }
	
	      var value = userId + "-" + fileId + ":" + duration;
	      if (durations.length > 0 && durations.slice(durations.length - 1, durations.length)[0].indexOf(userId + "-" + fileId) > -1) {
	        durations.splice(durations.length - 1, durations.length);
	      }
	      if (durations.length >= 20) {
	        durations.shift();
	      }
	      durations.push(value);
	      store.set("durations", durations);
	    }
	  }, {
	    key: "get",
	    value: function get(userId, fileId) {
	      var durationTmpArray = store.get("durations", {});
	      if (durationTmpArray) {
	        for (var i = 0; i < durationTmpArray.length; i++) {
	          var index = durationTmpArray[i].indexOf(userId + "-" + fileId);
	          if (index > -1) {
	            var key = durationTmpArray[i];
	            return parseFloat(key.split(":")[1]) - 5;
	          }
	        }
	      }
	      return 0;
	    }
	  }, {
	    key: "del",
	    value: function del(userId, fileId) {
	      var key = userId + "-" + fileId;
	      var durationTmpArray = store.get("durations");
	      for (var i = 0; i < durationTmpArray.length; i++) {
	        var index = durationTmpArray[i].indexOf(userId + "-" + fileId);
	        if (index > -1) {
	          durationTmpArray.splice(i, 1);
	        }
	      }
	      store.set("durations", durationTmpArray);
	    }
	  }]);
	
	  return DurationStorage;
	}();
	
	export default DurationStorage;

/***/ }),

/***/ "06597b47670159844043":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	import Messenger from 'es-messenger';
	import Emitter from "component-emitter";
	
	var EsMessenger = function (_Emitter) {
	  _inherits(EsMessenger, _Emitter);
	
	  function EsMessenger(options) {
	    _classCallCheck(this, EsMessenger);
	
	    var _this = _possibleConstructorReturn(this, (EsMessenger.__proto__ || Object.getPrototypeOf(EsMessenger)).call(this));
	
	    _this.name = options.name;
	    _this.project = options.project;
	    _this.children = options.children;
	    _this.type = options.type; //enum: parent,child
	    _this.setup();
	    return _this;
	  }
	
	  _createClass(EsMessenger, [{
	    key: 'setup',
	    value: function setup() {
	      var _this2 = this;
	
	      var messenger = new Messenger(this.name, this.project);
	      if (this.type == 'child') {
	        //同时广播同域和者跨域
	        messenger.addTarget(window.parent, 'parent');
	        messenger.addTarget(window.self, 'partner');
	      } else if (this.type == 'parent') {
	        messenger.addTarget(window.self, 'child');
	        var children = this.children;
	        for (var i = children.length - 1; i >= 0; i--) {
	          messenger.addTarget(children[i].contentWindow, children[i].id);
	        }
	      }
	
	      messenger.listen(function (msg) {
	        msg = JSON.parse(msg);
	        _this2.emit(msg.eventName, msg.args);
	      });
	      this.messenger = messenger;
	    }
	  }, {
	    key: 'sendToParent',
	    value: function sendToParent(eventName, args) {
	      for (var target in this.messenger.targets) {
	        this.messenger.targets[target].send(this.convertToString(eventName, args));
	      }
	    }
	  }, {
	    key: 'sendToChild',
	    value: function sendToChild(child, eventName, args) {
	      this.messenger.targets[child.id].send(this.convertToString(eventName, args));
	    }
	  }, {
	    key: 'convertToString',
	    value: function convertToString(eventName, args) {
	      var msg = { "eventName": eventName, "args": args };
	      msg = JSON.stringify(msg);
	      return msg;
	    }
	  }]);
	
	  return EsMessenger;
	}(Emitter);
	
	export default EsMessenger;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _playerFactory = __webpack_require__("cb3619dd4026be2f4f29");
	
	var _playerFactory2 = _interopRequireDefault(_playerFactory);
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _durationStorage = __webpack_require__("b30415350b581ef5a73d");
	
	var _durationStorage2 = _interopRequireDefault(_durationStorage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Show = function () {
	  function Show(element) {
	    _classCallCheck(this, Show);
	
	    var container = $(element);
	    this.htmlDom = $(element);
	    this.userId = container.data("userId");
	    this.userName = container.data("userName");
	    this.fileId = container.data("fileId");
	    this.fileGlobalId = container.data("fileGlobalId");
	
	    this.courseId = container.data("courseId");
	    this.lessonId = container.data("lessonId");
	    this.timelimit = container.data('timelimit');
	
	    this.playerType = container.data('player');
	    this.fileType = container.data('fileType');
	    this.url = container.data('url');
	    this.videoHeaderLength = container.data('videoHeaderLength');
	    this.enablePlaybackRates = container.data('enablePlaybackRates');
	    this.watermark = container.data('watermark');
	    this.accesskey = container.data('accessKey');
	    this.fingerprint = container.data('fingerprint');
	    this.fingerprintSrc = container.data('fingerprintSrc');
	    this.fingerprintTime = container.data('fingerprintTime');
	    this.balloonVideoPlayer = container.data('balloonVideoPlayer');
	    this.markerUrl = container.data('markerurl');
	    this.finishQuestionMarkerUrl = container.data('finishQuestionMarkerUrl');
	    this.starttime = container.data('starttime');
	    this.agentInWhiteList = container.data('agentInWhiteList');
	    this.disableVolumeButton = container.data('disableVolumeButton');
	    this.disablePlaybackButton = container.data('disablePlaybackButton');
	    this.disableResolutionSwitcher = container.data('disableResolutionSwitcher');
	    this.subtitles = container.data('subtitles');
	
	    this.initView();
	    this.initEvent();
	  }
	
	  _createClass(Show, [{
	    key: 'initView',
	    value: function initView() {
	      var html = "";
	      if (this.fileType == 'video') {
	        if (this.playerType == 'local-video-player') {
	          html += '<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>';
	        } else {
	          html += '<div id="lesson-player" style="width: 100%;height: 100%;"></div>';
	        }
	      } else if (this.fileType == 'audio') {
	        html += '<audio id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto" poster="http://s.cn.bing.net/az/hprichbg/rb/MountScott_ZH-CN8412403132_1920x1080.jpg"></audio>';
	      }
	      this.htmlDom.html(html);
	      this.htmlDom.show();
	    }
	  }, {
	    key: 'initPlayer',
	    value: function initPlayer() {
	      return _playerFactory2.default.create(this.playerType, {
	        element: '#lesson-player',
	        url: this.url,
	        mediaType: this.fileType,
	        fingerprint: this.fingerprint,
	        fingerprintSrc: this.fingerprintSrc,
	        fingerprintTime: this.fingerprintTime,
	        watermark: this.watermark,
	        starttime: this.starttime,
	        agentInWhiteList: this.agentInWhiteList,
	        timelimit: this.timelimit,
	        enablePlaybackRates: this.enablePlaybackRates,
	        controlBar: {
	          disableVolumeButton: this.disableVolumeButton,
	          disablePlaybackButton: this.disablePlaybackButton,
	          disableResolutionSwitcher: this.disableResolutionSwitcher
	        },
	        statsInfo: {
	          accesskey: this.accesskey,
	          globalId: this.fileGlobalId,
	          userId: this.userId,
	          userName: this.userName
	        },
	        videoHeaderLength: this.videoHeaderLength,
	        textTrack: this.transToTextrack(this.subtitles)
	      });
	    }
	  }, {
	    key: 'transToTextrack',
	    value: function transToTextrack(subtitles) {
	      var textTracks = [];
	      if (subtitles) {
	        for (var i in subtitles) {
	          var item = {
	            label: subtitles[i].name,
	            src: subtitles[i].url,
	            'default': "default" in subtitles[i] ? subtitles[i]['default'] : false
	          };
	          textTracks.push(item);
	        }
	      }
	
	      // set first item to default if no default
	      for (var _i in textTracks) {
	        if (textTracks[_i]['default']) {
	          return;
	        }
	        textTracks[0]['default'] = true;
	      }
	      return textTracks;
	    }
	  }, {
	    key: 'initMesseger',
	    value: function initMesseger() {
	      return new _messenger2.default({
	        name: 'parent',
	        project: 'PlayerProject',
	        type: 'child'
	      });
	    }
	  }, {
	    key: 'isCloudPalyer',
	    value: function isCloudPalyer() {
	      return 'balloon-cloud-video-player' == this.playerType;
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      var player = this.initPlayer();
	      var messenger = this.initMesseger();
	      player.on("ready", function () {
	        messenger.sendToParent("ready", { pause: true, currentTime: player.getCurrentTime() });
	        if (!_this.isCloudPalyer()) {
	          var time = _durationStorage2.default.get(_this.userId, _this.fileId);
	          if (time > 0) {
	            player.setCurrentTime(time);
	          }
	          player.play();
	        } else if (_this.isCloudPalyer()) {
	          if (_this.markerUrl) {
	            $.getJSON(_this.markerUrl, function (questions) {
	              player.setQuestions(questions);
	            });
	          }
	        }
	      });
	
	      player.on('answered', function (data) {
	        var regExp = /course\/(\d+)\/task\/(\d+)\/show/;
	        var matches = regExp.exec(window.location.href);
	
	        if (matches) {
	          $.post(_this.finishQuestionMarkerUrl, {
	            'questionMarkerId': data.id,
	            'answer': data.answer,
	            'type': data.type,
	            'courseId': matches[1],
	            'taskId': matches[2]
	          }, function (result) {});
	        }
	      });
	
	      player.on("timechange", function (data) {
	        messenger.sendToParent("timechange", { pause: true, currentTime: player.getCurrentTime() });
	        if (!_this.isCloudPalyer()) {
	          if (parseInt(player.getCurrentTime()) != parseInt(player.getDuration())) {
	            _durationStorage2.default.set(_this.userId, _this.fileId, player.getCurrentTime());
	          }
	        }
	      });
	
	      player.on("paused", function () {
	        messenger.sendToParent("paused", { pause: true, currentTime: player.getCurrentTime() });
	      });
	
	      player.on("playing", function () {
	        messenger.sendToParent("playing", { pause: false, currentTime: player.getCurrentTime() });
	      });
	
	      player.on("ended", function () {
	        messenger.sendToParent("ended", { stop: true });
	        if (!_this.isCloudPalyer()) {
	          _durationStorage2.default.del(_this.userId, _this.fileId);
	        }
	      });
	    }
	  }]);
	
	  return Show;
	}();
	
	new Show('#lesson-video-content');

/***/ }),

/***/ "cb3619dd4026be2f4f29":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import LocalVideoPlayer from './local-video-player';
	import BalloonVideoPlayer from './balloon-cloud-video-player';
	
	var PlayerFactory = function () {
	  function PlayerFactory() {
	    _classCallCheck(this, PlayerFactory);
	  }
	
	  _createClass(PlayerFactory, null, [{
	    key: 'create',
	    value: function create(type, options) {
	      switch (type) {
	        case "local-video-player":
	        case "audio-player":
	          return new LocalVideoPlayer(options);
	          break;
	        case "balloon-cloud-video-player":
	          return new BalloonVideoPlayer(options);
	          break;
	      }
	    }
	  }]);
	
	  return PlayerFactory;
	}();
	
	export default PlayerFactory;

/***/ })

});