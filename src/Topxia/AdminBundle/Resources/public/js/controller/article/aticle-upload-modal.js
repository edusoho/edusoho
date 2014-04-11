define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');

    exports.run = function() {
        var validator = new Validator({
            element: '#user-avatar-form'
        });

        validator.addItem({
            element: '[name="form[avatar]"]',
            required: true,
            requiredErrorMessage: '请选择要上传的头像文件。'
        });

        $('.use-partner-avatar').on('click', function(){
            var goto = $(this).data('goto');
            $.post($(this).data('url'), function(){
                window.location.href = goto;
            });
        });

        $("#avatar-upload-btn").click(function() {

            var $form = $('#user-avatar-form');

            $form.ajaxSubmit({
                clearForm: true,
                success: function(html){
                    $('#modal').html(html);
                }
            });

        });

    };

});