define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {

        require('../../../../topxiaweb/js/controller/course-manage/header').run();

        var validator = new Validator({
            element: '#course-next-round-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name=startTime]',
            required: true
        });

        validator.addItem({
            element: '[name=endTime]',
            required: true,
            rule: 'date_check'
        });

        var now = new Date();

        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function(ev){
            validator.query('[name=startTime]').execute();
        });

        $('[name=startTime]').datetimepicker('setStartDate', now);

        $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function(ev){
            validator.query('[name=endTime]').execute();
        });

        $('[name=endTime]').datetimepicker('setStartDate', now);

    };

});