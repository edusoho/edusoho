webpackJsonp(["app/js/activity/live/index"],{

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
	
	var _live = __webpack_require__("c6797855be84e924b7d5");
	
	var _live2 = _interopRequireDefault(_live);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	window.liveShow = new _live2.default();

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
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var LiveShow = function () {
	  function LiveShow() {
	    _classCallCheck(this, LiveShow);
	
	    this.init();
	  }
	
	  _createClass(LiveShow, [{
	    key: 'init',
	    value: function init() {
	      var _this = this;
	
	      var hasRoom = $('#lesson-live-content').data('hasRoom') == '1';
	      if (!hasRoom) {
	        $("#lesson-live-content").find('.lesson-content-text-body').html('<div class=\'live-show-item\'>\n        <p class=\'title\'>\u76F4\u64AD\u8BF4\u660E</p>\n        <p>\u76F4\u64AD\u6559\u5BA4\u5C1A\u672A\u521B\u5EFA\uFF01</p>\n        </div>');
	        $("#lesson-live-content").show();
	        return;
	      }
	
	      var activityData = JSON.parse($('#activity-data').html());
	      var startTime = parseInt(activityData.startTime);
	      var endTime = parseInt(activityData.endTime);
	      var nowDate = parseInt(activityData.nowDate);
	      this.liveStartTimeFormat = activityData.startTimeFormat;
	      this.liveEndTimeFormat = activityData.endTimeFormat;
	      var courseId = activityData.fromCourseId;
	      var activityId = activityData.id;
	      // let replayStatus = activityData.ext.replayStatus || 'ungenerated';
	      this.summary = $('#activity-summary').text();
	      this.$liveNotice = '<div class="live-show-item">\n          <p class="title">\u76F4\u64AD\u65F6\u95F4</p>\n          <p>\u76F4\u64AD\u5C06\u4E8E' + this.liveStartTimeFormat + '\u5F00\u59CB\uFF0C\u4E8E' + this.liveEndTimeFormat + '\u7ED3\u675F<p>\n          (\u8BF7\u5728\u8BFE\u524D10\u5206\u949F\u5185\u63D0\u65E9\u8FDB\u5165)\n         </div>';
	      this.iID = null;
	      if (this.iID) {
	        clearInterval(iID);
	      }
	      this.intervalSecond = 0;
	      this.entry_url = location.protocol + "//" + location.hostname + '/course/' + courseId + '/activity/' + activityId + '/live_entry';
	      this.generateHtml();
	      var millisecond = 0;
	      if (endTime > nowDate) {
	        millisecond = 1000;
	      }
	      this.iID = setInterval(function () {
	        _this.generateHtml();
	      }, millisecond);
	
	      $("#lesson-live-content").show();
	      this.started = false;
	    }
	  }, {
	    key: 'entryLiveRoom',
	    value: function entryLiveRoom() {
	      var that = this;
	      console.log('startLive', this.started, this.entry_url);
	      if (!this.started) {
	        this.started = true;
	        var emitter = new _activityEmitter2.default();
	        emitter.emit('start', {}).then(function () {
	          console.log('live.start');
	        }).catch(function (error) {
	          console.error(error);
	        });
	      }
	      window.open(this.entry_url, '_blank');
	    }
	  }, {
	    key: 'generateHtml',
	    value: function generateHtml() {
	      var activityData = JSON.parse($('#activity-data').text());
	      var startTime = parseInt(activityData.startTime);
	      var endTime = parseInt(activityData.endTime);
	      var nowDate = parseInt(activityData.nowDate);
	      nowDate = nowDate + this.intervalSecond;
	      var startLeftSeconds = parseInt(startTime - nowDate);
	      var endLeftSeconds = parseInt(endTime - nowDate);
	      var days = Math.floor(startLeftSeconds / (60 * 60 * 24));
	      var modulo = startLeftSeconds % (60 * 60 * 24);
	      var hours = Math.floor(modulo / (60 * 60));
	      modulo = modulo % (60 * 60);
	      var minutes = Math.floor(modulo / 60);
	      var seconds = modulo % 60;
	      var $replayGuid = Translator.trans('老师们：');
	
	      if (activityData.ext.liveProvider == 1) {
	        $replayGuid += Translator.trans('录制直播课程时，需在直播课程间点击') + '\n          <span class=\'color-info\'>' + Translator.trans('录制面板') + '</span>\uFF0C' + Translator.trans('，录制完成后点击') + '\n          <span class=\'color-info\'>' + Translator.trans('暂停') + '</span>' + Translator.trans('结束录播，录播结束后在') + '\n          <span class=\'color-info\'>' + Translator.trans('录播管理') + '</span>' + Translator.trans('界面生成回放。') + '\u3002\n        ';
	      } else {
	        $replayGuid += Translator.trans('直播平台') + '\n        <span class=\'color-info\'>' + Translator.trans('下课后') + '</span>' + Translator.trans('且') + '\n        <span class=\'color-info\'>' + Translator.trans('直播时间') + '</span>' + Translator.trans('结束后，在课时管理的') + '\n        <span class=\'color-info\'>' + Translator.trans('录播管理') + '</span>' + Translator.trans('点击生成回放。') + '\n        ';
	      }
	      $replayGuid = '<div class=\'live-show-item\'>' + $replayGuid + '</div>';
	      var $countDown = this._getCountDown(days, hours, minutes, seconds);
	      var $btn = '';
	
	      if (0 < startLeftSeconds && startLeftSeconds < 7200) {
	        this.$liveNotice = '<div class="live-show-item">\n          <p class="title">\u76F4\u64AD\u65F6\u95F4</p>\n          <p>\u76F4\u64AD\u5C06\u4E8E' + this.liveStartTimeFormat + '\u5F00\u59CB\uFF0C\u4E8E' + this.liveEndTimeFormat + '\u7ED3\u675F<p>\n          (\u8BF7\u5728\u8BFE\u524D10\u5206\u949F\u5185\u63D0\u65E9\u8FDB\u5165)\n         </div>';
	        $btn = '<div class=\'live-show-item\'>\n          <a class=\'btn btn-primary js-start-live\' href=\'javascript:;\'\n            onclick=\'$(liveShow.entryLiveRoom())\'>\n            ' + Translator.trans('进入直播教室') + '\n          </a>\n        </div>';
	        if (activityData.isTeacher) {
	          $btn += $replayGuid;
	        }
	      }
	      if (startLeftSeconds <= 0) {
	        clearInterval(this.iID);
	        $countDown = '';
	        this.$liveNotice = '<div class=\'live-show-item\'>\n          <p class="title">\u76F4\u64AD\u65F6\u95F4</p>\n          \u76F4\u64AD\u5DF2\u7ECF\u5F00\u59CB\uFF0C\u76F4\u64AD\u5C06\u4E8E' + this.liveEndTimeFormat + '\u7ED3\u675F\u3002\n        </div>';
	        $btn = '<div class=\'live-show-item\'>\n          <a class=\'btn btn-primary js-start-live\' href=\'javascript:;\'\n            onclick=\'$(liveShow.entryLiveRoom())\'>\n            ' + Translator.trans('进入直播教室') + '\n          </a>\n        </div>';
	        if (activityData.isTeacher) {
	          $btn += $replayGuid;
	        }
	      }
	      if (endLeftSeconds <= 0) {
	        $countDown = "";
	        $btn = '';
	        this.$liveNotice = '<div class=\'live-show-item\'>\n          <i class=\'es-icon es-icon-xinxi color-danger icon-live-end\'></i>\n          ' + Translator.trans('直播已经结束') + '\n        </div>';
	        if (activityData.replays && activityData.replays.length > 0) {
	          $.each(activityData.replays, function (i, n) {
	            $btn += "<a class='btn btn-primary btn-replays' href='" + n.url + "' target='_blank'>" + n.title + "</a>";
	          });
	          $btn = '<div class=\'live-show-item\'>' + $btn + '</div>';
	        }
	      }
	
	      var $content = this.$liveNotice + ' ' + $countDown + '\n      <div class=\'live-show-item\'>\n        <p class=\'title\'>\u76F4\u64AD\u8BF4\u660E</p>\n        ' + this.summary + '\n      </div>' + $btn;
	      $("#lesson-live-content").find('.lesson-content-text-body').html($content);
	      this.intervalSecond++;
	    }
	  }, {
	    key: '_getCountDown',
	    value: function _getCountDown(days, hours, minutes, seconds) {
	      var content = '';
	      content += days ? days + Translator.trans(' 天 ') : "";
	      content += hours ? hours + Translator.trans(' 小时 ') : "";
	      content += minutes ? minutes + Translator.trans(' 分钟 ') : "";
	      content += seconds ? seconds + Translator.trans(' 秒 ') : "";
	      content = '<div class=\'live-show-item\'>\n      <p class=\'title\'>' + Translator.trans('倒计时') + '</p>\n      <span class="color-warning">' + content + '</span>\n    </div>';
	      return content;
	    }
	  }]);
	
	  return LiveShow;
	}();
	
	exports.default = LiveShow;

/***/ })

});