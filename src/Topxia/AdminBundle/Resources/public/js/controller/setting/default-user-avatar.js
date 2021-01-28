define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $defaultAvatar = $("[name=defaultAvatar]");

        var defaultAvatarUploader = new WebUploader({
            element: '#default-avatar-btn'
        });

        defaultAvatarUploader.on('uploadSuccess', function(file, response ) {
            var url = $("#default-avatar-btn").data("gotoUrl");
            Notify.success(Translator.trans('admin.setting.default_user_avatar.upload_success_hint！'), 1);
            document.location.href = url;
        });

        $("[name=avatar]").change(function(){
            $defaultAvatar.val($("[name=avatar]:checked").val());
        });

        if ($('[name=avatar]:checked').val() == 0){
            $('#avatar-class').hide();
        }
        if ($('[name=avatar]:checked').val() == 1){
            $('#system-avatar-class').hide();
        }

        $("[name=avatar]").on("click",function(){
            if($("[name=avatar]:checked").val()==0){
                $('#system-avatar-class').show();
                $('#avatar-class').hide();
            }
            if($("[name=avatar]:checked").val()==1){
                $('#system-avatar-class').hide();
                $('#avatar-class').show();
                defaultAvatarUploader.enable();
            }
        });

    }
});

