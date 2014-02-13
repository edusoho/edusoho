define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#testpaper-form'
        });

        validator.addItem({
            element: '#testpaper-name-field',
            required: true
        });

        validator.addItem({
            element: '#testpaper-description-field',
            required: true,
            rule: 'maxlength{max:500}'
        });

        validator.addItem({
            element: '#testpaper-limitedTime-field',
            required: true,
            rule: 'integer'
        });

        var editor = EditorFactory.create('#testpaper-description-field', 'simple_noimage');
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});