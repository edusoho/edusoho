import ESInfiniteCachedScroll from 'common/es-infinite-cached-scroll';

class PagedCourseLesson {

  /**
   * @param options 
   * {
   *   'displayAllImmediately': false //默认为false, 如果为true, 则不做分页处理，立刻显示全部
   *   'afterFirstLoad': function() {},
   *   'pageSize': 25,
   * }
   */
  constructor(options) {
    if (typeof options == 'undefined') {
      options = {};
    }
    this._init(options);
  }

  _init(options) {
    let finalOptions = $.extend(this._getDefaultOptions(), options);
    new ESInfiniteCachedScroll(finalOptions);

    if (this._displayAllImmediately) {
      this._destroyPaging();
    }
  }

  _getDefaultOptions() {
    return {
      'data': this._toJson($('.js-hidden-cached-data').html()),

      'context': {
        'course': this._toJson($('.js-hidden-course-info').html()),

        'i18n': this._toJson($('.js-hidden-i18n').html()),

        'metas': this._toJson($('.js-hidden-activity-metas').html()),

        'currentTimeStamp': parseInt($('.js-hidden-current-timestamp').html(), 10),

        'isChapter': function(data, context) {
          return 'chapter' == data.itemType;
        },

        'isUnit': function(data, context) {
          return 'unit' == data.itemType;
        },

        'isLesson': function(data, context) {
          return 'lesson' == data.itemType;
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

        'getLessonName': function(data, context) {
          if (data['isOptional']) {
            return data.title;
          } else {
            return Translator.trans('course.lesson', { part_name: context.i18n.i18nLessonName, number: data.number, title: data.title });
          }
        },

        /*
         * 如果是多任务课时，任务上不显示选修，因为课时上已经显示选修了
         * 单任务课时，则直接在任务上显示选修（单任务课时时，只显示一条记录，以任务记录为主，附加了课时信息）
         */
        'isTreatedAsTaskOptional': function(data, context) {
          return data['isOptional'] && (data['itemType'] != 'task' || data['isSingleTaskLesson']);
        },

        'getTaskName': function(data, context) {
          if (data.isSingleTaskLesson) {
            return Translator.trans('course.lesson', { part_name: context.i18n.i18nLessonName, number: data.number, title: data.title });
          } else {
            return Translator.trans('course.catalogue.task_status.task', { taskName: context.i18n.i18nTaskName, taskNumber: data.number, taskTitle: data.title });
          }
        },

        'hasWatchLimitRemaining': function(data, context) {
          return data.watchLimitRemaining !== false;
        },

        /** 课时详情页中，当前课时会高亮 */
        'highlightTaskClass': function(data, context) {
          return data.taskId == context.course.currentTaskId ? 'active' : '';
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

        'lessonContainerClass': function(data, context) {
          let containerClass = 'color-gray bg-gray-lighter';
          if (context.isTask(data, context)) {
            return data.isSingleTaskLesson ? containerClass : '';
          } else if (context.isLesson(data, context)) {
            return containerClass;
          }
        },

        'isTaskLocked': function(data, context) {
          if (context.course.isMember) {
            return context.course.learnMode == 'lockMode' && data.lock;
          } else {
            return context.course.learnMode == 'lockMode';
          }
        },

        'isPublished': function(data, context) {
          return 'published' == data.status;
        },

        'isPublishedTaskUnlocked': function(data, context) {
          return context.isPublished(data, context) && !context.isTaskLocked(data, context);
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
    };
  }

  _destroyPaging() {
    let removedClasses = [
      'js-infinite-item-template',
      'js-hidden-cached-data',
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

  /*
   * 将字符串转化为json，转换前先去除换行，如果字符串为空，则转化为 {}
   */
  _toJson(str) {
    let json = {};
    if (str) {
      json = $.parseJSON(str.replace(/[\r\n\t]/g, ''));
    }
    return json;
  }
}

export default PagedCourseLesson;