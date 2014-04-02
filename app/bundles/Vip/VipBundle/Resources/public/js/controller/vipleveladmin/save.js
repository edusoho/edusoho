define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    var EditorFactory = require('common/kindeditor-factory');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function() {
		var $form = $('#memberlevel-form');
        var $table = $('#memberlevel-table');

		var validator = new Validator({
            element: $form
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        validator.addItem({
            element: '[name="monthPrice"]',
            required: true,
            rule:'currency arithmetic_number',
            display: '包月价格'           
        });

        validator.addItem({
            element: '[name="yearPrice"]',
            required: true,
            rule:'currency arithmetic_number',
            display: '包年价格'            
        });

        var editor = EditorFactory.create('#memberlevel-content-field', 'standard', {extraFileUploadParams:{}, height: '100px'});
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });
        
    };

});