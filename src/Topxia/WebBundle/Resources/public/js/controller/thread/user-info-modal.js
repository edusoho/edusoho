define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    var $modal = $('#join-event-form').parents('.modal');

    exports.run = function() {
        var validator = new Validator({
            element: '#join-event-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $modal.find('[type=submit]').button('loading').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(result){
                    window.location.reload();
                });
            }
        });

        validator.addItem({
            element: '[name="mobile"]',
            rule: 'phone'
        });

        validator.addItem({
            element: '[name="truename"]',
            rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}'
        });

    };

});