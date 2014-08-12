define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {

        var $form = $("#contact-setting-form");
        var uploader = new Uploader({
            trigger: '#contact-upload',
            name: 'contact',
            action: $('#contact-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传微信二维码失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#contact-container").html('<img src="' + response.url + '">');
                $form.find('[name=webchatURI]').val(response.path);
                Notify.success('上传微信二维码成功！');
            }
        });
    }
});