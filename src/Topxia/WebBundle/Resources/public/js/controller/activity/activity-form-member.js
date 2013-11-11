define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);



    exports.run = function() {

        var validator = new Validator({
            element: '#ActivityForm'
        });


        validator.addItem({
            element: '[name="activitymember[mobile]"]',
            required: true,
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="activitymember[truename]"]',
            required: true,
            rule: 'truename byte_minlength{min:2} byte_maxlength{max:12}'
        });

        validator.addItem({
            element: '[name="activitymember[joinMode]"]',
            required: true
           
        });


      

    };

});