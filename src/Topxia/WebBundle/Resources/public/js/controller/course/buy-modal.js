define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#course-buy-form',
            autoSubmit: true
        });
        if ($('#course-buy-form').find('input[name="mobile"]').length > 0){
            validator.addItem({
                element: '[name="mobile"]',
                rule: 'phone',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="truename"]').length > 0){
            validator.addItem({
                element: '[name="truename"]',
                rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="qq"]').length > 0){
            validator.addItem({
                element: '[name="qq"]',
                rule: 'qq'
            });
        }

    };

});