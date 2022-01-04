define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('es-ckeditor');
  require('common/validator-rules').inject(Validator);
  exports.run = function() {

    // group: 'default'
    var ckeditor = CKEDITOR.replace('description', {
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
      rule: 'byte_maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="description"]',
      rule: 'byte_maxlength{max:1000}',
      errormessageByte_maxlength:Translator.trans('admin_v2.certificate.validate.description')
    });

    validator.addItem({
      element: '[name="targetId"]',
      required: true,
    });

    validator.addItem({
      element: '[name="templateId"]',
      required: true,
    });

    if ($('[name="code"]').length > 0) {
      validator.addItem({
        element: '[name="code"]',
        required: true,
        rule: 'maxlength{max:20} remote certificate_code',
        errormessageRemote: Translator.trans('admin_v2.certificate.code.exist')
      });

      validator.addItem({
        element: '[name="expiryDay"]',
        rule: 'deadline_check',
      });
    }

    Validator.addRule('certificate_code', function(options) {
      var value = $(options.element).val();
      return /^[a-zA-Z0-9-]+$/i.test(value);
    }, Translator.trans('admin_v2.certificate.code.check'));

    Validator.addRule('deadline_check', function(options) {
      var value = $(options.element).val();
      if (!value || ((/^\+?[0-9][0-9]*$/.test(value) && value < 6001 && value > 0))) {
        return true;
      }
      return false;
    }, Translator.trans('admin_v2.certificate.expiry_day.check'));

    validator.on('formValidate', function(elemetn, event) {
      ckeditor.updateElement();
    });

    $('.js-auto-issue').on('click', function (e) {
      var $input = $(this).find('.es-switch__input');
      var ToggleVal = $input.val() == $input.data('open') ? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
      if ($input.val() == 1) {
        $('.js-auto-send').removeClass('hidden');
        $('.js-close-auto').addClass('hidden');
      } else {
        $('.js-close-auto').removeClass('hidden');
        $('.js-auto-send').addClass('hidden');
      }
    });

    $('[name="targetType"]').change(function () {
      var value = $(this).val();
      $('.js-target-name').html('');
      $('.js-template-name').html('');
      $('[name="targetId"]').val('');
      $('[name="templateId"]').val('');
      var targetUrl = $('.js-target-select').data('url');
      var templateUrl = $('.js-template-select').data('url');
      if (value == 'course') {
        targetUrl = targetUrl.replace(/classroom/g, value);
        templateUrl = templateUrl.replace(/classroom/g, value);
      } else {
        targetUrl = targetUrl.replace(/course/g, value);
        templateUrl = templateUrl.replace(/course/g, value);
      }

      $('.js-target-select').data('url', targetUrl);
      $('.js-template-select').data('url', templateUrl);
    });

  };
});