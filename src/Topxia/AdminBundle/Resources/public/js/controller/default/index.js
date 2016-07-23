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
        noticeModal();
        step1();
    };

    function step1() {
        $("#popular-courses-type").on('change', function() {
            $.get($(this).data('url'), {dateType: this.value}, function(html) {
                $('#popular-courses-table').html(html);
                step2();
            });
        }).trigger('change');
    }

    function step2() {
        $.post($('#operation-analysis-title').data('url'),function(html){
            $('#operation-analysis-table').html(html);
            step3();
        });
    }

    function step3() {
        $.post($('#system-status-title').data('url'),function(html){
            $('#system-status').html(html);

            $('.mobile-customization-upgrade-btn').click(function() {
                var $btn = $(this).button('loading');
                var postData = $(this).data('data');
                $.ajax({ 
                    url: $(this).data('url'),
                    data: postData,
                    type: 'post'
                }).done(function(data) {
                    $('.upgrade-status').html('<span class="label label-warning">升级受理中</span>');
                }).fail(function(xhr, textStatus) {
                    Notify.danger(xhr.responseJSON.error.message);
                }).always(function(xhr, textStatus) {
                    $btn.button('reset');
                });
            })

            step4();
        });
    }

    function step4() {
        $.post($('#onlineNum').data('url'),function(res){
            $('#onlineNum').html("当前在线："+res.onlineCount+"人");
            step5();
        });
    }

    function step5() {
        $.post($('#loginNum').data('url'),function(res){
            $('#loginNum').html("登录人数："+res.loginCount+"人");
        });
    }

    function noticeModal() {
        var noticeUrl = $('#admin-notice').val();
        $.post(noticeUrl, function(data){
            if (data['result']) {
                $('.modal').html(data['html']);
                $('.modal').modal({
                    backdrop:'static',
                    show:true
                });
            }
        })
    }

});