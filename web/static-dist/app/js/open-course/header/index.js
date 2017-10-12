webpackJsonp(["app/js/open-course/header/index"],{

/***/ "87fce900c1efe27f8ef1":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CourseAd = function () {
	  function CourseAd(_ref) {
	    var element = _ref.element,
	        courseUrl = _ref.courseUrl;
	
	    _classCallCheck(this, CourseAd);
	
	    this.$element = $(element);
	    this.courseUrl = courseUrl;
	    this.init();
	  }
	
	  _createClass(CourseAd, [{
	    key: 'init',
	    value: function init() {
	      var _this = this;
	
	      var html = '';
	
	      $.get(this.courseUrl).then(function (data) {
	        console.log(data);
	        data.map(function (item) {
	          html = html + _this.template(item.id, item.cover, item.title);
	        });
	
	        _this.$element.find('.modal-body').html(html);
	      });
	    }
	  }, {
	    key: 'isWxAndroidBrowser',
	    value: function isWxAndroidBrowser() {
	      var ua = navigator.userAgent.toLowerCase();
	      return (/android/.test(ua) && /micromessenger/i.test(ua)
	      );
	    }
	  }, {
	    key: 'isWxPreviewType',
	    value: function isWxPreviewType() {
	      return this.$element.parent('.js-open-course-wechat-preview').length > 0;
	    }
	  }, {
	    key: 'template',
	    value: function template(id, cover, title) {
	      return '<div class="modal-img">\n        <a href="/course_set/' + id + '">\n          <img class="img-responsive" src="' + cover.middle + '" alt="">\n        </a>\n        <div class="title"><a class="link-dark" href="/course_set/' + id + '">' + title + '</a></div>\n      </div>';
	    }
	  }, {
	    key: 'show',
	    value: function show() {
	      if (this.isWxPreviewType()) {
	        return;
	      }
	
	      if (this.isWxAndroidBrowser()) {
	        document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = "none";
	
	        this.$element.on('hide.bs.modal', function () {
	          document.getElementById('viewerIframe').contentWindow.document.getElementById('lesson-player').style.display = "block";
	        });
	      }
	
	      this.$element.modal({
	        backdrop: false
	      });
	    }
	  }, {
	    key: 'hide',
	    value: function hide() {
	      this.$element.modal('hide');
	    }
	  }]);
	
	  return CourseAd;
	}();
	
	exports["default"] = CourseAd;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _openCoursePlayer = __webpack_require__("ef77ea6623b706a87a55");
	
	var _openCoursePlayer2 = _interopRequireDefault(_openCoursePlayer);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	if ($('#firstLesson').length > 0) {
	  var openCoursePlayer = new _openCoursePlayer2["default"]({
	    url: $('#firstLesson').data('url'),
	    element: '.open-course-views'
	  });
	}

/***/ }),

