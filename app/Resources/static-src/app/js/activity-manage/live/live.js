import { initEditor } from '../editor';
export default class Live {
  constructor(props) {
    this.$startTime = $('#startTime');
    this._init();
  }

  _init() {
    this.initStep2Form();
    this._timePickerHide();
  }

  initStep2Form() {
    jQuery.validator.addMethod('show_overlap_time_error', function(value, element) {
      return this.optional( element ) || !$(element).data('showError');
    }, '所选时间已经有直播了，请换个时间');
    jQuery.validator.addMethod('show_server_time_error', function(value, element) {
      return this.optional( element ) || !$(element).data('showError');
    }, '所选时间不能小于服务器时间');
    let $step2_form = $('#step2-form');
    this.validator2 = $step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          open_live_course_title: true,
        },
        startTime: {
          required: true,
          DateAndTime: true,
          after_now:true,
          show_server_time_error: true,
        },
        length: {
          required: true,
          digits: true,
          max: 300,
          min: 1,
          show_overlap_time_error: true
        },
        remark: {
          maxlength: 1000
        },
      }
    });
    initEditor($('[name="remark"]'), this.validator2);
    $step2_form.data('validator', this.validator2);
    this.dateTimePicker(this.validator2);
    let that = this;
    $step2_form.find('#startTime').change(function () {
      that.checkOverlapTime($step2_form);
    });

    $step2_form.find('#length').change(function () {
      that.checkOverlapTime($step2_form);
    });
  }

  checkOverlapTime($step2_form) {
    if ($step2_form.find('#startTime').val() && $step2_form.find('#length').val()) {
      let params = {
        startTime: $step2_form.find('#startTime').val(),
        length: $step2_form.find('#length').val(),
        mediaType: 'live'
      };
      $.ajax({
        url: $step2_form.find('#length').data('url'),
        async: false,
        type: 'POST',
        data: params,
        dataType: 'json',
        success: function (resp) {
          let startTime = $step2_form.find('#startTime');
          let length = $step2_form.find('#length');
          startTime.data('showError', 0);
          length.data('showError', 0);
          if (resp.success === 0) {
            if (resp.error == 'activity.after_now_invalid') {
              startTime.data('showError', 1);
            } else {
              length.data('showError', 1);
            }
          }
        }
      });
    }
  }

  dateTimePicker(validator) {
    let $starttime = this.$startTime;
    $starttime.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      validator.form();
    });
    $starttime.datetimepicker('setStartDate', new Date());
  }

  _timePickerHide() {
    let $starttime = this.$startTime;
    parent.$('#modal', window.parent.document).on('afterNext',function(){
      $starttime.datetimepicker('hide');
    });
  }
}
