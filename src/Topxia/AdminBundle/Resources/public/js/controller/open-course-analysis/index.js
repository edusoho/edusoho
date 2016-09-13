define(function (require, exports, module) {
    "use strict";
    var Notify = require('common/bootstrap-notify');
    require('jquery.bootstrap-datetimepicker');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var moment = require('moment');
    var date2Str = function (date) {
        if (!date instanceof Date) {
            return null;
        } else {
            var dateWrapper = moment(date);
            return dateWrapper.format("YYYY-MM-DD");
        }
    };

    var prevYear = function (date_str) {
        var date = new Date(date_str);
        date.setFullYear(date.getFullYear() - 1);
        date.setDate(date.getDate() + 1);
        return date2Str(date);
    };
    
    var nextYear = function (date_str) {
        var date = new Date(date_str);
        date.setFullYear(date.getFullYear() + 1);
        date.setDate(date.getDate() - 1);
        return date2Str(date);
    };
    
    var addDateRule = function () {
        Validator.addRule(
            'date_range_with_year',
            function (options, commit) {
                var date = Date.parse(options.element.val());
                var otherDate = Date.parse(options.element.siblings('.datetimepicker-input').val());
                var startTime = otherDate > date ? date : otherDate;
                var endTime = otherDate > date ? otherDate : date;
                var yearTime = 3600 * 24 * 365 * 1000;
                if (endTime - startTime <= yearTime) {
                    return true;
                } else {
                    Notify.danger('日期跨度不可超过一年', 2);
                    return false;
                }

            }
        );
    };

    exports.run = function () {
        var $startTime = $('#startTime');
        var $endTime = $("#endTime");
        addDateRule();
        $startTime.datetimepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 2
        }).on('changeDate', function () {
            $endTime.datetimepicker('setStartDate', $startTime.val());
            $endTime.datetimepicker('setEndDate', nextYear($startTime.val()));
        });

        $startTime.datetimepicker('setEndDate', $endTime.val());

        $endTime.datetimepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 2
        }).on('changeDate', function () {
            $startTime.datetimepicker('setEndDate', $endTime.val());
            $startTime.datetimepicker('setStartDate', prevYear($endTime.val()));
        });

        $endTime.datetimepicker('setStartDate', $startTime.val());
        var $form = $('#refererlog-search-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function (error, results, $form) {
                if (error) {
                    return false;
                }
                return false;
            },
            failSilently: true
        });

        validator.addItem({
            element: $startTime,
            required: true,
            rule: 'date_range_with_year'
        });

        validator.addItem({
            element: $endTime,
            required: true,
            rule: 'date_range_with_year'
        });

        $form.on('click', '.btn-data-range', function () {
            $('.btn-data-range').removeClass('active');
            $(this).addClass('active');
            $startTime.val($(this).data('start'));
            $endTime.val($(this).data('end'));
            $endTime.datetimepicker('setStartDate', $startTime.val());
            $("input[name='date-range']").val($(this).data('type'));
            $form.submit();
        });
    }
});