/***/ "ef77ea6623b706a87a55":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _esSwfobject = __webpack_require__("c04c1b91e3806f24595a");
	
	var _esSwfobject2 = _interopRequireDefault(_esSwfobject);
	
	var _courseAd = __webpack_require__("87fce900c1efe27f8ef1");
	
	var _courseAd2 = _interopRequireDefault(_courseAd);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var OpenCoursePlayer = function () {
	  function OpenCoursePlayer(_ref) {
	    var url = _ref.url,
	        element = _ref.element;
	
	    _classCallCheck(this, OpenCoursePlayer);
	
	    this.url = url;
	    this.$element = $(element);
	
	    this.player = null;
	    this.lesson = null;
	    this.courseAd = null;
	
	    this.init();
	    this.initEvent();
	  }
	
	  _createClass(OpenCoursePlayer, [{
	    key: 'init',
	    value: function init() {
	      this.showPlayer();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.js-player-replay', function (event) {
	        return _this.replay(event);
	      });
	      this.$element.on('click', '.js-live-video-replay-btn', function (event) {
	        return _this.onLiveVideoPlay(event);
	      });
	    }
	  }, {
	    key: 'showPlayer',
	    value: function showPlayer() {
	      var _this2 = this;
	
	      $.get(this.url, function (lesson) {
	        console.log(_this2.url, lesson);
	        if (lesson.mediaError) {
	          $('#media-error-dialog').show();
	          $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
	          return;
	        }
	        $('#media-error-dialog').hide();
	        _this2.lesson = lesson;
	
	        var mediaSourceActionsMap = {
	          'iframe': _this2.onIframe,
	          'self': _this2.onVideo
	        };
	
	        var caller = mediaSourceActionsMap[lesson.mediaSource] ? mediaSourceActionsMap[lesson.mediaSource].bind(_this2) : undefined;
	
	        if (caller === undefined && (lesson.type == 'video' || lesson.type == 'audio')) {
	          caller = _this2.onSWF.bind(_this2);
	        }
	
	        if (caller === undefined) {
	          return;
	        }
	
	        caller(_this2);
	      });
	    }
	  }, {
	    key: 'onIframe',
	    value: function onIframe() {
	      var $ifrimeContent = $('#lesson-preview-iframe');
	      $ifrimeContent.empty();
	
	      var html = '<iframe class="embed-responsive-item" src="' + this.lesson.mediaUri + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
	
	      $ifrimeContent.html(html);
	      $ifrimeContent.show();
	    }
	  }, {
	    key: 'onVideo',
	    value: function onVideo() {
	      var lesson = this.lesson;
	
	      if (lesson.type == 'video' || lesson.type == 'audio') {
	        if (lesson.convertStatus != 'success' && lesson.storage == 'cloud') {
	          $('#media-error-dialog').show();
	          $('#media-error-dialog').find('.modal-body .media-error').html(Translator.trans('open_course.converting_hint'));
	          return;
	        }
	        var playerUrl = '/open/course/' + lesson.courseId + '/lesson/' + lesson.id + '/player';
	
	        this.videoPlay(playerUrl);
	      } else {
	        return;
	      }
	    }
	  }, {
	    key: 'onSWF',
	    value: function onSWF() {
	      var lesson = this.lesson;
	      var $swfContent = $('#lesson-preview-swf-player');
	
	      _esSwfobject2["default"].removeSWF('lesson-preview-swf-player');
	      $swfContent.html('<div id="lesson-swf-player"></div>');
	      _esSwfobject2["default"].embedSWF(lesson.mediaUri, 'lesson-swf-player', '100%', '100%', "9.0.0", null, null, {
	        wmode: 'opaque',
	        allowFullScreen: 'true'
	      });
	      $swfContent.show();
	    }
	  }, {
	    key: 'replay',
	    value: function replay() {
	      if (!this.player) {
	        window.location.reload();
	      } else {
	        this.player.replay();
	        this.courseAd.hide();
	      }
	    }
	  }, {
	    key: 'onLiveVideoPlay',
	    value: function onLiveVideoPlay(e) {
	      this.$element.find('.js-live-header-mask').hide();
	
	      var $target = $(e.currentTarget);
	
	      var lesson = this.lesson;
	
	      if (lesson.mediaError) {
	        $('#media-error-dialog').show();
	        $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
	        return;
	      }
	
	      $('#media-error-dialog').hide();
	
	      if (lesson.type == 'liveOpen' && lesson.replayStatus == 'videoGenerated') {
	        if (lesson.convertStatus != 'success' && lesson.storage == 'cloud') {
	          $('#media-error-dialog').show();
	          $('#media-error-dialog').find('.modal-body .media-error').html(Translator.trans('open_course.converting_hint'));
	          return;
	        }
	
	        var referer = $target.data('referer');
	        var playerUrl = '/open/course/' + lesson.courseId + '/lesson/' + lesson.id + '/player?referer=' + referer;
	
	        this.videoPlay(playerUrl);
	      } else {
	        return;
	      }
	    }
	  }, {
	    key: 'getPlayer',
	    value: function getPlayer() {
	      return window.frames["viewerIframe"].window.BalloonPlayer || window.frames["viewerIframe"].window.player;
	    }
	  }, {
	    key: 'videoPlay',
	    value: function videoPlay(playerUrl) {
	      var _this3 = this;
	
	      var $videoContent = $('#lesson-preview-player');
	      $videoContent.html('');
	
	      var html = '<iframe \n      class="embed-responsive-item" \n      src="' + playerUrl + '" \n      name="viewerIframe" \n      id="viewerIframe" \n      width="100%" \n      allowfullscreen \n      webkitallowfullscreen \n      height="100%"" \n      style="border:0px;position:absolute; left:0; top:0;"></iframe>';
	
	      $videoContent.html(html).show();
	
	      var messenger = new _messenger2["default"]({
	        name: 'parent',
	        project: 'PlayerProject',
	        children: [document.getElementById('viewerIframe')],
	        type: 'parent'
	      });
	
	      messenger.on("ready", function () {
	        // @TODO 不清楚这边有什么用
	        var player = _this3.getPlayer();
	        _this3.player = player;
	        console.log('player', player);
	      });
	
	      messenger.on("ended", function () {
	        console.log('ended');
	        _this3.onPlayEnd();
	      });
	    }
	  }, {
	    key: 'onPlayEnd',
	    value: function onPlayEnd() {
	      this.showADModal();
	    }
	  }, {
	    key: 'showADModal',
	    value: function showADModal() {
	      if (this.courseAd) {
	        this.courseAd.show();
	        return;
	      }
	
	      this.courseAd = new _courseAd2["default"]({
	        element: '#open-course-ad-modal',
	        courseUrl: this.$element.data('get-recommend-course-url')
	      });
	      this.courseAd.show();
	    }
	  }]);
	
	  return OpenCoursePlayer;
	}();
	
	exports["default"] = OpenCoursePlayer;

/***/ })

});
//# sourceMappingURL=index.js.map