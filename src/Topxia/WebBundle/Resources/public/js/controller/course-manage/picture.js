define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

	exports.run = function() {

		require('./header').run();

		var $form = $("#course-picture-form");

		validator = new Validator({
			element: $form
		});

		validator.addItem({
			element: '#course-picture-field',
			required: true,
			errormessageRequired: '请选择要上传的课程图片'
		});

	};

});