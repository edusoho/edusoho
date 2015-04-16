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

    var $avatarForm = $("#avatar-form");

    avatarValidator = new Validator({
      element: $avatarForm
    })

    avatarValidator.addItem({
      element: '#avatar-field',
      required: true,
      rule: 'maxsize_image',
      errormessageRequired: '请选择要上传的默认头像文件'
    });


    var $defaultAvatar = $("[name=defaultAvatar]");

    $("[name=avatar]").change(function() {
      console.log(1);
      $defaultAvatar.val($("[name=avatar]:checked").val());
    });

    if ($('[name=avatar]:checked').val() == 0) $('#avatar-class').hide();
    if ($('[name=avatar]:checked').val() == 1) $('#system-avatar-class').hide();

    $("[name=avatar]").on("click", function() {
      console.log(1);
      if ($("[name=avatar]:checked").val() == 0) {
        $('#system-avatar-class').show();
        $('#avatar-class').hide();
      }
      if ($("[name=avatar]:checked").val() == 1) {
        $('#system-avatar-class').hide();
        $('#avatar-class').show();
      }
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