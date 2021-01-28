import TaskShow from './task';
import { Browser, isMobileDevice } from 'common/utils';
import Cookies from 'js-cookie';
let $taskContent = $('#task-content-iframe');
$taskContent.attr('src', $taskContent.data('url'));
new TaskShow({
  element: $('body'),
  mode: $('body').find('#js-hidden-data [name="mode"]').val()
});

if (Browser.ie10 || Browser.ie11 || Browser.edge) {
  const iframeDom = document.getElementById('task-content-iframe');
  iframeDom.onload = () => {
    const iframeContent = iframeDom.contentWindow;
    const iframeHtml = iframeContent.document.getElementsByTagName('html')[0];
    iframeHtml.style.width = '100%';
  };
}

let $adBtn = $('.js-audio-convert-ad');
if ($adBtn.length > 0) {
  $adBtn.on('click', function(event) {
    Cookies.set($adBtn.data('cookie'), 'true', {expires: 360, path: '/'});
    $adBtn.parents('.js-audio-convert-box').remove();
    $('.js-dashboard-footer').removeClass('dashboard-footer--audio');
  });
}

// 微信通知
if ($('.js-wechat-qrcode-btn').length > 0) {
  var $target = $('.js-wechat-qrcode-btn');
  if (typeof($target.data('url')) != 'undefined') {
    $.get($target.data('url'), res => {
      $target.data('img', res.img);
      const src = res.img;
      $('.js-wechat-qrcode-btn').popover({
        trigger: 'click',
        placement: 'bottom',
        html: 'true',
        animation: false,
        container: 'body',
        content: `<img class="wechat-inform-task-qrcode" src="${src}">`
      });
    });
  } else {
    const src = $target.data('img');
    $('.js-wechat-qrcode-btn').popover({
      trigger: 'click',
      placement: 'bottom',
      html: 'true',
      animation: false,
      container: 'body',
      content: `<img class="wechat-inform-task-qrcode" src="${src}">`
    });
  }
}


const videoPopover = ($popover, offset, flag) => {
  const $iframe = $('.js-task-content-iframe');
  const $wrapper = $('.js-video-wrapper');
  const height = $iframe.height();
  const width = $iframe.width();
  $popover.on('show.bs.popover', () => {
    const popHeight = height - offset;
    if (flag) {
      $wrapper.css('height', popHeight);
    } else {
      if ($popover.hasClass('js-next-task')) {
        const value = offset + 30;
        $wrapper.css('height', height - value);
      } else {
        $wrapper.css('height', height - offset);
      }
    }
  });

  $popover.on('shown.bs.popover', () => {
    $('.popover').css({
      minWidth: width,
      left: '15px'
    }).find('.arrow').hide();
  });

  $popover.on('hidden.bs.popover',  () => {
    $wrapper.css('height', height);
  });
};

const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
if (isMobileDevice() && !isIOS) {
  const $prompt = $('.js-learn-video-prompt');
  const $learnedPrompt = $('.js-learned-video-prompt');
  videoPopover($prompt, 50, true);
  videoPopover($learnedPrompt, 115);
}
