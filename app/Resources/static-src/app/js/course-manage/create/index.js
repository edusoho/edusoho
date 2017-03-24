import Intro from 'app/js/courseset-manage/intro';

class Creator {
  constructor() {
    this.init();
    // this.isInitIntro();
  }

  init() {
    $('[data-toggle="popover"]').popover({
      html: true,
    });
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
        deadline: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() === 'days' && $('input[name="deadlineType"]:checked').val() === 'end_date' ;
          },
          after_now_date: true,
        },
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() === 'days' && $('input[name="deadlineType"]:checked').val() === 'days' ;
          },
          positive_integer: () => {
            return $('input[name="expiryMode"]:checked').val() === 'days' && $('input[name="deadlineType"]:checked').val() === 'days' ;
          },
          max_year: true,
        },
        expiryStartDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() === 'date';
          },
          date: true,
          after_now_date: true,
          before_date: '#expiryEndDate'
        },
        expiryEndDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() === 'date';
          },
          date: true,
          after_date: '#expiryStartDate'
        }
      },
      messages: {
        title: Translator.trans('请输入教学计划课程标题'),
        expiryDays: {
          required: '请输入有效期天数'
        },
        deadline: {
          required: '请输入截至日期'
        },
        expiryStartDate: {
          required: '请输入开始日期'
        },
        expiryEndDate: {
          required: '请输入结束日期'
        }
      }
    });

    $('#course-submit').click( (evt)=> {
      if (validator.form()) {
        this.isInitIntro();
        $(evt.currentTarget).button('loading');
        $form.submit();
      }
    });

    this.initDatePicker('#expiryStartDate',validator);
    this.initDatePicker('#expiryEndDate',validator);
    this.initDatePicker('#deadline',validator);
  }

  isInitIntro() {
    let listLength = $('#courses-list-table').find('tbody tr').length;
    if(listLength == 1) {
      console.log(listLength);
      let intro = new Intro();
      intro.isSetCourseListCookies();
    }
   
  }

  checkBoxChange() {
    $('input[name="deadlineType"]').on('change', function (event) {
      if ($('input[name="deadlineType"]:checked').val() == 'end_date') {
         $('#deadlineType-date').removeClass('hidden');
         $('#deadlineType-days').addClass('hidden');
      } else {
        $('#deadlineType-date').addClass('hidden');
         $('#deadlineType-days').removeClass('hidden');
      }
    });

    $('input[name="expiryMode"]').on('change', function (event) {
      if ($('input[name="expiryMode"]:checked').val() == 'date') {
        $('#expiry-days').removeClass('hidden').addClass('hidden');
        $('#expiry-date').removeClass('hidden');
      } else if($('input[name="expiryMode"]:checked').val() == 'days') {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden');
      } else  {
        $('#expiry-date').removeClass('hidden').addClass('hidden');
        $('#expiry-days').removeClass('hidden').addClass('hidden');
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
