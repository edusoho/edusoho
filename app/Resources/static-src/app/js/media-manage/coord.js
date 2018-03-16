import messenger from './messenger';
import * as Tool from 'common/utils';

let $elem = $('.js-editbox');
let $editbox_list = $('#editbox-lesson-list');
let partnum = 6;
let mediaLength = $elem.data('mediaLength');
let parttime = mediaLength / partnum;

for (let i = 0; i <= partnum; i++) {
  let $new_scale_default = $('[data-role="scale-default"]').clone().css('left', getleft(parttime * i, mediaLength)).removeClass('hidden').removeAttr('data-role');
  $new_scale_default.find('[data-role="scale-time"]').text(Tool.sec2Time(Math.round(parttime * i)));
  $('[data-role="scale-default"]').before($new_scale_default);
}

messenger.on('timechange', function(data) {
  $('.scale-white').css('left', getleft(data.currentTime, mediaLength));
});

$('.scale-white').on('mousedown', function(event) {
  let changeleft = false;
  $(document).on('mousemove.playertime', function(event) {
    window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
    let left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
    $('.scale-white').css('left', left);
    let times = gettime(left, mediaLength);
    messenger.sendToChild({ id: 'viewerIframe' }, 'setCurrentTime', { time: times });
  }).on('mouseup.playertime', function(event) {
    $(document).off('mousemove.playertime');
    $(document).off('mousedown.playertime');
    changeleft = true;
    // messenger.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
  });

});

function getleft(time, videoLength) {
  let _width = $('#editbox-lesson-list').width();
  let _totaltime = parseInt(videoLength);
  let _left = time * _width / _totaltime;
  return _left + 20;
}

function gettime(left, mediaLength) {
  return Math.round((left - 20) * mediaLength / $('#editbox-lesson-list').width());
}