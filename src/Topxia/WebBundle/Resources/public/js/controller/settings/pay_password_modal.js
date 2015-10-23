define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);
    Validator.addRule('passwordCheck', function(options, commit) {
        var element = options.element;
        var url = options.url ? options.url : (element.data('url') ? element.data('url') : null);

        $.post(url, {value:element.val()}, function(response) {
            commit(response.success, response.message);
        }, 'json');
    });

    exports.run = function() {
        var payPasswordValidator = new Validator({
            element: '#settings-pay-password-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#password-save-btn').button('submiting').addClass('disabled');
            }
        });

        payPasswordValidator.addItem({
            element: '[name="form[currentUserLoginPassword]"]',
            required: true,
            triggerType: 'submit',
            rule: 'passwordCheck'
        });

        payPasswordValidator.addItem({
            element: '[name="form[newPayPassword]"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        payPasswordValidator.addItem({
            element: '[name="form[confirmPayPassword]"]',
            required: true,
            rule: 'confirmation{target:#form_newPayPassword}'
        });

        $('.js-submit-form').unbind('click');
        $('.js-submit-form').click(function(){
            payPasswordValidator.execute(function(error, results, element) {
                if (error) {
                    return;
                }
                var data = $("#settings-pay-password-form").serialize();
                var targetUrl = $("#settings-pay-password-form").attr("action");
                $.post(targetUrl, data, function(res) {
                    
                    if (res['ACK'] == 'fail') {
                        Notify.danger(res['message']);
                    }
                    if (res['ACK'] == 'success') {
                        Notify.success(res['message']);
                        setTimeout(function() {
                            window.location.reload();
                        },1000);
                    }

                });
            });
        });
    };

});