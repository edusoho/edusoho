import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';
import notify from 'common/notify';
import Intro from './intro';
import Detail from 'app/js/courseset-manage/base/detail';
import { initTags } from 'app/js/courseset-manage/base/tag';

class CourseInfo {
  constructor() {
    if ($('#maxStudentNum-field').length > 0) {
      $.get($('#maxStudentNum-field').data('liveCapacityUrl')).done((liveCapacity) => {
        $('#maxStudentNum-field').data('liveCapacity', liveCapacity.capacity);
      });
    }
    this.initValidator();
    this.checkBoxChange();
    this.changeAudioMode();
    this.initDatetimepicker();
    this.initExpiryMode();
    this.setService();
    this.setIntroPosition();
  }

  setIntroPosition() {
    const space = 40;
    const introRight = $('.js-course-manage-info').offset().left + space;
    $('.js-plan-intro').css('right', `${introRight}px`);
  }

  setService() {
    $('.js-service-item').click(function (event) {
      let $item = $(event.currentTarget);
      let $values = $('#course_services').val();
      let values;
      if (!$values) {
        values = [];
      } else {
        values = JSON.parse($values);
      }

      if ($item.hasClass('service-primary-item')) {
        $item.removeClass('service-primary-item');
        values.splice(values.indexOf($item.data('code')), 1);
      } else {
        $item.addClass('service-primary-item');
        values.push($item.data('code'));
      }
      
      $('#course_services').val(JSON.stringify(values));
    });
  }

