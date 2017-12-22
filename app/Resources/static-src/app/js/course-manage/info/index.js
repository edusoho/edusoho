import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';
import notify from 'common/notify';

class courseInfo {

  constructor() {
    this.init();
  }

  init() {
    if ($('#maxStudentNum-field').length > 0) {
      $.get($('#maxStudentNum-field').data('liveCapacityUrl')).done((liveCapacity) => {
        $('#maxStudentNum-field').data('liveCapacity', liveCapacity.capacity);
      })
    }
    this.initCkeidtor();
    this.initValidator();
    this.checkBoxChange();
    this.changeAudioMode();
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.renderMultiGroupComponent('course-goals', 'goals');
    this.renderMultiGroupComponent('intended-students', 'audiences');
  }

  initCkeidtor() {
    CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
    });
  }

  changeAudioMode() {
    $('#audio-modal-id').on('change', "input[name='enableAudio']", function(){
      let mode = $("#course-audio-mode").data('value');
      if (mode == 'notAllowed') {
        notify('info', Translator.trans('course.audio.enable.biz.user'));
        $("[name='enableAudio']")[1].checked = true;
        $("[name='enableAudio']")[0].checked = false;
      }
    });
  }

  renderMultiGroupComponent(elementId, name) {
    let datas = $('#' + elementId).data('init-value');
    ReactDOM.render(<MultiInput
      dataSource={datas}
      outputDataElement={name} />, document.getElementById(elementId));
  }

  initValidator() {
    let $form = $('#course-info-form');
    let validator = $form.validate({
      currentDom: '#course-submit',
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        title: {
          maxlength: 100,
          required: {
            depends: function () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          }
        },
        maxStudentNum: {
          required: true,
          live_capacity: true,
          positive_integer: true
        },
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() != 'date';
          },
          digits: true,
          max_year: true
        },
        expiryStartDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          date: true,
          before_date: '#expiryEndDate'
        },
        expiryEndDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          date: true,
          after_date: '#expiryStartDate'
        }
      },
      messages: {
        title: {
          require: Translator.trans('course.manage.title_required_error_hint')
        },
        maxStudentNum: {
          required: Translator.trans('course.manage.max_student_num_error_hint')
        },
        expiryDays: {
          required: Translator.trans('course.manage.deadline_end_date_error_hint'),
        },
        expiryStartDate: {
          required: Translator.trans('course.manage.expiry_start_date_error_hint'),
          before: Translator.trans('course.manage.expiry_days_error_hint')
        },
        expiryEndDate: {
          required: Translator.trans('course.manage.expiry_end_date_error_hint'),
          after: Translator.trans('course.manage.expiry_start_date_error_hint')
        }
      }
    });

    $.validator.addMethod(
      "before",
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() > value;
      },
      Translator.trans('course.manage.expiry_end_date_error_hint')
    );

    $.validator.addMethod(
      "after",
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() < value;
      },
      Translator.trans('course.manage.expiry_start_date_error_hint')
    );

    $('#course-submit').click(() => {
      if (validator.form()) {
        this.publishAddMessage();
        $form.submit();
      }
    });
  }

  publishAddMessage() {
    postal.publish({
      channel: "courseInfoMultiInput",
      topic: "addMultiInput",
    });
  }

  initDatePicker($id) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    });
    $picker.datetimepicker('setStartDate', new Date());
  }

  checkBoxChange() {
    $('input[name="expiryMode"]').on('change', function (event) {
      if ($('input[name="expiryMode"]:checked').val() == 'date') {
        $('#expiry-days').removeClass('hidden').addClass('hidden');
        $('#expiry-date').removeClass('hidden');
      } else {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden');
      }
    });
  }
}

new courseInfo();

jQuery.validator.addMethod("max_year", function (value, element) {
  return this.optional(element) || value < 100000;
}, Translator.trans("course.manage.max_year_error_hint"));

jQuery.validator.addMethod("live_capacity", function (value, element) {
  const maxCapacity = parseInt($(element).data('liveCapacity'));
  if (value > maxCapacity) {
    const message = Translator.trans('course.manage.max_capacity_hint', { capacity: maxCapacity });
    $(element).parent().siblings('.js-course-rule').find('p').html(message);
  } else {
    $(element).parent().siblings('.js-course-rule').find('p').html('');
  }

  return true;
});