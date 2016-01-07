define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        var textarea = "本年课程的最终成绩由三部分组成，考试成绩占比权重为" + $("#score_examWeight").val() + "%，作业成绩权重为" + $("#score_homeworkWeight").val() + "%，其他权重为" + $("#score_otherWeight").val() + "%。\n本课程成绩" + $("#score_standardScore").val() + "分为合格，成绩预计公布时间为" + $("#score_expectPublishTime").val() + "日。\n请大家及时完成作业及考试，以免影响最终成绩。";
        $("#score_template").val(textarea);


        if (app.arguments.readonly) {
            $(':radio:not(:checked)').attr('disabled', true);
        } else {
            $("[name=expectPublishTime]").datetimepicker({
                language: 'zh-CN',
                autoclose: true,
                format: 'yyyy-mm-dd',
                minView: 'month',
            }).on('hide', function(ev) {
                validator.query('[name=expectPublishTime]').execute();
            });
            $('[name=expectPublishTime]').datetimepicker('setStartDate', new Date());
        }
        var validator = new Validator({
            element: '#score-setting-form',
        });
        validator.addItem({
            element: '[name=credit]',
            rule: 'decimal'
        });
        validator.addItem({
            element: '[name=examWeight]',
            rule: 'percent_number maxlength{max:3} noMoreThan',
            errormessageNoMoreThan: '成绩权重只能等于100%'
        });
        validator.addItem({
            element: '[name=homeworkWeight]',
            rule: 'percent_number maxlength{max:3} noMoreThan',
            errormessageNoMoreThan: '成绩权重只能等于100%'
        });
        validator.addItem({
            element: '[name=otherWeight]',
            rule: 'percent_number maxlength{max:3} noMoreThan',
            errormessageNoMoreThan: '成绩权重只能等于100%'
        });
        validator.addItem({
            element: '[name=standardScore]',
            rule: 'percent_number maxlength{max:3}',
            required: true
        });
        validator.addItem({
            element: '[name=expectPublishTime]',
            rule: 'dataAfter',
            required: true,
            errormessageDataAfter: '预发布时间不能小于或等于课程结束时间' + $('[name=course_endTime]').val()
        });
        var now = new Date();


    };
    Validator.addRule('dataAfter', function(options, commit) {
        var startTime = $('[name=course_endTime]').val();
        var endTime = $('[name=expectPublishTime]').val();
        startTime = startTime.replace(/-/g, "/");
        startTime = Date.parse(startTime) / 1000;
        endTime = endTime.replace(/-/g, "/");
        endTime = Date.parse(endTime) / 1000;
        if (endTime <= startTime) {
            return false;
        }
        return true;
    });

    Validator.addRule('noMoreThan', function(options, commit) {
        var fullValue = 0;
        var flag = false;
        var weights = new Array('input[name=examWeight]', 'input[name=homeworkWeight]', 'input[name=otherWeight]');
        $.each(weights, function(index, weight) {
            fullValue += ($(weight).val() == "") ? 0 : parseInt($(weight).val());
        });

        //  if ($("#score_examWeight").val() != "" && $("#score_homeworkWeight").val() != "") {
        flag = fullValue == 100;
        //}

        if (flag) {
            var currentWeight = 'input' + options.element.selector;
            $.each(weights, function(index, weight) {
                if (currentWeight != weight) {
                    $(weight).next().empty().parent().parent().removeClass('has-error');
                }
            });
        }
        return flag;
    });
});