  initDatetimepicker() {
    $('input[name="buyExpiryTime"]').datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2, //month
      autoclose: true,
    }).on('hide', () => {
      this.validator && this.validator.form();
    });
    $('input[name="buyExpiryTime"]').datetimepicker('setStartDate', new Date(Date.now()));
    $('input[name="buyExpiryTime"]').datetimepicker('setEndDate', new Date(Date.now() + 86400 * 365 * 10 * 1000));

    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');
  }

  changeAudioMode() {
    $('#audio-modal-id').on('change', 'input[name=\'enableAudio\']', function(){
      let mode = $('#course-audio-mode').data('value');
      if (mode == 'notAllowed') {
        cd.message({ type: 'info', message: Translator.trans('course.audio.enable.biz.user') });
        $('[name=\'enableAudio\']')[1].checked = true;
        $('[name=\'enableAudio\']')[0].checked = false;
      }
    });
  }

  initValidator() {
    let $form = $('#course-info-form');
    this.validator = $form.validate({
      currentDom: '#course-submit',
      ajax: true,
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        title: {
          required: true,
          maxlength: 10,
          trim: true,
          course_title: true,
        },
        maxStudentNum: {
          required: true,
          live_capacity: true,
          positive_integer: true
        },
        subtitle: {
          maxlength: 30
        },
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() != 'date';
          },
          digits: true,
          max_year: true
        },
        originPrice: {
          required: true,
          positive_price: true,
          min: 0
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
        },
        summary: {
          ckeditor_maxlength: 10000,
        }
      },
      messages: {
        originPrice: Translator.trans('validate_old.positive_currency.message'),
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
      },
      submitSuccess: (data) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
      }
    });

    $.validator.addMethod(
      'before',
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() > value;
      },
      Translator.trans('course.manage.expiry_end_date_error_hint')
    );

    $.validator.addMethod(
      'after',
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() < value;
      },
      Translator.trans('course.manage.expiry_start_date_error_hint')
    );

    $.validator.addMethod('max_year', function (value, element) {
      return this.optional(element) || value < 100000;
    }, Translator.trans('course.manage.max_year_error_hint'));

    $.validator.addMethod('live_capacity', function (value, element) {
      const maxCapacity = parseInt($(element).data('liveCapacity'));
      if (value > maxCapacity) {
        const message = Translator.trans('course.manage.max_capacity_hint', { capacity: maxCapacity });
        $(element).parent().siblings('.js-course-rule').find('p').html(message);
      } else {
        $(element).parent().siblings('.js-course-rule').find('p').html('');
      }

      return true;
    });

    if ($('#tags').length) {
      initTags();
    }
    if ($('#courseset-summary-field').length) {
      new Detail('#courseset-summary-field');
    } else {
      this.saveForm();
    }
  }

  saveForm() {
    $('#course-submit').on('click', (event) => {
      if (this.validator.form()) {
        $('#course-info-form').submit();
      }
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
    }).on('hide', () => {
      this.validator && this.validator.element($picker);
    });
    $picker.datetimepicker('setStartDate', new Date());
  }


  checkBoxChange() {
    $('input[name="buyable"]').on('change',  (event)=> {
      if ($('input[name="buyable"]:checked').val() == 0) {
        $('.js-course-add-close-show').removeClass('hidden');
        $('.js-course-add-open-show').addClass('hidden');
      } else {
        $('.js-course-add-close-show').addClass('hidden');
        $('.js-course-add-open-show').removeClass('hidden');
      }
      this.initenableBuyExpiry();
    });

    $('input[name="deadlineType"]').on('change', (event) => {
      if ($('input[name="deadlineType"]:checked').val() == 'end_date') {
        $('#deadlineType-date').removeClass('hidden');
        $('#deadlineType-days').addClass('hidden');
      } else {
        $('#deadlineType-date').addClass('hidden');
        $('#deadlineType-days').removeClass('hidden');
      }
      this.initExpiryMode();
    });

    $('input[name="expiryMode"]').on('change', (event) => {
      if ($('input[name="expiryMode"]:checked').val() == 'date') {
        $('#expiry-days').removeClass('hidden').addClass('hidden');
        $('#expiry-date').removeClass('hidden');
        $('.js-course-manage-expiry-tip').removeClass('ml0');
      } else if ($('input[name="expiryMode"]:checked').val() == 'days') {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden');
        $('.js-course-manage-expiry-tip').removeClass('ml0');
      } else {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden').addClass('hidden');
        $(event.target).closest('.form-group').removeClass('has-error');
        $('.js-course-manage-expiry-tip').addClass('ml0');
      }
      this.initExpiryMode();
    });

    $('input[name="enableBuyExpiryTime"]').on('change', (event) => {
      if ($('input[name="enableBuyExpiryTime"]:checked').val() == 0) {
        $('#buyExpiryTime').addClass('hidden');
      } else {
        $('#buyExpiryTime').removeClass('hidden');
      }
      this.initenableBuyExpiry();
    });

    $('input[name="expiryDays"]').on('blur', (event) => {
      this.validator.element($(event.target));
    });

  }

  initExpiryMode() {
    let $deadline = $('[name="deadline"]');
    let $expiryDays = $('[name="expiryDays"]');
    let $expiryStartDate = $('[name="expiryStartDate"]');
    let $expiryEndDate = $('[name="expiryEndDate"]');
    let expiryMode = $('[name="expiryMode"]:checked').val();
    let $deadlineType = $('[name="deadlineType"]:checked');

    this.elementRemoveRules($deadline);
    this.elementRemoveRules($expiryDays);
    this.elementRemoveRules($expiryStartDate);
    this.elementRemoveRules($expiryEndDate);

    switch (expiryMode) {
    case 'days':
      if ($deadlineType.val() === 'end_date') {
        this.elementAddRules($deadline, this.getDeadlineEndDateRules());
        this.validator.element($deadline);
        return;
      }
      this.elementAddRules($expiryDays, this.getExpiryDaysRules());
      this.validator.element($expiryDays);
      break;
    case 'date':
      this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
      this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
      this.validator.element($expiryStartDate);
      this.validator.element($expiryEndDate);
      break;
    default:
      break;
    }
  }

  elementRemoveRules($element) {
    $element.rules('remove');
  }

  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  getExpiryDaysRules() {
    return {
      required: true,
      positive_integer: true,
      max_year: true,
      messages: {
        required: Translator.trans(Translator.trans('course.manage.expiry_days_error_hint'))
      }
    };
  }

  initenableBuyExpiry() {
    let $enableBuyExpiryTime = $('[name="enableBuyExpiryTime"]:checked');
    let $buyable = $('[name="buyable"]:checked');
    let $buyExpiryTime = $('[name="buyExpiryTime"]');
    if ($buyable.val() == 1 && $enableBuyExpiryTime.val() == 1) {
      this.elementAddRules($buyExpiryTime, this.getBuyExpiryTimeRules());
    }
    else {
      this.elementRemoveRules($buyExpiryTime);
    }
    this.validator.form();
  }

  getBuyExpiryTimeRules() {
    return {
      required: true,
      messages: {
        required: Translator.trans('course.manage.buy_expiry_time_required_error_hint')
      }
    };
  }

  getExpiryStartDateRules() {
    return {
      required: true,
      date: true,
      before_date: '#expiryEndDate',
      messages: {
        required: Translator.trans('course.manage.expiry_start_date_error_hint')
      }
    };
  }

  getExpiryEndDateRules() {
    return {
      required: true,
      date: true,
      after_date: '#expiryStartDate',
      messages: {
        required: Translator.trans('course.manage.expiry_end_date_error_hint')
      }
    };
  }

  getDeadlineEndDateRules() {
    return {
      required: true,
      date: true,
      messages: {
        required: Translator.trans('course.manage.deadline_end_date_error_hint')
      }
    };
  }
}

new CourseInfo();

setTimeout(function() {
  new Intro();
}, 500);