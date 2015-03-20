define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {

		require('./header').run();

		var $form = $("#course-picture-form");

		validator = new Validator({
			element: $form
		});

		validator.addItem({
			element: '#course-picture-field',
			required: true,
			rule: 'maxsize_image',
			errormessageRequired: '请选择要上传的课程图片'
		});

		

	};

});