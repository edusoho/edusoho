import DoTestBase from 'app/js/testpaper/widget/do-test-base';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import ActivityEmitter from 'app/js/activity/activity-emitter';

import {
  initScrollbar,
  testpaperCardFixed,
  testpaperCardLocation,
  initWatermark,
  onlyShowError
} from 'app/js/testpaper/widget/part';

class DoExercise extends DoTestBase {

  constructor($container) {
    super($container);
    this._init();
  }

  _init() {
    initScrollbar();
    initWatermark();
    testpaperCardFixed();
    testpaperCardLocation();
    onlyShowError();
  }

  _getSeq() {
    let seq = [];
    $('.js-testpaper-question,.js-testpaper-question-stem-material').each(function () {
      seq.push($(this).attr('id').replace(/[^0-9]/ig,''));
    });
    return seq;
  }

  _suspendSubmit(url) {
    let values = this._getAnswers();
    let attachments = this._getAttachments();
    let seq = this._getSeq();
    $.post(url,{data:values,seq:seq,usedTime:this.usedTime,attachments:attachments})
      .done(() => {})
      .error(function (response) {
        notify('error', response.error.message);
      });
  }

  _submitTest(url) {
    let values = this._getAnswers();
    let seq = this._getSeq();
    let emitter = new ActivityEmitter();
    let attachments = this._getAttachments();

    $.post(url,{data:values,seq:seq,usedTime:this.usedTime,attachments:attachments})
      .done((response) => {
        if (response.result) {
          emitter.emit('finish', {data: ''});
        }

        if (response.goto != ''){
          console.log(response.goto);
          window.location.href = response.goto;
        } else if (response.message != '') {
          notify('error', response.message);
        }
      })
      .error(function (response) {
        notify('error', response.error.message);
      });
  }
}

new DoExercise($('.js-task-testpaper-body'));
new AttachmentActions($('.js-task-testpaper-body'));


