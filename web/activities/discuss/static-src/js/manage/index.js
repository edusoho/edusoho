import { initEditor } from 'app/js/activity-manage/editor.js';
export default class Discuss {
  constructor() {
    this._init();
  }

  _init() {
    this._inItStep2form();
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator.form(), data: this._serializeArray($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', {valid: this.validator.form()});
    });
  }

  _inItStep2form() {
    var $step2_form = $('#step2-form');
    this.validator = $step2_form.validate({
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
    initEditor($('[name="content"]'), this.validator);
  }
  
  _serializeArray($e) {
    let o = {};
    let a = $e.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  }
}

new Discuss();