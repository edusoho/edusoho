import Intro from 'app/js/courseset-manage/intro';

class Creator {
  constructor() {
    this.validator = null;
    this.init();
    // this.isInitIntro();
  }

  init() {
    $('[data-toggle="popover"]').popover({
      html: true,
    });
    this.initValidator();
    this.initExpiryMode();
    this.checkBoxChange();
  }

  initValidator() {
    let $form = $('#course-create-form');
    this.validator = $form.validate({
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        title: {
          required: true,
          trim: true
        }
      },
      messages: {
        title: Translator.trans('course.manage.title_required_error_hint'),
      }
    });

    $('#course-submit').click((evt) => {
      if (this.validator.form()) {
        this.isInitIntro();
        $(evt.currentTarget).button('loading');
        $form.submit();
      }
    });
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');
  }

  isInitIntro() {
    let listLength = $('#courses-list-table').find('tbody tr').length;
    if(listLength == 1) {
      let intro = new Intro();
      intro.isSetCourseListCookies();
    }
  }

  checkBoxChange() {
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
      } else if ($('input[name="expiryMode"]:checked').val() == 'days') {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden');
        $('input[name="deadlineType"][value="days"]').prop('checked', true);
      } else {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden').addClass('hidden');
      }
      this.initExpiryMode();
    });

    $('input[name="learnMode"]').on('change', (event) => {
      if ($('input[name="learnMode"]:checked').val() == 'freeMode') {
        $('#learnLockModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnFreeModeHelp').removeClass('hidden');
      } else {
        $('#learnFreeModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnLockModeHelp').removeClass('hidden');
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
      this.validator.form();
    });
    $picker.datetimepicker('setStartDate', new Date());
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
        this.validator.form();
        return;
      }
      this.elementAddRules($expiryDays, this.getExpiryDaysRules());
      this.validator.form();
      break;
    case 'date':
      this.elementAddRules($expiryStartDate, this.getExpiryStartDateRules());
      this.elementAddRules($expiryEndDate, this.getExpiryEndDateRules());
      this.validator.form();
      break;
    default:
      break;
    }
  }

  getExpiryEndDateRules() {
    return {
      required: true,
      date: true,
      after_date: '#expiryStartDate',
      messages: {
        required:Translator.trans('course.manage.expiry_end_date_error_hint')
      }
    };
  }

  getExpiryStartDateRules() {
    return {
      required: true,
      date: true,
      after_now_date: true,
      before_date: '#expiryEndDate',
      messages: {
        required: Translator.trans('course.manage.expiry_start_date_error_hint')
      }
    };
  }

  getExpiryDaysRules() {
    return {
      required: true,
      positive_integer: true,
      max_year: true,
      messages: {
        required: Translator.trans('course.manage.expiry_days_error_hint')
      }
    };
  }

  getDeadlineEndDateRules() {
    return {
      required: true,
      date: true,
      after_now_date: true,
      messages: {
        required: Translator.trans('course.manage.deadline_end_date_error_hint')
      }
    };
  }

  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  elementRemoveRules($element) {
    $element.rules('remove');
  }
}

new Creator();