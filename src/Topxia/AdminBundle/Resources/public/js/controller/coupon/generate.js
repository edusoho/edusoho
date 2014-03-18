define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
	require('jquery.bootstrap-datetimepicker');
	require('common/validator-rules').inject(Validator);
    require('jquery.form');

	exports.run = function (){

		$form = $('#coupon-generate-form');

        $form.find('[name="type"]:checked').trigger('change');

        $form.find('[name="course"]:checked').trigger('change');

        var validator = new Validator({
            element: $form ,
            autoSubmit: true
        });

        $form.on('change', '[name=course]', function(e){
            var target = $('#choose-course-input').is(':checked');
            if (!target) {
                $('#course-display').hide();
            }
        });

        $form.on('click', '#all-courses-input', function(e){
            $('#course-display').hide();
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
		        	rule:'max{max:10} min{min:1} currency'
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
        	required: true,
        	rule: 'remote alphanumeric'
        });

        validator.addItem({
        	element: '[name="generatedNum"]',
        	required: true,
        	rule: 'max{max:1000} min{min:1} positive_integer'
        });

        validator.addItem({
        	element: '[name="digits"]',
        	required: true,
        	rule: 'max{max:15} min{min:5} positive_integer'
        });

        validator.addItem({
        	element: '[name="deadline"]',
        	required: true
        });
        
        $form.find('[name=type]:checked').change();

        $("#coupon-deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

	};
});