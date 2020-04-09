import ESInfiniteCachedScroll from 'common/es-infinite-cached-scroll';
import { isEmpty } from 'common/utils';

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
    let finalOptions = $.extend(this._getDefaultOptions(options), options);
    finalOptions.wrapDom = options.wrapTarget;
    finalOptions.pageSize = this._getPageSizeByMaxLessonsNumOfChapter(finalOptions)

    new ESInfiniteCachedScroll(finalOptions);

    if (this._displayAllImmediately) {
      this._destroyPaging();
    }
  }

  // 分页数根据课程的最大章中的数量来设定，小于25则设置为25
  _getPageSizeByMaxLessonsNumOfChapter(options) {
    let items = options.data;
    if (isEmpty(items)) {
      return;
    }
    let pageSize = 0
    let num = 0
    items.forEach(item => {
      if (options.context.isChapter(item)) {
        pageSize = num > pageSize ? num : pageSize
        num = 0;
      } else {
        num ++;
      }
    });

    return pageSize < 25 ? 25 : pageSize + 1
  }

  _getDefaultOptions(options) {
    const $hiddenCachedData = this._wrapTarget(options.wrapTarget, '.js-hidden-cached-data');
    const $hiddenCourseInfo = this._wrapTarget(options.wrapTarget, '.js-hidden-course-info');
    const $hiddenI18n = this._wrapTarget(options.wrapTarget, '.js-hidden-i18n');
    const $hiddenActivityMetas = this._wrapTarget(options.wrapTarget, '.js-hidden-activity-metas');
    const $hiddenCurrentTimestamp = this._wrapTarget(options.wrapTarget, '.js-hidden-current-timestamp');
    return {
      'data': this._toJson($hiddenCachedData.html()),

      'context': {
        'course': this._toJson($hiddenCourseInfo.html()),

        'i18n': this._toJson($hiddenI18n.html()),

        'metas': this._toJson($hiddenActivityMetas.html()),

        'currentTimeStamp': parseInt($hiddenCurrentTimestamp.html(), 10),

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
          return Translator.trans('course.chapter', { chapter_name: context.i18n.i18nChapterName, number: data.number, title: data.title, colon: (data.title ? ':' : '') });
        },

        'getUnitName': function(data, context) {
          return Translator.trans('course.unit', { part_name: context.i18n.i18nUnitName, number: data.number, title: data.title, colon: (data.title ? ':' : '') });
        },

        'getLessonName': function(data, context) {
          if (context.isItemDisplayedAsOptional(data, context)) {
            return data.title;
          } else {
            return Translator.trans('course.lesson', { part_name: context.i18n.i18nLessonName, number: context.getLessonNum(data, context), title: data.title });
          }
        },

        /*
         * 选修或未发布状态下，
         *   业务逻辑：课时上面显示选修或敬请期待（未发布需要显示敬请期待），任务不显示选修或敬请期待
         *   技术逻辑：
         *     单任务课时，课时本身不显示选修或敬请期待，任务显示选修或敬请期待（页面上的课时，实际上是任务，只是套了一些课时的属性）
         *     多任务课时，课时本身显示选修或敬请期待，任务不显示选修或敬请期待
         */
        'isItemDisplayedAsOptionalOrUnpublished': function(data, context) {
          return context.isItemDisplayedAsOptional(data, context) ||
            context.isItemDisplayedAsUnpublished(data, context);
        },

        /**
         * 见 isItemDisplayedAsOptionalOrPublished 描述
         */
        'isItemDisplayedAsOptional': function(data, context) {
          return '1' == data['isOptional'] && context.isLessonNode(data, context);
        },

        'isItemDisplayedAsUnpublished': function(data, context) {
          return !context.isPublished(data, context) && context.isLessonNode(data, context);
        },

        'isLessonNode': function(data, context) {
          return (data['itemType'] == 'task' && data['isSingleTaskLesson']) ||
            (data['itemType'] == 'lesson' && !data['isSingleTaskLesson']);
        },

        'getTaskName': function(data, context) {
          if (data.isSingleTaskLesson) {
            return ('1' == data['isOptional']) ? data.title : Translator.trans('course.lesson', { part_name: context.i18n.i18nLessonName, number: context.getLessonNum(data, context), title: data.title });
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
        },

        'getLessonNum': function(data, context) {
          let lessonNum = data.number;

          if ('1' == context.course.isHideUnpublish) {
            lessonNum = data.published_number;
          }
          return lessonNum;
        },
      },

      'dataTemplateNode': '.js-infinite-item-template'
    };
  }

  _wrapTarget($target, className) {
    const $dom = $target ? $target.find(className): $(className);
    return $dom;
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