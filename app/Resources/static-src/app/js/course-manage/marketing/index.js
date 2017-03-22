class Marketing {
  constructor() {
    this.validator = null;
    this.init();
  }

  init() {
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');
    this.initValidator();
    this.taskPriceSetting();
    this.checkBoxChange();
    this.initDatetimepicker();
    this.setService();
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

      if ($item.hasClass('label-primary')) {
        $item.removeClass('label-primary').addClass('label-default');
        values.splice($values.indexOf($item.text()), 1);
      } else {
        $item.removeClass('label-default').addClass('label-primary');
        values.push($item.text());
      }

      $('#course_services').val(JSON.stringify(values));
    });
  }

  initDatetimepicker() {
    $('input[name="buyExpiryTime"]').datetimepicker({
      format: 'yyyy-mm-dd',
      language: "zh",
      minView: 2, //month
      autoclose: true,
    }).on('hide', () => {
      this.validator && this.validator.form();
    })
    this.updateDatetimepicker();
  }

  initValidator() {
    let $form = $('#course-marketing-form');
    $('.js-task-price-setting').perfectScrollbar();
    this.validator = $form.validate({
      rules: {
        originPrice: {
          required: function () {
            return $("[name=isFree]:checked").val() == 0;
          },
          positive_currency: function () {
            return $("[name=isFree]:checked").val() == 0;
          },
        },
        tryLookLength: {
          required: function () {
            return $("[name=tryLookable]:checked").val() == 1;
          },
          digits: true,
          min: 1,
          max: 10
        },
        tryLookLimit: {
          digits: true
        },
        buyExpiryTime: {
          required: function () {
            return $('input[name="enableBuyExpiryTime"]:checked').val() == 1;
          },
          after_now_date:true,
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
        buyExpiryTime: {
          required: '请选择有效的加入截止日期',
          date: '请选择有效的加入截止日期'
        },
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
    $('#course-submit').click((event) => {
      if (this.validator && this.validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

  updateDatetimepicker() {
    $('input[name="buyExpiryTime"]').datetimepicker('setStartDate', new Date(Date.now() + 86400 * 1000));
    $('input[name="buyExpiryTime"]').datetimepicker('setEndDate', new Date(Date.now() + 86400 * 365 * 10 * 1000));
  }

  checkBoxChange() {
    $('input[name="buyable"]').on('change', function (event) {
      if ($('input[name="buyable"]:checked').val() == 0) {
         $('.js-course-add-close-show').removeClass('hidden');
         $('.js-course-add-open-show').addClass('hidden');
      } else {
        $('.js-course-add-close-show').addClass('hidden');
         $('.js-course-add-open-show').removeClass('hidden');
      }
    });

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

    $('input[name="isFree"]').on('change', function (event) {
      if ($('input[name="isFree"]:checked').val() == 0) {
        $('.js-is-free').removeClass('hidden');
      } else {
        $('.js-is-free').addClass('hidden');
      }
    });
    $('input[name="tryLookable"]').on('change', function (event) {
      if ($('input[name="tryLookable"]:checked').val() == 1) {
        $('.js-enable-try-look').removeClass('hidden');
      } else {
        $('.js-enable-try-look').addClass('hidden');
      }
    });
    $('input[name="enableBuyExpiryTime"]').on('change', (event) => {
      if ($('input[name="enableBuyExpiryTime"]:checked').val() == 0) {
        $('#buyExpiryTime').addClass('hidden');
      } else {

        $('#buyExpiryTime').removeClass('hidden');
        this.updateDatetimepicker();
      }
    });

    $('input[name="showServices"]').on('change', (event) => {
      if($('input[name="showServices"]:checked').val() == 1){
        $('.js-services').removeClass('hidden');
      }else{
        $('.js-services').addClass('hidden');
      }
    });
  }

  taskPriceSetting() {
    $('.js-task-price-setting').on('click', 'li', function (event) {
      let $li = $(this).toggleClass('open');
      let $input = $li.find('input');
      $input.prop("checked", !$input.is(":checked"))
    });

    $('.js-task-price-setting').on('click', 'input', function (event) {
      event.stopPropagation();
      let $input = $(this);
      $input.closest('li').toggleClass('open');
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
}

new Marketing();