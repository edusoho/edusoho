define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

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

        var $block = $("#noftificationBlock");
        
        $.post($block.data('url'), function(response) {
            if(!response){
                return;
            }
            $("#upgradeNoftification").html("亲爱的用户，系统现在有 <span class='badge'>" + response + " </span> 个更新,请及时去系统安装与升级中心更新，体验最新的功能和改进。");
            $block.removeClass('hide');

        });

    };

});