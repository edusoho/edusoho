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

        $("#popular-courses-type").on('change', function() {
            $.get($(this).data('url'), {dateType: this.value}, function(html) {
                $('#popular-courses-table').html(html);
            });
        }).trigger('change');

        $.post($('#operation-analysis-title').data('url'),function(html){
            $('#operation-analysis-table').html(html);
        });

/*        $.post($('#open-message-title').data('url'),function(html){
            $('#edusoho-open-message').html(html);
        });*/

        $.post($('#system-status-title').data('url'),function(html){
            $('#system-status').html(html);
        });

        $.post($('#onlineNum').data('url'),function(res){
            $('#onlineNum').html("当前在线："+res.onlineCount+"人");
        });

        $.post($('#loginNum').data('url'),function(res){
            $('#loginNum').html("登录人数："+res.loginCount+"人");
        });
    };

});