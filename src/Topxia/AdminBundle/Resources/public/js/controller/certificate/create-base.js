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

    validator.addItem({
      element: '[name="targetType"]',
      required: true,
    });

    validator.on('formValidate', function(elemetn, event) {
      ckeditor.updateElement();
    });

  };
});