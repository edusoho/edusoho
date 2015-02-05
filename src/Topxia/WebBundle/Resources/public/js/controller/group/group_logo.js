 define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        if($('#group-avatar-form').length>0){
            var validator = new Validator({
            element: '#group-avatar-form'
            });

            validator.addItem({
            element: '[name="form[avatar]"]',
            required: true,
            rule: 'maxsize_image',
            requiredErrorMessage: '请选择要上传的文件。'
            });

        }
    };

});

