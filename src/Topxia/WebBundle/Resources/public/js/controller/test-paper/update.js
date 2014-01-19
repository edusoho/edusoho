define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var EditorFactory = require('common/kindeditor-factory');
    require('common/validator-rules').inject(Validator);

    var CreateBase = require('./util/create-base');
    
    exports.run = function() {

        var validator = new Validator({
            element: '#test-update-form',
        });

        CreateBase.initValidator(validator);

        var editor = EditorFactory.create('#test-description-field', 'simple_noimage');
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };

});