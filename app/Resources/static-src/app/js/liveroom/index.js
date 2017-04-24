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
        let html = Translator.trans('进入直播教室错误，请联系管理员，') + "<a href='javascript:document.location.reload()'>" + Translator.trans('重试') + "</a>" + Translator.trans('或') + "<a href='javascript:window.close();'>" + Translator.trans('关闭') + "</a>";
        $("#entry").html(html);
        return;
      }
      $.ajax({
        url: $("#entry").data("url"),
        success: function(data) {
          if (data.error) {
            clearInterval(intervalId);
            let html = data.error + Translator.trans('，') + "<a href='javascript:document.location.reload()'>" + Translator.trans('重试') + "</a>或<a href='javascript:window.close();'>" + Translator.trans('关闭') + "</a>";
            $("#entry").html(html);
            return;
          }

          if (data.roomUrl) {
            clearInterval(intervalId);
            self.isLiveRoomOpened = true;
            let html = '<iframe name="classroom" src="' + data.roomUrl + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
            $("body").html(html);
          }
          tryCount++;
        },
        error: function() {
          let html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
          $("#entry").html(html);
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
      $.ajax({
        url: $('meta[name="trigger_url"]').attr('content'),
        type: 'GET',
        data: { eventName: eventName },
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
