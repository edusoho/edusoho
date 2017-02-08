import './vendor.less';

import 'babel-polyfill';
import 'jquery';
import 'bootstrap';
import 'bootstrap-notify';

import 'common/bootstrap-modal-hack';
import 'common/script';

$('[data-toggle="popover"]').popover({
  html: true,
  trigger: 'hover',
  content: function () {
    return $(this).siblings('.popover-content').html();
  }
});

$('[data-toggle="tooltip"]').tooltip({
  html: true,
});

if (!navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
  $("li.nav-hover").mouseenter(function (event) {
    $(this).addClass("open");
  }).mouseleave(function (event) {
    $(this).removeClass("open");
  });

} else {
  $("li.nav-hover >a").attr("data-toggle", "dropdown");
}

$(document).ajaxError(function (event, jqxhr, settings, exception) {
  if (jqxhr.responseText === 'LoginLimit') {
    location.href = '/login';
  }
  var json = jQuery.parseJSON(jqxhr.responseText);
  let error = json.error;
  if (!error) {
    return;
  }

  if (error.name == 'Unlogin') {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger" && $('meta[name=is-open]').attr('content') != 0) {
      window.location.href = '/login/bind/weixinmob?_target_path=' + location.href;
    } else {
      var $loginModal = $("#login-modal");
      $('.modal').modal('hide');
      $loginModal.modal('show');
      $.get($loginModal.data('url'), function (html) {
        $loginModal.html(html);
      });
    }
  }
});

if ($('html').hasClass('lt-ie8')) {
  var message = '<div class="alert alert-warning" style="margin-bottom:0;text-align:center;">';
  message += Translator.trans('由于您的浏览器版本太低，将无法正常使用本站点，请使用最新的');
  message += '<a href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie" target="_blank">' + Translator.trans('IE浏览器') + '</a>、';
  message += '<a href="http://www.baidu.com/s?wd=%E8%B0%B7%E6%AD%8C%E6%B5%8F%E8%A7%88%E5%99%A8" target="_blank">' + Translator.trans('谷歌浏览器') + '</a>' + '<strong>' + '(' + Translator.trans('推荐') + ')' + '</strong>、';
  message += '<a href="http://firefox.com.cn/download/" target="_blank">' + Translator.trans('Firefox浏览器') + '</a>' + '，' + Translator.trans('访问本站。');
  message += '</div>';

  $('body').prepend(message);
}

$(document).ajaxSend(function (a, b, c) {
  if (c.type == 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});