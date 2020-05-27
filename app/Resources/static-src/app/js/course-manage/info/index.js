import Intro from './intro';
import Expiry from 'app/js/course-manage/expiry/expiry';
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
    this.setService();
    this.taskPriceSetting();
    this.setIntroPosition();
    this.initCkeditor();
    this.expiry = new Expiry();
  }

  setIntroPosition() {
    const space = 44;
    const introRight = $('.js-course-manage-info').offset().left + space;
    window.onload = () => {
      $('.js-plan-intro').css('right', `${introRight}px`).removeClass('hidden');
    };
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
    $('.js-task-price-setting-scroll ').perfectScrollbar();
    this.validator = $form.validate({
      currentDom: '#course-submit',
      ajax: true,
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {

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
        originPrice: {
          required: true,
          positive_price: true,
          min: parseFloat($('#js-course-info').data('minPrice')),
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
        watchLimit:{
          unsigned_integer:true,
          max:10000,
        },
        summary: {
          ckeditor_maxlength: 10000,
        },
      },
      messages: {
        originPrice: Translator.trans($('#js-course-info').data('hintMessage')),
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
        window.location.reload();
      }
    });

    if ($('.js-course-title').length) {
      $('.js-course-title').rules('add', {
        required: true,
        maxlength: 10,
        trim: true,
        course_title: true,
      });
      $('.js-course-subtitle').rules('add', {
        maxlength: 30
      });
    }
    if ($('.js-courseset-title').length) {
      $('.js-courseset-title').rules('add', {
        required: true,
        byte_maxlength: 200,
        trim: true,
        course_title: true,
      });
      $('.js-courseset-subtitle').rules('add', {
        maxlength: 50
      });
    }


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
      return this.optional(element) || value <= 7300;
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

    this.changeStudentNumTip();

    this.saveForm();
  }

  changeStudentNumTip() {
    const $maxField = $('#maxStudentNum-field');
    if ($maxField.length > 0) {
      $maxField.on('blur', () => {
        if (!this.validator.element($maxField)) {
          $maxField.parent().siblings('.js-course-rule').find('p').html('');
        }
      });
    }
  }

  initCkeditor() {
    const $summaryField = $('#courseset-summary-field');
    const summaryLength = $summaryField.length;
    const uploadUrl = $summaryField.data('imageUploadUrl');
    if (!summaryLength) {
      return;
    }
    let self = this;
    self.editor = CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: uploadUrl
    });

    self.editor.on('blur', () => {
      $summaryField.val(self.editor.getData());
    });
  }


  saveForm() {
    $('#course-submit').on('click', (event) => {
      this.expiry.commonExpiryMode();
      const $summaryField = $('#courseset-summary-field');
      if ($summaryField.length) {
        $summaryField.val(this.editor.getData());
      }
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

  taskPriceSetting() {
    const $priceItem = $('.js-task-price-setting');
    $priceItem.on('click', 'li', (event) => {
      const $li = $(event.currentTarget);
      $li.toggleClass('open');
      const $input = $li.find('input');
      $input.prop('checked', !$input.is(':checked'));
    });

    $priceItem.on('click', 'input', (event) => {
      event.stopPropagation();
      const $input = $(event.target);
      $input.closest('li').toggleClass('open');
    });
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

    $('input[name="enableBuyExpiryTime"]').on('change', (event) => {
      const $buyExpiryTime = $('#buyExpiryTime');
      if ($('input[name="enableBuyExpiryTime"]:checked').val() == 0) {
        $buyExpiryTime.addClass('hidden');
      } else {
        $buyExpiryTime.removeClass('hidden');
      }
      this.initenableBuyExpiry();
    });

    $('input[name="expiryDays"]').on('blur', (event) => {
      this.validator.element($(event.target));
    });

  }

  elementRemoveRules($element) {
    $element.rules('remove');
  }

  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  initenableBuyExpiry() {
    let $enableBuyExpiryTime = $('[name="enableBuyExpiryTime"]:checked');
    let $buyable = $('[name="buyable"]:checked');
    let $buyExpiryTime = $('[name="buyExpiryTime"]');
    if ($buyable.val() == 1 && $enableBuyExpiryTime.val() == 1) {
      this.elementAddRules($buyExpiryTime, this.getBuyExpiryTimeRules());
    } else {
      this.elementRemoveRules($buyExpiryTime);
      $enableBuyExpiryTime.closest('.form-group').removeClass('has-error');
      $buyExpiryTime.removeClass('form-control-error');
      $('.jq-validate-error').remove();
    }
  }

  getBuyExpiryTimeRules() {
    return {
      required: true,
      messages: {
        required: Translator.trans('course.manage.buy_expiry_time_required_error_hint')
      }
    };
  }
}

new CourseInfo();

setTimeout(function() {
  new Intro();
}, 500);