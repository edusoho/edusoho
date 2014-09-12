define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {

        var $form = $("#school-form");
        var uploader = new Uploader({
            trigger: '#school-homepage-upload',
            name: 'homepagePicture',
            action: $('#school-homepage-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传图片失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#school-homepage-container").html('<img src="' + response.url + '?'+(new Date()).getTime()+'" style="max-width:400px;">');
                $form.find('[name=homepagePicture]').val(response.path);
                $("#school-homepage-remove").show();
                Notify.success('上传学校主页成功！');
            }
        });

       $("#school-homepage-remove").on('click', function(){
        if (!confirm('确认要删除吗？')) return false;
        var $btn = $(this);
        $.post($btn.data('url'), function(){
            $("#school-homepage-container").html('');
            $form.find('[name=homepagePicture]').val('');
            $btn.hide();
            Notify.success('删除学校主页成功！');
        }).error(function(){
            Notify.danger('删除学校主页失败！');
        });
    });
      
    }
});