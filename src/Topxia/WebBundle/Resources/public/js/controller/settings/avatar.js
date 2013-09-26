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
            requiredErrorMessage: '请选择要上传的头像文件。'
        });

    };

});