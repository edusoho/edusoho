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


/*        var $alert = $("#app-upgrade-alert");
        $.post($alert.data('url'), function(result) {
            var count = parseInt(result);
            if (count == 0) {
                return ;
            }
            var html = "<a href='" + $alert.data('upgradeUrl') + "'>亲爱的用户，系统现在有 <span class='badge'>" + count + " </span> 个更新,请及时去应用中心检查查看，体验最新的功能和改进。</a>";
            $alert.append(html);
            $alert.removeClass('hide');
        });*/
        
        $.ajax({
            url: $('#operation-analysis-title').data('url'),
            type: 'POST',
            async: false,
            success: function(html){
                $('#operation-analysis-table').html(html);
            }
        });

/*        $.post($('#open-message-title').data('url'),function(html){
            $('#edusoho-open-message').html(html);
        });*/

        $.ajax({
            url: $('#system-status-title').data('url'),
            type: 'POST',
            async: false,
            success: function(html){
                $('#system-status').html(html);
            }
        });

        $.ajax({
            url: $('#onlineNum').data('url'),
            type: 'POST',
            async: false,
            success: function(res){
                $('#onlineNum').html("当前在线："+res.onlineCount+"人");
            }
        });

        $.ajax({
            url: $('#loginNum').data('url'),
            type: 'POST',
            async: false,
            success: function(res){
                $('#loginNum').html("登录人数："+res.loginCount+"人");
            }
        });
    };

});