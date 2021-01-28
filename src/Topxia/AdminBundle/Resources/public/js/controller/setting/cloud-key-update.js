define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');

    exports.run = function() {


        var $form = $('#key-update-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $form.find('.save-btn').button('loading');
            }
        });
        
        validator.addItem({
            element: '#access-key-field',
            required: true,
            rule:'required'
        });

        validator.addItem({
            element: '#secret-key-field',
            required: true,
            rule:'required'
        });

        var $btn = $("#key-apply-btn");

        $btn.click(function() {
            $btn.button('loading');
            $.post($(this).data('url'), function(response) {
            }, 'json').done(function(){
                window.location.href = $btn.data('gotoUrl');
            });
        });

    }

})