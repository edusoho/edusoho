import 'babel-polyfill';
import 'jquery';
import 'bootstrap';
import Swiper from 'common/swiper';
// import 'placeholder';
import 'es6-promise/auto';
import 'libs/js/jquery-lavalamp';
import 'common/bootstrap-modal-hack';
import 'common/script';
import 'common/card';
import 'common/es-polyfill';
import { isMobileDevice } from 'common/utils';

import './vendor.less';

// 等待确认可删除Cookie
// var Cookie = require('cookie');  

$('[data-toggle="popover"]').popover({
  html: true,
});

$('[data-toggle="tooltip"]').tooltip({
  html: true,
});

$(document).ajaxError(function (event, jqxhr, settings, exception) {
  if (jqxhr.responseText === 'LoginLimit') {
    location.href = '/login';
  }
  let json = jQuery.parseJSON(jqxhr.responseText);
  let error = json.error;
  if (!error) {
    return;
  }

  if (error.name === 'Unlogin') {
    let ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) === "micromessenger" && $('meta[name=is-open]').attr('content') !== 0) {
      window.location.href = '/login/bind/weixinmob?_target_path=' + location.href;
    } else {
      let $loginModal = $("#login-modal");
      $('.modal').modal('hide');
      $loginModal.modal('show');
      $.get($loginModal.data('url'), function (html) {
        $loginModal.html(html);
      });
    }
  }
});

$(document).ajaxSend(function (a, b, c) {
  if (c.type === 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }
});

if (app.scheduleCrontab) {
  $.post(app.scheduleCrontab);
}

$("i.hover-spin").mouseenter(function () {
  $(this).addClass("md-spin");
}).mouseleave(function () {
  $(this).removeClass("md-spin");
});

if ($(".set-email-alert").length > 0) {
  $(".set-email-alert .close").click(function () {
    // Cookie.set("close_set_email_alert", 'true');
  });
}

if ($(".announcements-alert").length > 0) {
  if ($('.announcements-alert .swiper-container .swiper-wrapper').children().length > 1) {
    let noticeSwiper = new Swiper('.alert-notice .swiper-container', {
      speed: 300,
      loop: true,
      mode: 'vertical',
      autoplay: 5000,
      calculateHeight: true
    });
  }

  $(".announcements-alert .close").click(function () {
    // Cookie.set("close_announcements_alert", 'true', {
    //   path: '/'
    // });
  });
}

if (!isMobileDevice()) {
  $("body").on("mouseenter", "li.nav-hover", function (event) {
    $(this).addClass("open");
  }).on("mouseleave", "li.nav-hover", function (event) {
    $(this).removeClass("open");
  });
} else {
  $("li.nav-hover >a").attr("data-toggle", "dropdown");
}

$(".js-search").focus(function () {
  $(this).prop("placeholder", "").addClass("active");
}).blur(function () {
  $(this).prop("placeholder", Translator.trans('搜索')).removeClass("active");
});

if ($(".nav.nav-tabs").length > 0 && !isMobileDevice()) {
  // console.log(lavaLamp);
  console.log($(".nav.nav-tabs"));
  $(".nav.nav-tabs").lavaLamp();
}

$("select[name='language']").change(function () {
  // Cookie.set("locale", $('select[name=language]').val(), { 'path': '/' });
  $("select[name='language']").parents('form').trigger('submit');
});