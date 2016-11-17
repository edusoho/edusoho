define(function(require, exports, module) {

  var player = require('./player.js');

  var $elem = $('.js-editbox')
  var $editbox_list = $('#editbox-lesson-list');
  var partnum = 6;
  mediaLength = $elem.data('mediaLength');
  var parttime = mediaLength / partnum;
  for (var i = 0; i <= partnum; i++) {
    var $new_scale_default = $('[data-role="scale-default"]').clone().css('left', getleft(parttime * i, mediaLength)).removeClass('hidden').removeAttr('data-role');
    $new_scale_default.find('[data-role="scale-time"]').text(convertTime(Math.round(parttime * i)));
    $('[data-role="scale-default"]').before($new_scale_default);
  }
  player.on("timechange", function(data) {
    $('.scale-white').css('left', getleft(data.currentTime, mediaLength));
  });
  $('.scale-white').on('mousedown', function(event) {
    var changeleft = false;
    $(document).on('mousemove.playertime', function(event) {
      window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
      var left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
      $('.scale-white').css('left', left);
      var times = gettime(left, mediaLength);
      player.sendToChild({ id: 'viewerIframe' }, 'setCurrentTime', { time: times });
    }).on('mouseup.playertime', function(event) {
      $(document).off('mousemove.playertime');
      $(document).off('mousedown.playertime');
      changeleft = true;
      // player.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
    });

  });

  function getleft(time, videoLength) {
    var _width = $('#editbox-lesson-list').width();
    var _totaltime = parseInt(videoLength);
    var _left = time * _width / _totaltime;
    return _left + 20;
  }

  function convertTime(num) {
    var time = "";
    var h = parseInt((num % 86400) / 3600);
    var s = parseInt((num % 3600) / 60);
    var m = num % 60;
    if (h > 0) {
      time += h + ':';
    }
    if (s.toString().length < 2) {
      time += '0' + s + ':';
    } else {
      time += s + ':';

    }
    if (m.toString().length < 2) {
      time += '0' + m;
    } else {
      time += m;
    }
    return time;
  }
  gettime = function(left, mediaLength) {
    return Math.round((left - 20) * mediaLength / $('#editbox-lesson-list').width());
  }

})
