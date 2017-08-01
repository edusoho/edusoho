define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require("jquery.bootstrap-datetimepicker");
  require('common/validator-rules').inject(Validator);
  require('es-ckeditor');
  var WebUploader = require('edusoho.webuploader');

  exports.run = function() {
    var uploader = new WebUploader({
      element: '#upload-picture-btn'
    });

    uploader.on('uploadSuccess', function(file, response ) {
      var url = $("#upload-picture-btn").data("gotoUrl");

      $('#modal').modal('show');

      $.post(url, {}, function(data){
          $('#modal').html(data);
        }
      );
    });

    var editor = CKEDITOR.replace('about', {
      toolbar: 'Detail',
      filebrowserImageUploadUrl: $('#product_about').data('imageUploadUrl')
    });

    var validator = new Validator({
      element: '#product-form',
      failSilently: true,
      onFormValidated: function(error){
        if (error) {
          return false;
        }
        $('#product-create-btn').button('submitting').addClass('disabled');

      }
    });

    validator.on('formValidate', function(element, event) {
      editor.updateElement();
    });

    validator.addItem({
      element: '[name="title"]',
      required: true,
      rule: 'chinese_alphanumeric byte_maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="price"]',
      required: true,
      rule: 'integer min{min: 0} max{max: 100000}'
    });

  };

});