define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
	var Validator = require('bootstrap.validator');

    exports.run = function() {
        require('./common').run();

        var editor = EditorFactory.create('#thread_content', 'simple', {extraFileUploadParams:{group:'course'}});

        var validator = new Validator({
            element: '#thread-form',
        });

        validator.addItem({
            element: '[name="thread[title]"]',
            required: true
        });

        validator.addItem({
            element: '[name="thread[content]"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});