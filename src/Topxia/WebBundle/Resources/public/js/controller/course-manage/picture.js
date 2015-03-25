define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	var WebUploader = require('../widget/web-uploader');

	exports.run = function() {

		require('./header').run();


		

		var uploader = new WebUploader({
			element: '#upload-picture-btn'
		});

		$('#upload-picture-btn').click(function(){
			uploader.upload();
		})

	};

});