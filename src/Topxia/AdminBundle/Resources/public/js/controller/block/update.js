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
                    Notify.danger(Translator.trans('上传图片失败，请重试！'))
                },
                success: function(response) {
                    response = $.parseJSON(response);
                    $(el).siblings('a').attr('href', response.url);
                    $(el).siblings('a').show();
                    $(el).siblings('input').val(response.url);
                    $(el).siblings('button').show();
                    Notify.success(Translator.trans('上传图片成功！'));
                }
            });

        });

        $form.find('.upload-img-del').each(function(index, el) {
            $(el).on('click', function(event) {
                $(this).siblings('span').html('');
                $(this).siblings('input').val('');
                $(this).hide();
                $(this).siblings('a').hide();
                Notify.success(Translator.trans('删除图片成功！'));
            });
        });

        $form.submit(function() {
            $('#block-update-btn').button('submiting').addClass('disabled');
            $.post($form.attr('action'), $form.serialize(), function(response) {
                if (response.status == 'ok') {
                    var $html = $(response.html);
                    if ($table.find('#' + $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success(Translator.trans('更新成功！'));
                    } else {
                        $table.find('tbody').prepend(response.html);
                        Notify.success(Translator.trans('提交成功!'));
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
                Notify.danger(Translator.trans('请先选择要上传的图片'));
                return false;
            }

            $uploadForm.ajaxSubmit({
                clearForm: true,
                dataType:'json',
                success: function(response){
                    var html = '<img src="' + response.url + '">';
                    $("#blockContent").val($("#blockContent").val() + '\n' + html);
                    Notify.success(Translator.trans('插入图片成功！'));
                },
                error: function(response) {
                    Notify.danger(Translator.trans('上传图片失败，请重试！'));
                }
            });

            return false;
        });

    };

});