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
                      labels: ['购买课程数'],
                      xLabels:"day",
                    });
        }


         $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
        $('[name=endTime]').datetimepicker('setEndDate', now);
        $('[name=endTime]').datetimepicker('setStartDate', $('#paidLessonStartDate').attr("value"));
        
        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
        $('[name=startTime]').datetimepicker('setEndDate', now);
        $('[name=startTime]').datetimepicker('setStartDate', $('#paidLessonStartDate').attr("value"));

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

        $("[name=analysisDateType]").on("change",function(){

                switch($("[name=analysisDateType]").val())
                {
                    case "register":
                    window.location.href=$("[name=register]").attr("value");break;
                    case "login":
                    window.location.href=$("[name=login]").attr("value");break;
                    case "course":
                    window.location.href=$("[name=course]").attr("value");break;
                    case "lesson":
                    window.location.href=$("[name=lesson]").attr("value");break;
                    case "joinLesson":
                    window.location.href=$("[name=lesson_join]").attr("value");break;
                    case "paidLesson":
                    window.location.href=$("[name=lesson_paid]").attr("value");break;
                    case "finishedLesson":
                    window.location.href=$("[name=lesson_finished]").attr("value");break;
                    case "videoViewed":
                    window.location.href=$("[name=video_viewed]").attr("value");break;
                    case "cloudVideoViewed":
                    window.location.href=$("[name=video_cloud_viewed]").attr("value");break;
                    case "localVideoViewed":
                    window.location.href=$("[name=video_local_viewed]").attr("value");break;
                    case "netVideoViewed":
                    window.location.href=$("[name=video_net_viewed]").attr("value");break;
                    case "income":
                    window.location.href=$("[name=income]").attr("value");break;
                    case "courseIncome":
                    window.location.href=$("[name=course_income]").attr("value");break;
                    case "exitLesson":
                    window.location.href=$("[name=lesson_exit]").attr("value");break;
                }

        });
    };

});