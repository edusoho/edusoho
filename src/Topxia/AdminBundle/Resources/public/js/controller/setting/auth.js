define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('jquery.sortable');
  require('ckeditor');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {

    // group: 'default'
    CKEDITOR.replace('user_terms_body', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('#user_terms_body').data('imageUploadUrl')
    });

    $(".register-list").sortable({
      'distance': 20
    });

    $("#show-register-list").hide();

    $("#hide-list-btn").on("click", function() {
      $("#show-register-list").hide();
      $("#show-list").show();
    });

    $("#show-list-btn").on("click", function() {
      $("#show-register-list").show();
      $("#show-list").hide();
    });

    $("input[name=register_protective]").change(function() {

      var type = $('input[name=register_protective]:checked').val();

      $('.register-help').hide();

      $('.' + type).show();

    });

    var validator = new Validator({
      element: '#auth-form'
    });

    validator.addItem({
      element: '[name="user_name"]',
      required: true
    });

  };


});