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
	        $("#lesson-live-content").find('.lesson-content-text-body').html('<div class=\'live-show-item\'>\n        <p class=\'title\'>' + Translator.trans('site.activity.live.content_title') + '</p>\n        <p>' + Translator.trans('activity.liva.not_created_notice') + '\uFF01</p>\n        </div>');
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
	      this.$liveNotice = '<div class="live-show-item">\n          <p class="title">' + Translator.trans('activity.live.notice_title') + '</p>\n           ' + Translator.trans('activity.live.default_notice', { 'startTimeFormat': this.liveStartTimeFormat, 'endTimeFormat': this.liveEndTimeFormat }) + '\n         </div>';
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
	      var replayGuid = '';
	
	      if (activityData.ext.liveProvider == 1) {
	        replayGuid = Translator.trans('activity.live.replay_guid_1');
	      } else {
	        replayGuid = Translator.trans('activity.live.replay_guid');
	      }
	      replayGuid = '<div class=\'live-show-item\'>' + replayGuid + '</div>';
	      var $countDown = this._getCountDown(days, hours, minutes, seconds);
	      var $btn = '';
	
	      if (0 < startLeftSeconds && startLeftSeconds < 7200) {
	        this.$liveNotice = '<div class="live-show-item">\n          <p class="title">' + Translator.trans('activity.live.notice_title') + '</p>\n          ' + Translator.trans('activity.live.default_notice', { 'startTimeFormat': this.liveStartTimeFormat, 'endTimeFormat': this.liveEndTimeFormat }) + '\n         </div>';
	        $btn = '<div class=\'live-show-item\'>\n          <a class=\'btn btn-primary js-start-live\' href=\'javascript:;\'\n            onclick=\'$(liveShow.entryLiveRoom())\'>\n           ' + Translator.trans('activity.live.entry_live_room') + '\n          </a>\n        </div>';
	        if (activityData.isTeacher) {
	          $btn += replayGuid;
	        }
	      }
	      if (startLeftSeconds <= 0) {
	        clearInterval(this.iID);
	        $countDown = '';
	        this.$liveNotice = '<div class=\'live-show-item\'>\n          <p class="title">' + Translator.trans('activity.live.notice_title') + '</p>\n          ' + Translator.trans('activity.live.started_notice', { 'endTimeFormat': this.liveEndTimeFormat }) + '\n        </div>';
	        $btn = '<div class=\'live-show-item\'>\n          <a class=\'btn btn-primary js-start-live\' href=\'javascript:;\'\n            onclick=\'$(liveShow.entryLiveRoom())\'>\n            ' + Translator.trans('activity.live.entry_live_room') + '\n          </a>\n        </div>';
	        if (activityData.isTeacher) {
	          $btn += replayGuid;
	        }
	      }
	      if (endLeftSeconds <= 0) {
	        $countDown = "";
	        $btn = '';
	        this.$liveNotice = '<div class=\'live-show-item\'>\n          <i class=\'es-icon es-icon-xinxi color-danger icon-live-end\'></i>\n          ' + Translator.trans('activity.live.ended_notice') + '\n        </div>';
	        if (activityData.replays && activityData.replays.length > 0) {
	          $.each(activityData.replays, function (i, n) {
	            $btn += "<a class='btn btn-primary btn-replays' href='" + n.url + "' target='_blank'>" + n.title + "</a>";
	          });
	          $btn = '<div class=\'live-show-item\'>' + $btn + '</div>';
	        }
	      }
	
	      var $content = this.$liveNotice + ' ' + $countDown + '\n      <div class=\'live-show-item\'>\n        <p class=\'title\'>' + Translator.trans('activity.live.content_title') + '</p>\n        ' + this.summary + '\n      </div>' + $btn;
	      $("#lesson-live-content").find('.lesson-content-text-body').html($content);
	      this.intervalSecond++;
	    }
	  }, {
	    key: '_getCountDown',
	    value: function _getCountDown(days, hours, minutes, seconds) {
	      var content = '';
	      content += days ? Translator.trans('site.date_format_dhis', { 'days': days, 'hours': hours, 'minutes': minutes, 'seconds': seconds }) : "";
	      content += hours ? Translator.trans('site.date_format_his', { 'hours': hours, 'minutes': minutes, 'seconds': seconds }) : "";
	      content += minutes ? Translator.trans('site.date_format_is', { 'minutes': minutes, 'seconds': seconds }) : "";
	      content += seconds ? Translator.trans('site.date_format_s', { 'seconds': seconds }) : "";
	      content = '<div class=\'live-show-item\'>\n      <p class=\'title\'>' + Translator.trans('activity.live.count_down_title') + '</p>\n      <span class="color-warning">' + content + '</span>\n    </div>';
	      return content;
	    }
	  }]);
	
	  return LiveShow;
	}();
	
	exports.default = LiveShow;

/***/ })

});