define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#approval-form'
        });

       

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese byte_minlength{min:4} byte_maxlength{max:50}'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'phone'
        });

        validator.addItem({
            element: '[name=email2]',
            required: true,
            rule: 'email'
        });


        validator.addItem({
            element: '[name="postAddr"]',
            required: true
           
        });

        validator.addItem({
            element: '[name="company"]',
            required: true
           
        });

        validator.addItem({
            element: '[name="job"]',
            required: true
           
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule : 'idcard'
        });

        validator.addItem({
            element: '[name="faceImg"]',
            required: true
        });

        validator.addItem({
            element: '[name="backImg"]',
            required: true
        });

          validator.addItem({
            element: '[name="headImg"]',
            required: true
        });

   

    };

});