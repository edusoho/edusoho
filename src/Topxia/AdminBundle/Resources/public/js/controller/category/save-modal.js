define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
	exports.run = function() {
		var $form = $('#category-form');
		var $modal = $form.parents('.modal');

		var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $.post($form.attr('action'), $form.serialize(), function(response){
					if (response.status == 'ok') {
						window.location.reload();
					} else {
						var errorMsg = '添加失败：' + ((response.error && response.error.message) ? response.error.message : '');
						toastr.error(errorMsg);
					}
				}, 'json');

            }
        });

        validator.addItem({
            element: '[name="category[name]"]',
            required: true,
            rule: 'maxlength{max:10}'
        });

        validator.addItem({
            element: '[name="category[code]"]',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '[name="category[weight]"]',
            required: false,
            rule: 'max{max: 99999}'
        });



	};

});