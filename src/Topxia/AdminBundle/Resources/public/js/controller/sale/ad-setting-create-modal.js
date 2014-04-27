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
                required: true,
                rule: 'remotePost',
                hideMessage:function(msg,ele,eve){               
                    if(null != msg ){
                        $("#targetUrl_info").html(msg);
                        $("#targetUrl_info").addClass('text-color-green');
                    }
                }          
             });
        }

        if ($('#ad-setting-form').find('input[name="showUrl"]')){
            validator.addItem({
                element: '[id][name="showUrl"]',         
                required: true,
                rule: 'url'
            });
        }

       
    };

});