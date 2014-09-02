define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = EditorFactory.create('#post-content-field', 'simple', {extraFileUploadParams:{group:'course'}});

        var validator = new Validator({
            element: '#thread-post-form',
        });

        validator.addItem({
            element: '#post-content-field',
            required: true
        });

        Validator.query('#thread-post-form').on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});