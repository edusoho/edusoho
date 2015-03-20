define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {

        var $form = $("#site-form");
        var uploader = new Uploader({
            trigger: '#site-logo-upload',
            name: 'logo',
            action: $('#site-logo-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网站LOGO失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#site-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=logo]').val(response.path);
                $("#site-logo-remove").show();
                Notify.success('上传网站LOGO成功！');
            }
        });

        $("#site-logo-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
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

        var uploader1 = new Uploader({
            trigger: '#site-favicon-upload',
            name: 'favicon',
            action: $('#site-favicon-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'ico',
            error: function(file) {
                Notify.danger('上传网站浏览器图标失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#site-favicon-container").html('<img src="' + response.url + '" style="margin-bottom: 10px;">');
                $form.find('[name=favicon]').val(response.path);
                $("#site-favicon-remove").show();
                Notify.success('上传网站浏览器图标成功！');
            }
        });

        $("#site-favicon-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-favicon-container").html('');
                $form.find('[name=favicon]').val('');
                $btn.hide();
                Notify.success('删除网站浏览器图标成功！');
            }).error(function(){
                Notify.danger('删除网站浏览器图标失败！');
            });
        });

    };

});