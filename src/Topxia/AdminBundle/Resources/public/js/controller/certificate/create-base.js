define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('es-ckeditor');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');
  require('/bundles/topxiaadmin/js/controller/system/common');
  exports.run = function() {

    // group: 'default'
    let ckeditor = CKEDITOR.replace('description', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('[name="description"]').data('imageUploadUrl')
    });

    var validator = new Validator({
      element: '#certificate-form',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#create-certificate').button('loading').addClass('disabled');
        $('#certificate-form').submit();
      }
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

    validator.addItem({
      element: '[name="targetType"]',
      required: true,
    });

    validator.on('formValidate', function(elemetn, event) {
      ckeditor.updateElement();
    });

  };
});