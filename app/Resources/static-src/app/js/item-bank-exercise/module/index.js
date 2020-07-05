import notify from 'common/notify';

class Module{
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#module-form');

    let validator = $form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 6,
          chinese_alphanumeric: true,
        }
      }
    });

    $('.js-submit-btn').click(function () {
      if (validator.form()) {
        $.post($form.attr('action'), $form.serialize(), function(response) {
          window.location.reload();
        }).error(function (response) {
          notify('danger', response.error.message);
        });
      }
    });

    $('.js-delete-module').click(function () {
      $.post($(this).data('url'), function(response) {
        window.location.reload();
      });
    });
  }
}

new Module();