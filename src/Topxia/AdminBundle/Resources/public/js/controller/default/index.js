define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        $('.tbody').on('click', 'button.remind-teachers', function() {
            $.post($(this).data('url'), function(response) {
                Notify.success('提醒教师的通知，发送成功！');
            });
        });
        $("#popular-courses-type").on('change', step1);

        step1();
        step2();
        step3();
        step4();
        step5();
    };

    function step1() {
        $.ajax({
            url: $(this).data('url'), 
            data: {dateType: this.value}, 
            async : false, 
            success: function(html) {
                $('#popular-courses-table').html(html);
            }
        });
    }

    function step2() {
        $.ajax({
            url: $('#operation-analysis-title').data('url'),
            async : false,
            type : "post",
            success: function(html){
                $('#operation-analysis-table').html(html);
            }
        });
    }

    function step3() {
        $.ajax({
            url: $('#system-status-title').data('url'),
            type : "post",
            async : false,
            success: function(html){
                $('#system-status').html(html);
            }
        });
    }

    function step4() {
        $.ajax({
            url: $('#onlineNum').data('url'),
            type : "post",
            async : false,
            success: function(res){
                $('#onlineNum').html("当前在线："+res.onlineCount+"人");
            }
        });
    }

    function step5() {
        $.ajax({
            url: $('#loginNum').data('url'),
            type : "post",
            async : false,
            success: function(res){
                $('#loginNum').html("登录人数："+res.loginCount+"人");
            }
        });
    }

});