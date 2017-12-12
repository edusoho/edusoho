import UAParser from 'ua-parser-js';

class Live {
  constructor() {
    this.init();
  }

  init() {
    let self = this;
    this.isLiveRoomOpened = false;
    let intervalId = 0;
    let tryCount = 1;
    intervalId = setInterval(function() {
      if (tryCount > 10) {
        clearInterval(intervalId);
        $("#entry").html(Translator.trans('course_set.live_room.entry_error_hint'));
        return;
      }
      $.ajax({
        url: $("#entry").data("url"),
        success: function(data) {
          if (data.error) {
            clearInterval(intervalId);
            $("#entry").html(Translator.trans('course_set.live_room.entry_error_with_message', {message: data.error}));
            return;
          }

          if (data.roomUrl) {
            let provider = $("#entry").data('provider');
            let $uapraser = new UAParser(navigator.userAgent);
            console.log($uapraser.getBrowser());
            let version = navigator.userAgent.match(/Version\/(.*?)\s/) || navigator.userAgent.match(/Chrome\/(\d+)/);
            if (document.location.protocol ==='http:' && provider === 8 && version && version[1] >= 60) {
              window.location.href = data.roomUrl;
            }

            clearInterval(intervalId);
            self.isLiveRoomOpened = true;
            let html = '<iframe name="classroom" src="' + data.roomUrl + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
            $("body").html(html);
          }
          tryCount++;
        },
        error: function() {
          $("#entry").html(Translator.trans('course_set.live_room.entry_error_hint'));
        }
      })
    }, 3000);

    this.triggerLiveEvent();
  }

  triggerLiveEvent() {
    let self = this;
    
    let eventName = null;
    let eventTrigger = setInterval(function() {
      if (!self.isLiveRoomOpened || $('meta[name="trigger_url"]').length == 0) return;
      eventName = eventName ? 'doing' : 'start';
      let timestamp = Date.parse( new Date() ).toString();
      timestamp = timestamp.substr(0,10);
      $.ajax({
        url: $('meta[name="trigger_url"]').attr('content'),
        type: 'GET',
        data: { eventName: eventName, data: {lastTime: timestamp, events: {watching: {watchTime: 60}}}},
        success: function(response) {
          if (response.live_end) {
            clearInterval(eventTrigger);
          }
        }
      });
    }, 60000);

  }
}

new Live();