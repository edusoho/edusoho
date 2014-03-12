define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function (){
		$form = $('#coupon-generate-form');

        $form.find('[name="type"]:checked').trigger('change');

        var validator = new Validator({
            element: $form ,
            autoSubmit: false
/*            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('优惠码添加成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('优惠码添加失败');
                });

            }*/
        });

        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();
            var minus = $('.minus-rate');
            var discount = $('.discount-rate');

            if (type == 'minus') {
                minus.show();
                discount.hide();
		        validator.addItem({
		        	element: '[name="minus-rate"]',
		        	required: true,
		        	rule:'currency'
		        });
                validator.removeItem('[name="discount-rate"]');
            } else if (type == 'discount') {
                discount.show();
                minus.hide();
		        validator.addItem({
		        	element: '[name="discount-rate"]',
		        	required: true,
		        	rule:'currency'
		        });
                validator.removeItem('[name="minus-rate"]');
            }
        });

        validator.addItem({
            element: '#name',
            required: true
        });

        validator.addItem({
        	element: '[name="prefix"]',
        	required: true/*,
        	rule: 'remote'*/
        });

        $form.find('[name=type]:checked').change();



	};
});