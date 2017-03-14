class Creator {
  constructor() {
    this.init();
  }

  init() {
    this.initValidator();
    this.checkBoxChange();
  }

  initValidator() {
    let $form = $("#course-create-form");
    let validator = $form.validate({
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        title: {
          required: true,
          trim: true,
        },
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          digits: true,
          max_year: true
        },
        expiryStartDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          date: true,
          after_now_date: true,
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
        title: Translator.trans('请输入教学计划课程标题'),
        expiryStartDate: {
          required: Translator.trans('请输入开始日期'),
          before_date: Translator.trans('开始日期应早于结束日期')
        },
        expiryEndDate: {
          required: Translator.trans('请输入结束日期'),
          after_date: Translator.trans('结束日期应晚于开始日期')
        }
      }
    });

    $('#course-submit').click(function (evt) {
      if (validator.form()) {
        $(evt.currentTarget).button('loading');
        $form.submit();
      }
    });

    this.initDatePicker('#expiryStartDate',validator);
    this.initDatePicker('#expiryEndDate',validator);
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

    $('input[name="learnMode"]').on('change', function (event) {
      if ($('input[name="learnMode"]:checked').val() == 'freeMode') {
        $('#learnLockModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnFreeModeHelp').removeClass('hidden');
      } else {
        $('#learnFreeModeHelp').removeClass('hidden').addClass('hidden');
        $('#learnLockModeHelp').removeClass('hidden');
      }
    });
  }

  initDatePicker($id,validator) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd',
      language: "zh",
      minView: 2, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      validator.form();
    })
    $picker.datetimepicker('setStartDate', new Date());
  }
}

new Creator();
