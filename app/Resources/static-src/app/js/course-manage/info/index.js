import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';

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
          require: Translator.trans('请输入教学计划课程标题')
        },
        maxStudentNum: {
          required: Translator.trans('请输入课程人数')
        },
        expiryDays: {
          required: Translator.trans('请输入学习有效期'),
        },
        expiryStartDate: {
          required: Translator.trans('请输入开始日期'),
          before: Translator.trans('开始日期应早于结束日期')
        },
        expiryEndDate: {
          required: Translator.trans('请输入结束日期'),
          after: Translator.trans('结束日期应晚于开始日期')
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
      Translator.trans('开始日期应早于结束日期')
    );

    $.validator.addMethod(
      "after",
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() < value;
      },
      Translator.trans('结束日期应晚于开始日期')
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
      language: "zh",
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
}, "有效期最大值不能超过99,999天");

jQuery.validator.addMethod("live_capacity", function (value, element) {
  const maxCapacity = parseInt($(element).data('liveCapacity'));
  if (value > maxCapacity) {
    const message = Translator.trans('网校可支持最多%capacity%人同时参加直播，您可以设置一个更大的数值，但届时有可能会导致满额后其他学员无法进入直播。', { capacity: maxCapacity });
    $(element).parent().siblings('.js-course-rule').find('p').html(message);
  } else {
    $(element).parent().siblings('.js-course-rule').find('p').html('');
  }

  return true;
});


