import Swiper from 'swiper';
import 'common/tabs-lavalamp/index';
import 'common/card';
import 'common/es-polyfill';
import 'app/common/reward-point-notify';
import { isMobileDevice } from 'common/utils';
import Cookies from 'js-cookie';
import 'app/less/main.less';

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
    if (ua.match(/micromessenger/i) == "micromessenger" && $('meta[name=is-open]').attr('content') != 0) {
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
    Cookies.set("close_set_email_alert", 'true');
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
    Cookies.set("close_announcements_alert", 'true', {
      path: '/'
    });
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

$("select[name='language']").change(function () {
  Cookies.set("locale", $('select[name=language]').val(), { 'path': '/' });
  $("select[name='language']").parents('form').trigger('submit');
});

let eventPost = function($obj) {
    let postData = $obj.data();
    $.post($obj.data('url'), postData)
}

$('.event-report').each(function(){
    (function($obj){
        eventPost($obj);
    })($(this));
})

$('body').on('event-report', function(e, name){
    let $obj = $(name);
    eventPost($obj);
})

