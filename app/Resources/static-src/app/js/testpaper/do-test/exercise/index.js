import DoTestpaper from 'app/js/testpaper/do-test/do-test';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import ActivityEmitter from "app/js/activity/activity-emitter";

class DoExercise extends DoTestpaper {

  _getSeq() {
    let seq = [];
    $('.js-testpaper-question').each(function () {
      seq.push($(this).attr('id').replace(/[^0-9]/ig,""));
    });
    return seq;
  }

  _submitTest(url,toUrl='') {
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


