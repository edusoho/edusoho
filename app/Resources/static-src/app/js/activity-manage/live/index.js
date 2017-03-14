import { initEditor } from '../editor'

class Live {
  constructor(props) {
    this._init();
    this.validator2 = null;
  }
  _init() {
    this.initStep2Form();
  }

  initStep2Form() {
    let $step2_form = $("#step2-form");
    this.validator2 = $step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
        },
        startTime: {
          required: true,
          DateAndTime: true,
          after_now: true,
        },
        length: {
          required: true,
          digits: true,
          max: 300,
          min: 1
        },
        remark: {
          maxlength: 1000
        },
      },
    });
    initEditor($('[name="remark"]'), this.validator2);
   $step2_form.data('validator', this.validator2);
    this.dateTimePicker(this.validator2);
  }

  dateTimePicker(validator) {
    console.log(validator);
    let $starttime = $('#startTime');
    $starttime.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: "zh",
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 100 * 1000)
    }).on('hide', () => {
      validator.form();
    })
    $starttime.datetimepicker('setStartDate', new Date());
  }
}

new Live();
