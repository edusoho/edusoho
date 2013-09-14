define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');

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
                }
            });

            return false;
        });

        $('.btn-recover-content').on('click', function() {
            var html = $(this).parents('tr').find('.data-role-content').text();
            $("#blockContent").val(html);
        });
    };

});