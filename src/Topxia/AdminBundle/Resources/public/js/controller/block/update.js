define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');

    exports.run = function() {
        var $form = $('#block-form');
        var $modal = $form.parents('.modal');
        var $table = $('#block-table');


        $form.find('.upload-img').each(function(index, el) {

            var uploader = new Uploader({
                trigger: $(el),
                name: 'file',
                action: $(el).data('url'),
                data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                accept: 'image/*',
                error: function(file) {
                    Notify.danger('上传图片失败，请重试！')
                },
                success: function(response) {
                    response = $.parseJSON(response);
                    $(el).siblings('span').html(response.url);
                    $(el).siblings('input').val(response.url);
                    Notify.success('上传图片成功！');
                }
            });

        });











        $form.submit(function() {
            $.post($form.attr('action'), $form.serialize(), function(response) {
                if (response.status == 'ok') {
                    var $html = $(response.html);
                    if ($table.find('#' + $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success('更新成功！');
                    } else {
                        $table.find('tbody').prepend(response.html);
                        Notify.success('提交成功!');
                    }
                    $modal.modal('hide');
                }
            }, 'json');
            return false;
        });


        $("#block-image-upload-form").submit(function(){
            var $uploadForm = $(this);

            var file = $uploadForm.find('[name=file]').val();
            if (!file) {
                Notify.danger('请先选择要上传的图片');
                return false;
            }

            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = '<img src="' + response.url + '">';
                    $("#blockContent").val($("#blockContent").val() + '\n' + html);
                    Notify.success('插入图片成功！');
                },
                error: function(response) {
                    Notify.danger('上传图片失败，请重试！');
                }
            });

            return false;
        });

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


        $('.btn-recover-content').on('click', function() {
            var html = $(this).parents('tr').find('.data-role-content').text();
            $("#blockContent").val(html);
        });
    };

});