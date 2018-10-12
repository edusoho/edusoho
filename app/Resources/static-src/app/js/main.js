import Swiper from 'swiper';
import Cookies from 'js-cookie';

import 'codeages-design';
import 'common/tabs-lavalamp';
import 'common/card';
import 'common/ajax-event';
import 'common/bootstrap-modal-hack';
import RewardPointNotify from 'app/common/reward-point-notify';
import { isMobileDevice } from 'common/utils';
import notify from 'common/notify';
import './alert';
import 'echo-js';

echo.init();

let rpn = new RewardPointNotify();
rpn.display();

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

if (app.scheduleCrontab) {
  $.post(app.scheduleCrontab);
}

$('i.hover-spin').mouseenter(function() {
  $(this).addClass('md-spin');
}).mouseleave(function() {
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
  $('body').on('mouseenter', 'li.nav-hover', function(event) {
    $(this).addClass('open');
  }).on('mouseleave', 'li.nav-hover', function(event) {
    $(this).removeClass('open');
  });
} else {
  $('li.nav-hover >a').attr('data-toggle', 'dropdown');
}

$('.js-search').focus(function() {
  $(this).prop('placeholder', '').addClass('active');
}).blur(function() {
  $(this).prop('placeholder', Translator.trans('site.search_hint')).removeClass('active');
});

$('select[name=\'language\']').change(function() {
  Cookies.set('locale', $('select[name=language]').val(), { 'path': '/' });
  $('select[name=\'language\']').parents('form').trigger('submit');
});

let eventPost = function($obj) {
  let postData = $obj.data();
  $.post($obj.data('url'), postData);
};

$('.event-report').each(function() {
  (function($obj) {
    eventPost($obj);
  })($(this));
});

$('body').on('event-report', function(e, name) {
  let $obj = $(name);
  eventPost($obj);
});

$('.modal').on('hidden.bs.modal', function() {
  let $modal = $(this);
  if ($modal.find('.modal-dialog').data('clear')) {
    $modal.empty();
  }
});

if ($('.js-hidden-exception').length > 0) {
  let replacedExceptionHtml = $('.js-hidden-exception').html().replace(/\r?\n/g, '');
  let exception = $.parseJSON(replacedExceptionHtml);
  notify('danger', exception.message);
  if ($('.js-hidden-exception-trace').length > 0) {
    exception.trace = $('.js-hidden-exception-trace').html();
  }
  console.log('exception', exception);
}

$.ajax('/online/sample');