import FileChooser from '../../file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';

export default class Document {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
    this.init();
  }

  init() {
    showChooserType(this.$mediaId)
    this.initStep2Form();
    this.initStep3Form();
    this.initFileChooser();
  }

  initStep2Form() {
    var $step2_form = $("#step2-form");
    var validator = $step2_form.data('validator');
    validator = $step2_form.validate({
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
          required: '请上传或选择%display%'
        }
      }
    });
  }

  initStep3Form() {
    let $step3_form = $("#step3-form");
    let validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
        },
        finishDetail: {
          required: true,
          positive_integer: true,
          max: 300,
          min: 1,
        },
      },
      messages: {
        finishDetail: {
          required: "请输入完成条件",
          digits: "完成条件必须为数字"
        }
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

    $('#condition-select').on('change', event => {
      let conditionsType = $(event.currentTarget).children('option:selected').val();

      let $conditionsDetail = $("#condition-group");
      if (conditionsType !== 'time') {
        $conditionsDetail.addClass('hidden');
      } else {
        onConditionTimeType();
      }
    });
  }
}