import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui.js';
export default class Flash {
  constructor() {
    this.mediaId = $('#step2-form').data('mediaId');
    this.init();
    this.initEvent();
  }
  init() {
    showChooserType(this.mediaId);
    this.initStep2Form();
    this.initFileChooser();
  }

  initEvent() {
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator2.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator2.form() });
    });
  }

  initStep2Form() {
    let $form = $('#step2-form');
    this.validator2 = $form.validate({
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
          required: Translator.trans('activity.flash_manage.media_error_hint')
        }
      }
    });

    $form.data('validator', this.validator2);
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