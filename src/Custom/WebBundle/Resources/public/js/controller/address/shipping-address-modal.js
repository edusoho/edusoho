define(function(require, exports, module) {
    require("chineserp-jquery");
    require("chineserp-jquery-css");
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function(){

        var $form = $('#shipping-address-from');
        var $modal = $form.parents('.modal');
        /* validator */
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $.post($form.data('url'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    $('#order-verify-form .js-shipping-address').html(html);
                    Notify.success('保存成功');
                }).fail(function(){
                    Notify.danger('保存失败!');
                });

            }
        });

        validator.addItem({
            element: '[name=region]',
            required: true,
            display: '所在地区'
        });

        validator.addItem({
            element: '[name=address]',
            required: true,
            display: '详细地址'
        });

        validator.addItem({
            element: '[name=postCode]',
            required: true,
            rule:'positive_integer',
            display: '邮政编码'
        });

        validator.addItem({
            element: '[name=contactName]',
            required: true,
            display: '收货人姓名'
        });

        validator.addItem({
            element: '[name=mobileNo]',
            required: false,
            rule:'phone',
            display: '手机号码'
        });

        validator.addItem({
            element: '#telNo1',
            required: false,
            rule:'positive_integer',
            display: '区号'
        });

        validator.addItem({
            element: '#telNo2',
            required: false,
            rule:'positive_integer',
            display: '电话号码'
        });

        validator.addItem({
            element: '#telNo3',
            required: false,
            rule:'positive_integer',
            display: '分机'
        });
         
        /* region picker */
        $form.find('[data-role =region-picker]').regionPicker().on('picked.rp', function(e, regions){
          $('[data-role =region-picker]').val(regions.map(function(r){ return r.n; }).join(" "));
        });
    }
});