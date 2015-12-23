define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

		var $modal = $('#write-invite-code').parents('.modal');
        var url = $('#create-btn').data('url');

        var validator = new Validator({
            element: '#write-invite-code',
            autoSubmit: false,
            onFormValidated: function(error, results, $form){
                if (error) {
                    return false;
                }

                $('#create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(response) {                  
                    if(response.success == true){
                        $modal.modal('hide');
                        window.location.href= url;
                    }else{
                        Notify.warning(response.message, 1);
                        setTimeout(function(){
                            window.location.reload();
                        },1000);
                    }
                });
            }
        });

        validator.addItem({
            element: '[name="inviteCode"]',
            required: false,
            rule: 'reg_inviteCode',
            display: '邀请码'
        });
    }
});