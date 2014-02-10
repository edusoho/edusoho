define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('jquery.bootstrap-datetimepicker');

    exports.run = function() {
        var $form = $('#linksale-web-form');
      

        var validator = new Validator({
            element: $form,
            autoSubmit: true
        });    

        validator.addItem({
            element: '[name="webCommission"]',
            required: true,
            rule: 'integer  min{min:0} max{max:100}'
        });


        validator.addItem({
            element: '[name="webCommissionDay"]',
            required: true,
            rule: 'integer  min{min:1} max{max:100}'
        });

    };

});