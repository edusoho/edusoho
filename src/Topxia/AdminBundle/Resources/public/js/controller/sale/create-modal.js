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
            rule: 'integer remotePost',
            hideMessage:function(msg,ele,eve){
               
                if(null != msg ){
                    $("#prod_info").html(msg);
                    $("#prod_info").addClass('text-color-green');
                }
            }
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