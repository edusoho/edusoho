define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        var editor = CKEDITOR.replace('profile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
        });
        var uploader = new WebUploader({
            element: '#upload-picture-btn'
        });

        uploader.on('uploadSuccess', function(file, response) {
            var url = $("#upload-picture-btn").data("gotoUrl");
            $.get(url, function(html) {
                $("#modal").modal('show');
                $("#modal").html(html);
            })
        });

        var validator = new Validator({
            element: '#user-profile-form',
            autoSubmit: false,
            onFormValidate:function(){
                editor.updateElement();
            },
            onFormValidated: function(error) {
                if (error) {
                    return false;
                }
                $('#course-create-btn').button('submiting').addClass('disabled');
                $.post($('#user-profile-form').attr('action'), $('#user-profile-form').serialize(), function() {
                    Notify.success('保存成功');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                })
            }
        });
        
        validator.addItem({
            element: '[id="profile_avatar"]',
            required: true,
            errormessageRequired: '请上传用户头像'
        });

        validator.addItem({
            element: '[id="profile_title"]',
            rule: 'chinese_limit{max:24}',
            required: true
        });

        validator.addItem({
            element: '[id="profile_about"]',
            required: true
        });
    }
});