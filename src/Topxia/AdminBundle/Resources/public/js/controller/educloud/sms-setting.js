define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('echarts-debug');


    exports.run = function() {
        for (var i = 17; i >= 1; i--) {
            var id = '#article-property-tips' + i;
            var htmlId = id + '-html';
            $(id).popover({
                html: true,
                trigger: 'hover', //'hover','click'
                placement: 'left', //'bottom',
                content: $(htmlId).html()
            });
        };

        var validateSmsControllerForm = function() {
            var validator = new Validator({
                element: '#sms-controller-form'
            });
            validator.addItem({
                element: '[name="sms_school_name"]',
                required: true,
                rule: 'chinese_alphanumeric minlength{min:3} maxlength{max:8}',
                display: Translator.trans('签名'),
                errormessageRequired: Translator.trans('签名3-8字，建议使用汉字')
            });
        }

        if ($('#sms-form').length > 0) {

            $('[name="sms-close"]').click(function() {
                var registerMode = $('input[name="register-mode"]').val();
                if (registerMode == 'email_or_mobile' || registerMode == 'mobile') {
                    $('[name="sms_enabled"][value=1]').prop('checked', true);
                    Notify.danger(Translator.trans('您启用了手机注册模式，不可关闭短信功能！'));
                    return false
                }
            });

        }
        $("[name='sign-update']").on('click', function() {
            $("[name='submit-sign']").show();
            $("[name='status']").hide();
            validateSmsControllerForm();

        });

        $("[name='sms-open']").on('click', function() {
            validateSmsControllerForm();
        });


        //改版图表
        var smsSendChart = echarts.init(document.getElementById('smsSendChart'));
         var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:['时间']
            },
            xAxis: {
                data: ["2016/02","2016/03","2016/04","2016/05","2016/05","2016/06"]
            },
            yAxis: {},
            series: [{
                name: '发送量(条)',
                type: 'bar',
                data: [50, 220, 136, 110, 10, 90]
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        smsSendChart.setOption(option);
    }

});