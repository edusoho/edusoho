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
        $('#entry').html(Translator.trans('course_set.live_room.entry_error_hint'));
        return;
      }
      $.ajax({
        url: $('#entry').data('url'),
        success: function(data) {
          if (data.error) {
            clearInterval(intervalId);
            $('#entry').html(Translator.trans('course_set.live_room.entry_error_with_message', {message: data.error}));
            return;
          }

          if (data.roomUrl) {
            let provider = $('#entry').data('provider');
            let role = $('#entry').data('role');
            let $uapraser = new UAParser(navigator.userAgent);
            let browser = $uapraser.getBrowser();
            let os = $uapraser.getOS();

            if (document.location.protocol ==='http:' && role === 'student' && (provider === 8 || provider === 9) && os.name !== ('Android'||'iOS'||'Windows Phone'||'Windows Mobile') &&  browser.name === 'Chrome' && browser.major >= 60) {
              window.location.href = data.roomUrl;
            }

            clearInterval(intervalId);
            self.isLiveRoomOpened = true;
            let html = '<iframe name="classroom" src="' + data.roomUrl + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
            $('body').html(html);
          }
          tryCount++;
        },
        error: function() {
          $('#entry').html(Translator.trans('course_set.live_room.entry_error_hint'));
        }
      });
    }, 3000);

    this.triggerLiveEvent();
  }

  triggerLiveEvent() {
    let self = this;
    
    let eventName = null;
    let timestamp = Date.parse( new Date() ).toString();
    timestamp = timestamp.substr(0,10);
    let eventTrigger = setInterval(function() {
      if (!self.isLiveRoomOpened || $('meta[name="trigger_url"]').length == 0) return;
      eventName = eventName ? 'doing' : 'start';
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
      timestamp = Date.parse( new Date() ).toString();
      timestamp = timestamp.substr(0,10);
    }, 60000);

  }
}

new Live();