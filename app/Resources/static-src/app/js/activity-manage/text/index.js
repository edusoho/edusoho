import { initEditor } from '../editor'

class Text {
  constructor(props) {
    this._init();
  }

  _init() {
    this._inItStep2form();
    this._inItStep3form();
    initEditor($('[name="content"]'));
  }

  _inItStep2form() {
    var $step2_form = $("#step2-form");
    var validator = $step2_form.data('validator');
    validator = $step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
        },
        content: 'required',
      },
    });
  }

  _inItStep3form() {
    var $step3_form = $("#step3-form");
    var validator = $step3_form.data('validator');
    validator = $step3_form.validate({
      onkeyup: false,
      rules: {
        'finishDetail': {
          required: true,
          digits: true,
          max: 300,
        },
      },
      messages: {
        finishDetail: {
          required: '请输入至少观看多少分钟',
        },
      }
    });
  }
}

new Text();
