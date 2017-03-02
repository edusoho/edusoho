class StudentAdd {
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#student-add-form');
    let validator = $form.validate({
      onkeyup: false,
      currentDom:'#student-add-submit',
      rules: {
        queryfield: {
          required: true,
          remote: {
            url: $('#student-nickname').data('url'),
            type: 'get',
            data: {
              'value': function() {
                return $('#student-nickname').val();
              }
            }
          }
        }
      },
      messages: {
        queryfield: {
          remote: Translator.trans('请输入学员邮箱/手机号/用户名')
        }
      }
    });

    $('#student-add-submit').click(function(event) {
      if(validator.form()) {
        $form.submit();
      }
    });
  }
}

new StudentAdd();
