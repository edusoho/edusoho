define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('ckeditor');

    exports.run = function() {

        var $form = $("#mobile-form");
        var uploader = new Uploader({
            trigger: '#mobile-logo-upload',
            name: 'logo',
            action: $('#mobile-logo-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校LOGO失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=logo]').val(response.path);
                $("#mobile-logo-remove").show();
                Notify.success('上传网校LOGO成功！');
            }
        });

        $("#mobile-logo-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-logo-container").html('');
                $form.find('[name=logo]').val('');
                $btn.hide();
                Notify.success('删除网校LOGO成功！');
            }).error(function(){
                Notify.danger('删除网校LOGO失败！');
            });
        });

       group: 'default'
        CKEDITOR.replace('mobile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#mobile_about').data('imageUploadUrl')
        });

    };

});