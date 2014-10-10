define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $('#user-create-form');
        var $modal= $('#user-create-form').parent('.modal');
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

        Validator.addRule('numberUnique', function(options) {
            var v = options.element.val();
            var result=0;
            $numberList=$form.find('.childNumber');
            for(var i=0;i<$numberList.length;i++){
                if($numberList.eq(i).val()==v){
                    result++;
                }
            }
            return result==1;
        }, '填写的学号不可重复');

        $form.on('click', '#addNumberBtn', function() {
            var $numberInputNum=$form.find("#numberInputNum");
            var $count=parseInt($numberInputNum.val())+1;
            var $newObject = $(this).parent().parent().prev().clone();

            
            $newObject.find('label').attr('for','number_'+$count);
            $newObject.find('label').hide();
            $newObject.find('.childNumber').attr('id','number_'+$count);

            $newObject.find('.childNumber').val('');
            $(this).parent().parent().before($newObject);

            validator.addItem({
                element: $newObject.find('.childNumber'),
                required: true,
                rule: 'remote numberUnique'
            });
            
            $numberInputNum.val($count);
        });

        $form.delegate('.deleteNumberBtn','click',function() {
            if($form.find('.childNumber').length>1){
                var $divObject=$(this).parent().parent();
                if($divObject.find('label').is(":visible")){
                    $divObject.next().find('label').show();
                }
                validator.removeItem($divObject.find('.childNumber'));
                $divObject.remove();
            }
        });

        validator.addItem({
            element: $form.find('#number_1'),
            required: true,
            rule: 'remote numberUnique'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'remote phone'
        });

        validator.addItem({
            element: '[name="relation"]',
            required: true
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="email"]',
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