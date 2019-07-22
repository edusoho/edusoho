import {hiddenUnpublishTask, addLesson} from './../header-util';
import LessonIntro from './lesson-intro';
import BaseManage from './../BaseManage';
import {TaskListHeaderFixed} from 'app/js/course-manage/help';

class NormalManage extends BaseManage {
  constructor($container) {
    super($container);
    new LessonIntro();
  }

  _flushTaskNumber() {
    if (!this.$taskNumber) {
      this.$taskNumber = $('#task-num');
    }

    let num = $('.js-task-manage-item:not(.js-optional-task)').length;
    this.$taskNumber.text(num);
  }

  _triggerAsTaskNumUpdated(container) {
    let lessonBox = container.find('.js-lesson-box');
    let isMulTasks = lessonBox.find('.js-task-manage-item').length > 1;

    if (isMulTasks) {
      lessonBox.removeClass('hidden');
    } else {
      lessonBox.addClass('hidden');
    }

    this._triggerLessonIconAsTaskNumUpdated(container, isMulTasks);
  }

  _triggerLessonIconAsTaskNumUpdated(container, isMulTasks) {
  }

  sortablelist() {
    // 前台排序 章，课时，任务 的序号
    let sortableElements = ['.js-task-manage-lesson[show-num=1]', '.js-task-manage-chapter'];
    for (let j = 0; j < sortableElements.length; j++) {
      this._sortNumberByClassName(sortableElements[j]);
    }
    this._sortUnitNumber();
    this._sortTaskNumber();
  }

  _sortTaskNumber() {
    let num;
    this.$element.find('.js-lesson-box').each(function () {
      let $task = $(this).find('.js-task-manage-item');
      num = 0;
      $task.each(function () {
        $(this).find('.number').text(num++);
      });
    });
  }
}


new NormalManage('#sortable-list');

hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
