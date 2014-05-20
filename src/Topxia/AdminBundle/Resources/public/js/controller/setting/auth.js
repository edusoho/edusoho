define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = EditorFactory.create('#user_terms_body', 'simple');

            editor.sync();

    };

});