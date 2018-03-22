import ESInfiniteCachedScroll from 'common/es-infinite-cached-scroll';

class PagedCourseLesson {

  /**
   * @param options 
   * {
   *   'displayAllImmediately': false //默认为false, 如果为true, 则不做分页处理，立刻显示全部
   * }
   */
  constructor(options) {
    if (typeof options == 'undefined') {
      options = {};
    }
    this._displayAllImmediately = options['displayAllImmediately'] ? true : false;
  }

  init() {
    new ESInfiniteCachedScroll({
      'displayAllImmediately': this._displayAllImmediately,

      'data': $.parseJSON($('.js-hidden-data').html().replace(/[\r\n]/g, '')),

      'context': {
        'course': $.parseJSON($('.js-hidden-course-info').html().replace(/[\r\n]/g, '')),

        'i18n': $.parseJSON($('.js-hidden-i18n').html().replace(/[\r\n]/g, '')),

        'metas': $.parseJSON($('.js-hidden-activity-metas').html().replace(/[\r\n]/g, '')),

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
  }

  destroyPaging() {
    let removedClasses = [
      'js-infinite-item-template',
      'js-hidden-data',
      'js-hidden-course-info',
      'js-hidden-i18n',
      'js-hidden-activity-metas',
      'js-hidden-current-timestamp',
      'infinite-container',
      'js-down-loading-more'
    ];

    for (let i = 0; i < removedClasses.length; i++) {
      $('.' + removedClasses[i]).removeClass(removedClasses[i]);
    }
  }
}

export default PagedCourseLesson;