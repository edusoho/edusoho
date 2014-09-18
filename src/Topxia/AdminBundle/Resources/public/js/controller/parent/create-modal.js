define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $('#user-create-form');
        var $modal= $('#user-create-form').parent('.modal');
        console.log($form.html());
        var validator = new Validator({
            element: '#user-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#user-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('新用户添加成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('新用户添加失败');
                });

            }
        });

        $form.on('click', '#addNumberBtn', function() {
            var $numberInputNum=$modal.find("#numberInputNum");
            console.log($numberInputNum.val());
            $(this).before("<input type='text' id='number' name='numbers["+$numberInputNum.val()+"]' class='form-control' >");
            $numberInputNum.val(parseInt($numberInputNum.val())+1);
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'remote phone'
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="email"]',
            required: true,
            rule: 'email email_remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#password}'
        });
    };

});