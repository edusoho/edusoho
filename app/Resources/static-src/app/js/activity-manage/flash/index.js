import FileChooser from '../../file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';

class Flash {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
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
    let validator = $form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
        },
        mediaId: 'required',
      },
      messages: {
        mediaId: {
          required: '请上传或选择%display%'
        }
      }
    });

    $form.data('validator', validator);
  }

  initStep3Form() {
    let $step3_form = $("#step3-form");

    let validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        finishDetail: {
          required: true,
          positive_integer: true,
          max: 300,
        },
      },
      messages: {
        finishDetail: {
          required: '请输入至少观看多少分钟',
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
    });
  }
}

new Flash();