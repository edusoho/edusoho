import FileChooser from '../../file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';

export default class Flash {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
    this.validator2 = null;
    this.init();
  }
  init() {
    showChooserType(this.$mediaId);
    this.initStep2Form();
    this.initStep3Form();
    this.initFileChooser();
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
        mediaId: 'required',
      },
      messages: {
        mediaId: {
          required: Translator.trans('activity.flash_manage.media_error_hint')
        }
      }
    });

    $form.data('validator', this.validator2);
  }

  initStep3Form() {
    let $step3_form = $('#step3-form');

    let validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        finishDetail: {
          required: true,
          positive_integer: true,
          max: 300,
          min: 1,
        },
      },
      messages: {
        finishDetail: {
          required: Translator.trans('activity.flash_manage.finish_detail_required_error_hint'),
        },
      }
    });

    $step3_form.data('validator', validator);
  }

  initFileChooser() {
    let fileChooser = new FileChooser();
    fileChooser.on('select', (file) => {
      chooserUiClose();
      this.$mediaId.val(file.id);
      $('#step2-form').valid();
      $('[name="media"]').val(JSON.stringify(file));
      if(this.validator2) {
        this.validator2.form();
      }
    });
  }
}