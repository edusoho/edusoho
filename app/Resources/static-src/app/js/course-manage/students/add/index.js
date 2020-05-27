class StudentAdd {
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#student-add-form');
    let rules = {
      queryfield: {
        required: true,
        remote: {
          url: $('#student-nickname').data('url'),
          type: 'get',
          data: {
            'value': function () {
              return $('#student-nickname').val();
            }
          }
        }
      },
      price: {
        positive_price: true,
        max: parseFloat($('#buy-price').data('price')),
      }
    };

    let messages = {
      queryfield: {
        remote: Translator.trans('course_manage.student_create.field_required_error_hint')
      },
      price: {
        max: Translator.trans('course_manage.student_create.price_max_error_hint'),
      }
    };

    if ($('#min-price').length) {
      rules.price = Object.assign({
        required: true,
        min: parseFloat($('#min-price').data('price'))
      }, rules.price);
      messages.price = Object.assign({min: Translator.trans('course_manage.student_create.price_min_error_hint')}, messages.price);
    }

    let validator = $form.validate({
      onkeyup: false,
      currentDom: '#student-add-submit',
      rules: rules,
      messages: messages
    });

    $('#student-add-submit').click(function (event) {
      if (validator.form()) {
        $form.submit();
      }
    });
  }
}

new StudentAdd();
