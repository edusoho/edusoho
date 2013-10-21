define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {

        var $form = $("#site-form");
        var uploader = new Uploader({
            trigger: '#site-logo-upload',
            name: 'logo',
            action: $('#site-logo-upload').data('url'),
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网站LOGO失败，请重试！')
            },
            success: function(response) {
                response = eval("(" + response + ")");
                $("#site-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=logo]').val(response.path);
                $("#site-logo-remove").show();
                Notify.success('上传网站LOGO成功！');
            }
        });

        $("#site-logo-remove").on('click', function(){
            var $btn = $(this);

            $.post($btn.data('url'), function(){
                $("#site-logo-container").html('');
                $form.find('[name=logo]').val('');
                $btn.hide();
                Notify.success('删除网站LOGO成功！');
            }).error(function(){
                Notify.danger('删除网站LOGO失败！');
            });
        });

    };

});