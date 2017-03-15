class Marketing {
  constructor() {
    this.validator = null;
    this.init();
  }

  init() {
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

  updateDatetimepicker() {
    $('input[name="buyExpiryTime"]').datetimepicker('setStartDate', new Date(Date.now() + 86400 * 1000));
    $('input[name="buyExpiryTime"]').datetimepicker('setEndDate', new Date(Date.now() + 86400 * 365 * 10 * 1000));
  }

  checkBoxChange() {
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
        }
      },
      messages: {
        buyExpiryTime: {
          required: '请选择有效的购买截止日期',
          date: '请选择有效的购买截止日期'
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
}

new Marketing();