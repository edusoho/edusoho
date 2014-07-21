define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    	var $form = $('#field-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#add-btn').button('submiting').addClass('disabled');
            },
		});
        

        validator.addItem({
            element: '[name="field_title"]',
            required: true,
            rule:'minlength{min:2} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="field_seq"]',
            required: true,
            rule:'positive_integer'
        });

        validator.addItem({
            element: '[name="field_type"]',
            required: true,
            errormessageRequired: '请选择字段类型'
        });


        $('#field_type').on('change',function(){
            
            $('#type_num').html($(this).children('option:selected').attr('num'));
        });

    };

});