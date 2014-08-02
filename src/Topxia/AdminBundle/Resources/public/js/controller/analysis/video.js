define(function(require, exports, module) {
    var Morris=require("morris");
    require("jquery.bootstrap-datetimepicker");
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var now = new Date();
    exports.run = function() {

        if($('#data').length > 0){
                    var data = eval ("(" + $('#data').attr("value") + ")");

                    Morris.Line({
                      element: 'line-data',
                      data: data,
                      xkey: 'date',
                      ykeys: ['count',],
                      labels: ['观看数'],
                      xLabels:"day",
                    });
        }


         $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

        $minCreatedTime = $('[name=minCreatedTime]').val();
        console.log($minCreatedTime);

        $('[name=endTime]').datetimepicker('setEndDate', now);
        $('[name=endTime]').datetimepicker('setStartDate', $minCreatedTime);
        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });


        $('[name=startTime]').datetimepicker('setEndDate', now);
        $('[name=startTime]').datetimepicker('setStartDate', $minCreatedTime);

        var validator = new Validator({          
            element: '#operation-form'});

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule:'date_check'
        });

        validator.addItem({
            element: '[name=endTime]',
            required: true,
            rule:'date_check'
        });

    Validator.addRule('date_check',
            function() {

                var startTime = $('[name=startTime]').val();
                var endTime = $('[name=endTime]').val();
                startTime = startTime.replace(/-/g,"/");
                startTime = Date.parse(startTime)/1000;
                endTime = endTime.replace(/-/g,"/");
                endTime = Date.parse(endTime)/1000;

                if (endTime >= startTime) {
                    return true;
                }else{
                    return false;
                }
            },"开始时间必须小于或等于结束时间"
        );
    };

});