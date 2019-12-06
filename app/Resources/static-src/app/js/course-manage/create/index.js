import Expiry from 'app/js/course-manage/expiry/expiry';
class Creator {
  constructor() {
    this.validator = null;
    this.init();
    this.expiry = new Expiry();
  }

  init() {
    $('[data-toggle="popover"]').popover({
      html: true,
    });
    this.initValidator();
    this.expiryDaysBlur();
  }

  initValidator() {
    let $form = $('#course-create-form');
    this.validator = $form.validate({
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        title: {
          maxlength: 10,
          required: true,
          trim: true,
          course_title: true
        },
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() != 'date';
          },
          digits: true,
          max_year: true
        }
      }
    });

    $.validator.addMethod('max_year', function (value, element) {
      return this.optional(element) || value <= 7300;
    }, Translator.trans('course.manage.max_year_error_hint'));

    $('#course-submit').click((evt) => {
      this.expiry.commonExpiryMode();
      if (this.validator.form()) {
        $(evt.currentTarget).button('loading');
        $form.submit();
      }
    });
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');
  }


  expiryDaysBlur() {
    $('input[name="expiryDays"]').on('blur', (event) => {
      this.validator.element($(event.target));
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
}

new Creator();