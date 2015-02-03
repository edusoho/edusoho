define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
	var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = EditorFactory.create('#post_content', 'simple', {extraFileUploadParams:{group:'course'}});

        var validator = new Validator({
            element: '#post-thread-form',
        });

        validator.addItem({
            element: '[name="post[content]"]',
            required: true
        });

        Validator.query('#post-thread-form').on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});