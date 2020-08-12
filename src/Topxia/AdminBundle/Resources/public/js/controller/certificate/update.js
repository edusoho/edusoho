define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('es-ckeditor');
  require('common/validator-rules').inject(Validator);
  exports.run = function() {

    // group: 'default'
    let ckeditor = CKEDITOR.replace('description', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('[name="description"]').data('imageUploadUrl')
    });

    var $form = $('#certificate-form');

    var validator = new Validator({
      element: $form,
    });

    validator.addItem({
      element: '[name="name"]',
      required: true,
      rule: 'byte_maxlength{max:100}'
    });

    validator.addItem({
      element: '[name="description"]',
      rule: 'byte_maxlength{max:200}'
    });

    validator.on('formValidate', function(elemetn, event) {
      ckeditor.updateElement();
    });

    $('.js-auto-issue').on('click', function (e) {
      let $input = $(this).find('.es-switch__input');
      let ToggleVal = $input.val() == $input.data('open') ? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
      if ($input.val() == 1) {
        $('.js-auto-issue-setting').removeClass('hidden');
      } else {
        $('.js-auto-issue-setting').addClass('hidden');
      }
    });

  };
});