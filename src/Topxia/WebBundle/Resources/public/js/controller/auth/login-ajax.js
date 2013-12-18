define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#login-ajax-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                $form.find('.alert-danger').hide();

                if (error) {
                    return ;
                }

                $.post($form.attr('action'), $form.serialize(), function(response) {
                    window.location.reload();
                }, 'json').error(function(jqxhr, textStatus, errorThrown) {
                    var json = jQuery.parseJSON(jqxhr.responseText);
                    $form.find('.alert-danger').html(json.message).show();
                });

            }
        });

        validator.addItem({
            element: '[name="_username"]',
            required: true
        });

        validator.addItem({
            element: '[name="_password"]',
            required: true
        });



    };

});