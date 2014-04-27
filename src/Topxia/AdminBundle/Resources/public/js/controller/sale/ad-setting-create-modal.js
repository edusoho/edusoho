define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
  

    exports.run = function() {
        var $form = $('#ad-setting-form');

        var validator = new Validator({
            element: $form,
            autoSubmit: true
        });

        if ($('#ad-setting-form').find('input[name="targetUrl"]')){
            validator.addItem({
                element: '[id][name="targetUrl"]',
                required: true
                   
             });
        }

        if ($('#ad-setting-form').find('input[name="showUrl"]')){
            validator.addItem({
                element: '[id][name="showUrl"]',         
                required: true
              
            });
        }

       
    };

});