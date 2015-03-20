define(function(require, exports, module) {

    var AutoComplete = require('autocomplete');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');

    Validator.addRule('reward_range',
    function() {

        var amount = $('[name=amount]').val();
        var canUse = $('#canUse').attr('data-val');
        amount=parseInt(amount);

        if (canUse<amount) {
            return false;
        }
        return true;
    },"余额不足!");

    Validator.addRule('reward_check',
    function() {

        var amount = $('[name=amount]').val();
        amount=parseInt(amount);

        if(amount>50){
            return false;
        }
        return true;
    },"悬赏额范围在1-50之间!");

    exports.run = function() {

        var $modal = $('#reward-form').parents('.modal');

        var validator = new Validator({
            element: '#reward-form',
            autoSubmit: false,
            onFormValidated: function(error){
                    if (error) {
                    return false;
                }
 
                $('#create-btn').button('submiting').addClass('disabled');

                $form=$('#reward-form');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    window.location.reload();
                }).error(function(){
                });
            }
        });

        validator.addItem({
            element: '[name="amount"]',
            required: true,
            rule: 'reward_check positive_integer reward_range'
        });



    };

});