 define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {

            var validator = new Validator({
            element: '#classroom-picture-form'
            });

            validator.addItem({
            element: '[name="picture"]',
            required: true,
            rule: 'maxsize_image',
            errormessageRequired: '请选择要上传的文件。'
            });

    };

});

