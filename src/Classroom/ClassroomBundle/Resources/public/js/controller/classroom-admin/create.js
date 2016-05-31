define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        var validator = new Validator({
            element: '#classroom-create-form',
            onFormValidated: function(error) {
                if (error) {
                    return false;
                }
                $('#classroom-create-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:30}',
            errormessageUrl: '长度为2-30位'
        });

    };

});