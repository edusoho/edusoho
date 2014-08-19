define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.select2-css');
    require('jquery.select2');
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    
        require('./header').run();

        var validator = new Validator({
            element: '#price-form',
            failSilently: true,
            triggerType: 'change',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $form = $('#price-form');
                if($('#freeStartTime').length > 0){
                    var startTime = $('#freeStartTime').val();
                    startTime = startTime.replace(/-/g,"/");
                    startTime = Date.parse(startTime)/1000;
                    var endTime = $('#freeEndTime').val();
                    endTime = endTime.replace(/-/g,"/");
                    endTime = Date.parse(endTime)/1000;
                    var nowTime = Date.parse(new Date())/1000;

                    if(startTime > endTime){
                        Notify.danger('请输入一个小于结束时间的开始时间');
                        $('#freeStartTime').focus();
                        return false;
                    }
                }   

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    Notify.success('课程价格已经修改成功');
                }).error(function(){
                    Notify.danger('操作失败');
                });;
            }
        });

    $("#freeStartTime").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        language: 'zh-CN',
        todayBtn: true,
        autoclose: true,
        startDate: new Date(),
        todayHighlight: true,
        forceParse: false
    });

    $("#freeEndTime").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        language: 'zh-CN',
        todayBtn: true,
        autoclose: true,
        startDate: new Date(),
        todayHighlight: true,
        forceParse: false
    });    

    Validator.addRule('time_check',
        function(a) {
            var thisTime = $(a.element.selector).val();
            thisTime = thisTime.replace(/-/g,"/");
            if (!Date.parse(thisTime)) {
            return false;
            }else{
            return true;
            }
        },"请输入一个正确的时间");    

    validator.addItem({
        element: '[name="price"]',
        rule: 'currency'
    });

    validator.addItem({
        element: '[name="freeStartTime"]',
        rule: 'time_check'
    });

    validator.addItem({
        element: '[name="freeEndTime"]',
        rule: 'time_check'
    });

    };

});