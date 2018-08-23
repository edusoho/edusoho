export default class Expiry {
  constructor() {
    this.init();
  }
  
  init() {
    this.checkBoxChange();
  }
  
  checkBoxChange() {
    // 截止日期，有效天数
    $('input[name="deadlineType"]').on('change', (event) => {
      const $date = $('#deadlineType-date');
      const $days = $('#deadlineType-days');
      const checkValue = $('input[name="deadlineType"]:checked').val();
      this.removeErrorTip(event);
      if (checkValue === 'end_date') {
        $date.removeClass('hidden');
        $days.addClass('hidden');
      } else {
        $date.addClass('hidden');
        $days.removeClass('hidden');
      }
      this.commonExpiryMode(true);
    });
  
    // 有效期的选项
    $('input[name="expiryMode"]').on('change', (event) => {
      const $expiryDays = $('#expiry-days');
      const $expiryDate = $('#expiry-date');
      const checkValue = $('input[name="expiryMode"]:checked').val();
      this.removeErrorTip(event);
      const $tip = $('.js-expiry-tip');
      if (checkValue === 'date') {
        $expiryDays.addClass('hidden');
        $expiryDate.removeClass('hidden');
        $tip.removeClass('ml0');
      } else if (checkValue === 'days') {
        $expiryDate.addClass('hidden');
        $expiryDays.removeClass('hidden');
        $tip.removeClass('ml0');
      } else {
        $expiryDate.addClass('hidden');
        $expiryDays.addClass('hidden');
        $tip.addClass('ml0');
      }
      this.commonExpiryMode(true);
    });
  }
  
  commonExpiryMode(flag) {
    let $deadline = $('[name="deadline"]');
    let $expiryDays = $('[name="expiryDays"]');
    let $expiryStartDate = $('[name="expiryStartDate"]');
    let $expiryEndDate = $('[name="expiryEndDate"]');
    let $deadlineType = $('[name="deadlineType"]:checked');
    let expiryMode = $('[name="expiryMode"]:checked').val();
    this.elementRemoveRules($deadline);
    this.elementRemoveRules($expiryDays);
    this.elementRemoveRules($expiryStartDate);
    this.elementRemoveRules($expiryEndDate);
  
    switch (expiryMode) {
    case 'days':
      if ($deadlineType.val() === 'end_date') {
        if (flag) {
          $deadline.on('focus', () => {
            this.elementAddRules($deadline, this.getDeadlineEndDateRules());
          });
        } else {
          this.elementAddRules($deadline, this.getDeadlineEndDateRules());
        }
        return;
      }
      if (flag) {
        $expiryDays.on('focus', () => {
          this.elementAddRules($expiryDays, this.getExpiryDaysRules());
        });
      } else {
        this.elementAddRules($expiryDays, this.getExpiryDaysRules());
      }
      break;
    case 'date':
      if (flag) {
        $expiryStartDate.on('focus', () => {
          this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
        });
        $expiryEndDate.on('focus', () => {
          this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
        });
      } else {
        this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
        this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
      }
      break;
    default:
      break;
    }
  }
  
  removeErrorTip(event) {
    const $targetItem = $(event.target).closest('.form-group');
    $targetItem.removeClass('has-error');
    $targetItem.find('.js-expiry-input').removeClass('form-control-error');
    $('.jq-validate-error').remove();
  }
  
  // 添加验证规则
  elementRemoveRules($element) {
    $element.rules('remove');
  }
  
  // 移除验证规则
  elementAddRules($element, options) {
    $element.rules('add', options);
  }
  
  
  // 有效期天数
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
  
  // 开始日期
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
  
  // 结束日期 
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
  
  // 截止日期
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