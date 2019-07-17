import {hiddenUnpublishTask, addLesson} from './../header-util';
import LessonIntro from './lesson-intro';
import BaseManage from './../BaseManage';
import { TaskListHeaderFixed } from 'app/js/course-manage/help';
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
}

new NormalManage('#sortable-list');

hiddenUnpublishTask();
addLesson();
TaskListHeaderFixed();
