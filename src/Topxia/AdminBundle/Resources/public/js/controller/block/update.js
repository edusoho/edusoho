define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    var Uploader = require('upload');

    exports.run = function() {
        var $form = $('#block-form');
        var $modal = $form.parents('.modal');
        var $table = $('#block-table');

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

        var uploader = new Uploader({
            trigger: '#block-insert-image',
            name: 'file',
            action: $('#block-insert-image').data('url'),
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传图片失败，请重试！')
            },
            success: function(response) {
                var html = '<img src="' + response.url + '">';
                $("#blockContent").val($("#blockContent").val() + '\n' + html);
                Notify.success('插入图片成功！');
            }
        });


        $('.btn-recover-content').on('click', function() {
            var html = $(this).parents('tr').find('.data-role-content').text();
            $("#blockContent").val(html);
        });
    };

});