import Swiper from 'swiper';
import Cookies from 'js-cookie';

import 'codeages-design';
import 'common/tabs-lavalamp';
import 'common/card';
import 'common/ajax-event';
import 'common/bootstrap-modal-hack';
import RewardPointNotify from 'app/common/reward-point-notify';
import {isMobileDevice} from 'common/utils';
import notify from 'common/notify';
import './alert';
import 'echo-js';
import 'app/common/katex-render';

echo.init();

let rpn = new RewardPointNotify();
rpn.display();

if ($('#rewardPointNotify').length > 0) {
  let message = $('#rewardPointNotify').text();
  if (message) {
    notify('success', decodeURIComponent(message));
  }
}

$(document).on('click', '.js-handleLearnOnMessage', function (event) {
  event.preventDefault();
  notify('danger', decodeURIComponent(Translator.trans('validate.course.closed')));
});

$(document).on('click', '.js-handleClassroomOnMessage', function (event) {
  event.preventDefault();
  notify('danger', decodeURIComponent(Translator.trans('validate.classroom.closed')));
});

$(document).on('click', '.js-handleExerciseOnMessage', function (event) {
  event.preventDefault();
  notify('danger', decodeURIComponent(Translator.trans('validate.exercise.closed')));
});

$(document).on('click', '.js-handleLearnContentOnMessage', function (event) {
  event.preventDefault();
  notify('danger', decodeURIComponent(Translator.trans('validate.learn_content.closed')));
});

$('[data-toggle="popover"]').popover({
  html: true
});

$('[data-toggle="tooltip"]').tooltip({
  html: true,
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
  Cookies.set('locale', $('select[name=language]').val(), {'path': '/'});
  $('select[name=\'language\']').parents('form').trigger('submit');
});

let eventPost = function ($obj) {
  let postData = $obj.data();
  $.post($obj.data('url'), postData);
};

$('.event-report').each(function () {
  (function ($obj) {
    eventPost($obj);
  })($(this));
});

$('body').on('event-report', function (e, name) {
  let $obj = $(name);
  eventPost($obj);
});

$('.modal').on('hidden.bs.modal', function () {
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

let pageQueryUrl = $('.js-advanced-paginator').data('url');
let currentPerPageCount = $('#currentPerPageCount').children('option:selected').val();
// 每页显示数量
$('#currentPerPageCount').on('change', function () {
  currentPerPageCount = $(this).val();
  window.location.href = pageQueryUrl + 'page=1&perpage=' + currentPerPageCount;
})

// 分页
$('.js-advanced-paginator a').on('click', function () {
  let page = $(this).data('page');
  window.location.href = pageQueryUrl + 'page=' + page + '&perpage=' + currentPerPageCount;
})

// 跳页
$('#jumpPage').on('blur', function () {
  let currentPage = $(this).data('currentPage');
  let lastPage = $(this).data('lastPage');
  let jumpPage = $(this).val();
  if (currentPage == jumpPage || jumpPage > lastPage) {
    $(this).val(currentPage);
  } else {
    window.location.href = pageQueryUrl + 'page=' + jumpPage + '&perpage=' + currentPerPageCount;
  }
});

const aiAgentToken = document.getElementById('aiAgentToken');
if (aiAgentToken) {
  const sdk = new AgentSDK({
    token: aiAgentToken.value,
    uiIframeSrc: '/static-dist/libs/agent-web-sdk/ui/index.html',
    signalServerUrl: 'wss://test-ai-signal.edusoho.cn/'
  });
  sdk.addShortcut("plan.create", {
    name: "制定学习计划",
    icon: "<svg width=\"16\" height=\"16\" viewBox=\"0 0 16 16\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
      "<path d=\"M1.66665 6.33301H14.3333V13.6663C14.3333 14.0345 14.0348 14.333 13.6666 14.333H2.33332C1.96513 14.333 1.66665 14.0345 1.66665 13.6663V6.33301Z\" stroke=\"#333333\" stroke-linejoin=\"round\"/>\n" +
      "<path d=\"M1.66665 3.33366C1.66665 2.96547 1.96513 2.66699 2.33332 2.66699H13.6666C14.0348 2.66699 14.3333 2.96547 14.3333 3.33366V6.33366H1.66665V3.33366Z\" stroke=\"#333333\" stroke-linejoin=\"round\"/>\n" +
      "<path d=\"M5.33335 10.333L7.33335 12.333L11.3334 8.33301\" stroke=\"#333333\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
      "<path d=\"M5.33335 1.66699V4.33366\" stroke=\"#333333\" stroke-linecap=\"round\"/>\n" +
      "<path d=\"M10.6666 1.66699V4.33366\" stroke=\"#333333\" stroke-linecap=\"round\"/>\n" +
      "</svg>",
    type: "Send",
    data: {
      content: "制定学习计划"
    }
  })
  sdk.setChatMetadata({
    workerUrl: document.getElementById('workerUrl').value,
    domainId: document.getElementById('aiTeacherDomain').value,
    courseId: document.getElementById('agentCourseId').value,
    courseName: document.getElementById('agentCourseName').value,
    lessonId: document.getElementById('agentLessonId')?.value,
    lessonName: document.getElementById('agentLessonName')?.value,
  })
  sdk.boot();
  window.agentSdk = sdk;
}