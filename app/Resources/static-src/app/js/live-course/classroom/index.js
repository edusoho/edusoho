let intervalId = 0;
let tryCount = 1;

function getRoomUrl() {
  if (tryCount > 10) {
    clearInterval(intervalId);

    let html = Translator.trans('classroom.live_room.entry_error_hint');
    
    $('#classroom-url').html(html);
    return;
  }
  $.ajax({
    url: $('#classroom-url').data('url'),
    success: function(data) {
      if (data.error) {
        clearInterval(intervalId);

        let html = data.error+Translator.trans('，')+Translator.trans('classroom.live_room.retry_or_close');
        
        $('#classroom-url').html(html);
        return;
      }

      if (data.url) {
        let url = data.url;
        if (data.param) {
          url = url+'?param='+data.param;
        }
        let html = '<iframe name="classroom" src="'+url+'" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no" allowfullscreen="true"></iframe>';

        $('body').html(html);

        clearInterval(intervalId);
      }

      tryCount ++;
    },
    error: function() {
      //var html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
      //$("#classroom-url").html(html);
    }
  });
}

getRoomUrl();

intervalId = setInterval(getRoomUrl, 3000);