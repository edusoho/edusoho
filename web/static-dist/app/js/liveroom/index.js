webpackJsonp(["app/js/liveroom/index"],[
/* 0 */
/***/ (function(module, exports) {

	"use strict";
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Live = function () {
	  function Live() {
	    _classCallCheck(this, Live);
	
	    this.init();
	  }
	
	  _createClass(Live, [{
	    key: "init",
	    value: function init() {
	      var self = this;
	      this.isLiveRoomOpened = false;
	      var intervalId = 0;
	      var tryCount = 1;
	      intervalId = setInterval(function () {
	        if (tryCount > 10) {
	          clearInterval(intervalId);
	          $("#entry").html(Translator.trans('course_set.live_room.entry_error_hint'));
	          return;
	        }
	        $.ajax({
	          url: $("#entry").data("url"),
	          success: function success(data) {
	            if (data.error) {
	              clearInterval(intervalId);
	              $("#entry").html(Translator.trans('course_set.live_room.entry_error_with_message', { message: data.error }));
	              return;
	            }
	
	            if (data.roomUrl) {
	              clearInterval(intervalId);
	              self.isLiveRoomOpened = true;
	              var html = '<iframe name="classroom" src="' + data.roomUrl + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
	              $("body").html(html);
	            }
	            tryCount++;
	          },
	          error: function error() {
	            $("#entry").html(Translator.trans('course_set.live_room.entry_error_hint'));
	          }
	        });
	      }, 3000);
	
	      this.triggerLiveEvent();
	    }
	  }, {
	    key: "triggerLiveEvent",
	    value: function triggerLiveEvent() {
	      var self = this;
	
	      var eventName = null;
	      var eventTrigger = setInterval(function () {
	        if (!self.isLiveRoomOpened || $('meta[name="trigger_url"]').length == 0) return;
	        eventName = eventName ? 'doing' : 'start';
	        $.ajax({
	          url: $('meta[name="trigger_url"]').attr('content'),
	          type: 'GET',
	          data: { eventName: eventName },
	          success: function success(response) {
	            if (response.live_end) {
	              clearInterval(eventTrigger);
	            }
	          }
	        });
	      }, 60000);
	    }
	  }]);
	
	  return Live;
	}();
	
	new Live();

/***/ })
]);
//# sourceMappingURL=index.js.map