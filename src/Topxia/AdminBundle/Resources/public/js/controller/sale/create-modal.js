define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('jquery.bootstrap-datetimepicker');

    exports.run = function() {
        var $form = $('#offsale-form');
        var $modal = $form.parents('.modal');
        var $table = $('#offsale-table');

        var validator = new Validator({
            element: $form,
            autoSubmit: true
        });

        validator.addItem({
            element: '[name="promoNum"]',
            required: true,
            rule:'integer min{min:1} max{max:500}'
        });

        validator.addItem({
            element: '[name="reducePrice"]',
            required: true,
            rule: 'currency  min{min:1}'
        });

        validator.addItem({
            element: '[name="prodId"]',         
            required: true,
            rule: 'integer remotePost'
        });


        //日期时间选择
        $('#44strvalidTime').datetimepicker({
            format: 'yyyy-mm-dd',
            language: 'zh-CN',
            pickDate: true,
            pickTime: true,
            hourStep: 1,
            minuteStep: 30,
            secondStep: 30,
            inputMask: true
      });

    };

});