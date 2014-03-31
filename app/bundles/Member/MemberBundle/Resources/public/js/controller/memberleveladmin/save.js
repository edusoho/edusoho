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

        $form.find('[name="monthType"]:checked').trigger('change');

        $form.find('[name="yearType"]:checked').trigger('change');

        $form.on('change', '[name="monthType"]', function(e) {
            if ($(this).is(':checked')) {
                validator.addItem({
                    element: '[name="monthPrice"]',
                    required: true,
                    rule:'currency arithmetic_number'            
                });
            } else {
                validator.removeItem('[name="monthPrice"]');
            };
        });

        $form.on('change', '[name="yearType"]', function(e) {
            if ($(this).is(':checked')) {
                validator.addItem({
                    element: '[name="yearPrice"]',
                    required: true,
                    rule:'currency arithmetic_number'            
                });
            } else {
                validator.removeItem('[name="yearPrice"]');
            };
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        var editor = EditorFactory.create('#memberlevel-content-field', 'standard', {extraFileUploadParams:{}, height: '100px'});
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });
        
    };

});