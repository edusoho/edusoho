import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui';

export default class Document {
  constructor() {
    this.mediaId = $('#step2-form').data('mediaId');
    this.init();
  }

  init() {
    showChooserType(this.mediaId);
    this.initStep2Form();
    this.initFileChooser();
    this.initEvent();
  }

  initEvent() {
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator.form() });
    });
  }

  initStep2Form() {
    var $step2_form = $('#step2-form');
    this.validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        media: 'required',
      },
      messages: {
        media: {
          required: Translator.trans('activity.document_manage.media_error_hint')
        }
      }
    });
  }

  initFileChooser() {
    let fileChooser = new FileChooser();

    fileChooser.on('select', (file) => {
      chooserUiClose();
      $('[name="media"]').val(JSON.stringify(file));
      $('#step2-form').valid();
    });
  }
}