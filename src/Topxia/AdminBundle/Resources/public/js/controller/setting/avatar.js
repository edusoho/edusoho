define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {


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

      $defaultAvatar.val($("[name=avatar]:checked").val());
    });

    if ($('[name=avatar]:checked').val() == 0) $('#avatar-class').hide();
    if ($('[name=avatar]:checked').val() == 1) {

      $('#avatar-class').show();
      $('#system-avatar-class').hide();
    }

    $("[name=avatar]").on("click", function() {

      if ($("[name=avatar]:checked").val() == 0) {
        $('#system-avatar-class').show();
        $('#avatar-class').hide();
      }
      if ($("[name=avatar]:checked").val() == 1) {
        $('#system-avatar-class').hide();
        $('#avatar-class').show();
      }
    });


  };


});