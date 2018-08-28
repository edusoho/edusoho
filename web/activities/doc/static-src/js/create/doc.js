import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui';

export default class Document {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
    this.init();
  }

  init() {
    showChooserType(this.$mediaId);
    this.initStep2Form();
    this.initFileChooser();
    this.initEvent();
  }

  initEvent() {
    window.ltc.on('getActivity', function(msg){
      let validator = $('#step2-form').data('validator');
      console.log(validator);
      if (validator && validator.form()) {
        window.ltc.emit('returnActivity', {valid:true,data:window.ltc.getFormSerializeObject($('#step2-form'))});
      }
    });
  }

  initStep2Form() {
    var $step2_form = $('#step2-form');
    var validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        mediaId: 'required',
      },
      messages: {
        mediaId: {
          required: Translator.trans('activity.document_manage.media_error_hint')
        }
      }
    });
    $step2_form.data('validator', validator);
  }

  initFileChooser() {
    let fileChooser = new FileChooser();

    fileChooser.on('select', (file) => {
      chooserUiClose();
      this.$mediaId.val(file.id);
      $('#step2-form').valid();

      $('[name="media"]').val(JSON.stringify(file));
    });
  }
}