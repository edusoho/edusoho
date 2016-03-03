define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#category-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
                element: $form,
                autoSubmit: false,
                
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            errormessageRequired: '请输入自定义名称，不能为空。'
        });


        
    };

});