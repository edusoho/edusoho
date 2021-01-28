import { initEditor } from '../editor';

export default class Discuss {
  constructor(props) {
    this._init();
  }

  _init() {
    this._inItStep2form();
  }

  _inItStep2form() {
    var $step2_form = $('#step2-form');
    var validator = $step2_form.data('validator');
    validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        content: 'required',
      },
    });
    initEditor($('[name="content"]'),validator);
  }
}