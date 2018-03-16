import { chapterAnimate } from 'app/common/widget/chapter-animate';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import { Browser } from 'common/utils';
import ESInfiniteCachedScroll from 'common/es-infinite-cached-scroll';
import { buyBtn } from 'app/common/widget/btn-util';

new ESInfiniteCachedScroll({
  'data': $.parseJSON($('.js-hidden-data').html().replace(/[\r\n]/g, "")),

  'context': {
    'course': $.parseJSON($('.js-hidden-course-info').html().replace(/[\r\n]/g, "")),

    'i18n': $.parseJSON($('.js-hidden-i18n').html().replace(/[\r\n]/g, "")),

    'metas': $.parseJSON($('.js-hidden-activity-metas').html().replace(/[\r\n]/g, "")),

    'currentTimeStamp': parseInt($('.js-hidden-current-timestamp').html(), 10),

    'isChapter': function(data, context) {
      return 'chapter' == data.itemType;
    },

    'isUnit': function(data, context) {
      return 'unit' == data.itemType;
    },

    'isTask': function(data, context) {
      return 'task' == data.itemType;
    },

    'getChapterName': function(data, context) {
      return Translator.trans('course.chapter', { chapter_name: context.i18n.i18nChapterName, number: data.number, title: data.title });
    },

    'getUnitName': function(data, context) {
      return Translator.trans('course.unit', { part_name: context.i18n.i18nUnitName, number: data.number, title: data.title });
    },

    'getTaskName': function(data, context) {
      return Translator.trans('course.catalogue.task_status.task', { taskNumber: data.number, taskTitle: data.title });
    },

    'hasWatchLimitRemaining': function(data, context) {
      return data.watchLimitRemaining != '';
    },

    'taskClass': function(data, context) {
      let classNames = 'es-icon left-menu';
      if (context.isTaskLocked(data, context)) {
        classNames += ' es-icon-lock';
      } else if (data.result == '' || context.course.isMember == 'false') {
        classNames += ' es-icon-undone-check color-gray';
      } else if (data.resultStatus == 'start') {
        classNames += ' es-icon-doing color-primary';
      } else if (data.resultStatus == 'finish') {
        classNames += ' es-icon-iccheckcircleblack24px color-primary';
      }
      return classNames;
    },

    'isTaskLocked': function(data, context) {
      return context.course.isDefault == '0' && context.course.learnMode == 'lockMode' &&
        (data.lock == 'true' || !context.course.member);
    },

    'isPublished': function(data, context) {
      return 'published' == context.course.status && 'published' == data.status;
    },

    'isCloudVideo': function(data, context) {
      return 'video' == data.type && 'cloud' == data.fileStorage;
    },

    'getMetaIcon': function(data, context) {
      if (typeof context.metas[data.type] != 'undefined') {
        return context.metas[data.type]['icon'];
      }
      return '';
    },

    'getMetaName': function(data, context) {
      if (typeof context.metas[data.type] != 'undefined') {
        return context.metas[data.type]['name'];
      }
      return '';
    },

    'isLiveReplayGenerated': function(data, context) {
      return 'ungenerated' != data.replayStatus;
    },

    'isLive': function(data, context) {
      return 'live' == data.type;
    },

    'isLiveNotStarted': function(data, context) {
      return context.isLive(data, context) && context.currentTimeStamp < context.toInt(data.activityStartTime);
    },

    'isLiveStarting': function(data, context) {
      return context.isLive(data, context) && context.currentTimeStamp >= context.toInt(data.activityStartTime) &&
        context.currentTimeStamp <= context.toInt(data.activityEndTime);
    },

    'isLiveFinished': function(data, context) {
      return context.isLive(data, context) && context.currentTimeStamp > context.toInt(data.activityEndTime);
    },

    'toInt': function(timestampStr) {
      return parseInt(timestampStr, 10);
    }
  },

  'dataTemplateNode': '.js-infinite-item-template'
});

echo.init();
chapterAnimate();
initTaskLearnChart();
triggerMemberExpired();
remainTime();

if ($('.js-attachment-list').length > 0) {
  new AttachmentActions($('.js-attachment-list'));
}

buyBtn($('.js-buy-btn'));
buyBtn($('.js-task-buy-btn'));

function initTaskLearnChart() {
  let colorPrimary = $('.color-primary').css('color');
  let colorWarning = $('.color-warning').css('color');
  $('#freeprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: colorPrimary,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
    onStep: function(from, to, percent) {
      $('canvas').css('height', '146px');
      $('canvas').css('width', '146px');
      if (Math.round(percent) == 100) {
        $(this.el).addClass('done');
      }
      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
    }
  });

  $('#orderprogress-plan').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: colorWarning,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
  });

  let bg = $('#orderprogress-plan').length > 0 ? 'transparent' : '#ebebeb';

  $('#orderprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: bg,
    barColor: colorPrimary,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
    onStep: function(from, to, percent) {
      if (Math.round(percent) == 100) {
        $(this.el).addClass('done');
      }
      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
    }
  });
}

function triggerMemberExpired() {
  if ($('.member-expire').length) {
    $('.member-expire a').trigger('click');
  }
}

function remainTime() {
  var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
  if (remainTime >= 0) {
    var endtime = new Date(new Date().valueOf() + remainTime * 1000);
    $('#discount-endtime-countdown').countdown(endtime, function(event) {
      var $this = $(this).html(event.strftime(Translator.trans('course_set.show.count_down_format_hint')));
    }).on('finish.countdown', function() {
      $(this).html(Translator.trans('course_set.show.time_finish_hint'));
      setTimeout(function() {
        $.post(app.crontab, function() {
          window.location.reload();
        });
      }, 2000);
    });
  }
}

// 暂时去掉块状
// let orderLearnSwiper = null;
// $('.js-task-show-type').on('click', 'a', function() {
//     let $this = $(this).addClass('active');
//     $($this.data('list')).removeClass('hidden');
//     $($this.siblings('a').removeClass('active').data('list')).addClass('hidden');
//     if($this.data('type') == 'chart'&& !orderLearnSwiper) {
//       initSwiper();
//     }
// })
// 暂时去掉块状
// function initSwiper() {
//   orderLearnSwiper = new Swiper('.swiper-container',{
//     pagination: '.swiper-pager',
//     loop:true,
//     grabCursor: true,
//     paginationClickable: true
//   })
//   $('.arrow-left').on('click', function(e){
//     e.preventDefault()
//     orderLearnSwiper.swipePrev();
//   })
//   $('.arrow-right').on('click', function(e){
//     e.preventDefault()
//     orderLearnSwiper.swipeNext();
//   })
// }