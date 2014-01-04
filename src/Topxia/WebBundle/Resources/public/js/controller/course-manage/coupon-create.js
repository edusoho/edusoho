define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $form = $('#coupon-create-form');
        var $modal = $('#coupon-create-form').parents('.modal');
        var $table = $('#course-coupon-list');

        var validator = new Validator({
            element: '#coupon-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('优惠码添加成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('优惠码添加失败');
                });

            }
        });

        $form.find('[name="type"]:checked').trigger('change');

        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();
            var discount = $('#discount-way');
            var minus = $('#minus-way');

            if (type == 'discount') {
                discount.show();
                minus.hide();

            } else if (type == 'minus') {
                minus.show();
                discount.hide();
            }
        });

        validator.addItem({
            element: '#coupon-rate',
            required: true,
            rule: 'integer'
        });

        validator.addItem({
            element: '#coupon-times',
            required: true,
            rule: 'integer'
        });

        validator.addItem({
            element: '#coupon-number',
            required: true,
            rule: 'integer'
        });

    };

});