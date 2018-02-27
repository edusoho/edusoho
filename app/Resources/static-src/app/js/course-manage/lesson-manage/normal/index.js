import {hiddenUnpublishTask, addLesson} from './../header-util';
import BaseManage from './../BaseManage';

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
}

new NormalManage('#sortable-list');

hiddenUnpublishTask();
addLesson();

