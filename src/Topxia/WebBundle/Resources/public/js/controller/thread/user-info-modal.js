define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    var $modal = $('#join-event-form').parents('.modal');

    exports.run = function() {
        $form = $('#join-event-form');
        var validator = new Validator({
            element: '#join-event-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                submitForm($form);
            }
        });
        validator.addItem({
            element: '[name="truename"]',
            rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}',
            required: true
        });
        validator.addItem({
            element: '[name="mobile"]',
            rule: 'phone',
            required: true
        });


        $('body').keypress(function(e) {
            if((e.which == 10 || e.which == 13) && ($modal.find('.disabled').length == 0)) {
                validator.execute(function(error, results, element){
                    if (error) {
                        return false;
                    }
                });
            }
        });
        function submitForm($form)
        {
            $modal.find('[type=submit]').button('loading').addClass('disabled');
            $.post($form.attr('action'), $form.serialize(), function(result){
                window.location.reload();
            });
        }
    };

});