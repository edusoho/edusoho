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
            element: '[name="adCommission"]',
            required: true,
            rule: 'currency  min{min:0} max{max:100}'
        });

        validator.addItem({
            element: '[name="adCommissionDay"]',
            required: true,
            rule: 'integer  min{min:0} max{max:100}'
        });

        validator.addItem({
            element: '[name="reducePrice"]',
            required: true,
            rule: 'currency  min{min:0} max{max:100}'
        });
       

        validator.addItem({
            element: '[name="strvalidTime"]',         
            required: false,
            rule: 'datetime-i'
        });


        //日期时间选择
        $('#strvalidTime').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            language: 'zh-CN',
            pickDate: true,
            pickTime: true,
            hourStep: 1,
            minuteStep: 15,
            secondStep: 15,
            inputMask: true,
            autoclose: true
      });

    };

});