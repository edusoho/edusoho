import Swiper from 'swiper';
import Cookies from 'js-cookie';

import 'codeages-design';
import 'common/tabs-lavalamp';
import 'common/card';
import 'common/bootstrap-modal-hack';
import RewardPointNotify from 'app/common/reward-point-notify';
import { isMobileDevice } from 'common/utils';
import notify from 'common/notify';
import './alert';

let rpn = new RewardPointNotify();
rpn.display();

$(document).ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions){
  rpn.push(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));
  rpn.display();
});

if ($('#rewardPointNotify').length > 0) {
  let message = $('#rewardPointNotify').text();
  if (message) {
    notify('success', decodeURIComponent(message));
  }
}

$('[data-toggle="popover"]').popover({
  html: true
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
    if (ua.match(/micromessenger/i) == 'micromessenger' && $('meta[name=is-open]').attr('content') != 0) {
      window.location.href = '/login/bind/weixinmob?_target_path=' + location.href;
    } else {
      let $loginModal = $('#login-modal');
      $('.modal').modal('hide');
      $loginModal.modal('show');
      $.get($loginModal.data('url'), function (html) {
        $loginModal.html(html);
      });
    }
  }
});

$(document).ajaxSend(function (a, b, c) {
  // 加载loading效果
  let url = c.url;
  url = url.split('?')[0];
  let $dom = $(`[data-url="${url}"]`);
  if ($dom.data('loading')) {
    let loading;
    loading = cd.loading({
      isFixed: $dom.data('is-fixed')
    });

    let loadingBox = $($dom.data('target') || $dom);
    loadingBox.html(loading);
  }

  if (c.type === 'POST') {
    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
  }

});

if (app.scheduleCrontab) {
  $.post(app.scheduleCrontab);
}

$('i.hover-spin').mouseenter(function () {
  $(this).addClass('md-spin');
}).mouseleave(function () {
  $(this).removeClass('md-spin');
});

if ($('#announcements-alert').length && $('#announcements-alert .swiper-container .swiper-wrapper').children().length > 1) {
  let noticeSwiper = new Swiper('#announcements-alert .swiper-container', {
    speed: 300,
    loop: true,
    mode: 'vertical',
    autoplay: 5000,
    calculateHeight: true
  });
}

if (!isMobileDevice()) {
  $('body').on('mouseenter', 'li.nav-hover', function (event) {
    $(this).addClass('open');
  }).on('mouseleave', 'li.nav-hover', function (event) {
    $(this).removeClass('open');
  });
} else {
  $('li.nav-hover >a').attr('data-toggle', 'dropdown');
}

$('.js-search').focus(function () {
  $(this).prop('placeholder', '').addClass('active');
}).blur(function () {
  $(this).prop('placeholder', Translator.trans('site.search_hint')).removeClass('active');
});

$('select[name=\'language\']').change(function () {
  Cookies.set('locale', $('select[name=language]').val(), { 'path': '/' });
  $('select[name=\'language\']').parents('form').trigger('submit');
});

let eventPost = function($obj) {
  let postData = $obj.data();
  $.post($obj.data('url'), postData);
};

$('.event-report').each(function(){
  (function($obj){
    eventPost($obj);
  })($(this));
});

$('body').on('event-report', function(e, name){
  let $obj = $(name);
  eventPost($obj);
});

$('.modal').on('hidden.bs.modal', function(){
  let $modal = $(this);
  if (1 == $modal.find('.modal-dialog').data('clear')) {
    $modal.html('');
  }
});

$.ajax('/online/sample');