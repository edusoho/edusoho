import {addLesson, hiddenUnpublishTask} from './../header-util';
import BaseManage from './../BaseManage';
import {TaskListHeaderFixed} from 'app/js/course-manage/help';

class NormalManage extends BaseManage {
  constructor($container) {
    super($container);
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
}

new NormalManage('#sortable-list');

hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
