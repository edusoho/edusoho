define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {
        var editor = EditorFactory.create('#homework-about-field', 'simple');

    };

});