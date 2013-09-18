define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('jquery.form');

    exports.run = function() {
        var $form = $("#course-material-form");

        validator = new Validator({
            element: $form,
            autoSubmit: false
        });

        validator.addItem({
            element: '#material-file-field',
            required: true,
            errormessageRequired: '请选择要上传的资料文件'
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $form.ajaxSubmit({
                beforeSubmit: function(arr, $form, options) {
                    $form.find('[type=submit]').button('uploading');
                },
                success: function(html, status, jqr, $form) {
                    Notify.success('资料上传成功！');
                    $form.find('[type=submit]').button('reset');
                    $("#material-list").append(html).show();
                    $form.find('.text-warning').hide();
                    $form.clearForm();
                },
                error: function(jqr, textStatus, errorThrown, $form) {
                    Notify.danger(jqr.responseJSON.error.message);
                    $form.find('[type=submit]').button('reset');
                    $form.clearForm();
                }
            });

        });

        $form.on('click', '.delete-btn', function(){
            var $btn = $(this);
            if (!confirm('真的要删除该资料吗？')) {
                return ;
            }

            $.post($btn.data('url'), function(){
                $btn.parents('.list-group-item').remove();
                Notify.success('资料已删除');
            });
        });


    };
});