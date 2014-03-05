define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#settings-avatar-form'
        });

        validator.addItem({
            element: '[name="form[avatar]"]',
            required: true,
            rule: 'maxsize_image',
            requiredErrorMessage: '请选择要上传的头像文件。'
        });

        $('.use-partner-avatar').on('click', function(){
            var goto = $(this).data('goto');
            $.post($(this).data('url'), function(){
                window.location.href = goto;
            });
        });

    };

});