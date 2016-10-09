define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        $('.tbody').on('click', 'js-remind-teachers', function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('提醒教师的通知，发送成功！'));
            });
        });
        //noticeModal();
        getData();

    };


    var getData = function() {
        initData()
        .then(operationData)
        .then(systemStatusData)
        .then(onlineNum)
        .then(loginNum)
    }


    var initData = function() {
        var $popularCoursesType = $("#popular-courses-type");
        return $.get($popularCoursesType.data('url'), {dateType: $popularCoursesType.val()}, function(html) {
            $('#popular-courses-table').html(html);
        });
    }

    var popularCoursesData = function () {
        $("#popular-courses-type").on('change', function() {
            $.get($(this).data('url'), {dateType: this.value}, function(html) {
                $('#popular-courses-table').html(html);
            });
        });
    }

    var operationData = function() {
        return $.post($('#operation-analysis-title').data('url'),function(html){
            $('#operation-analysis-table').html(html);
        });
    }

    var systemStatusData = function() {
        return $.post($('#system-status-title').data('url'),function(html){
            $('#system-status').html(html);

            $('.mobile-customization-upgrade-btn').click(function() {
                var $btn = $(this).button('loading');
                var postData = $(this).data('data');
                $.ajax({ 
                    url: $(this).data('url'),
                    data: postData,
                    type: 'post'
                }).done(function(data) {
                    $('.upgrade-status').html('<span class="label label-warning">'+Translator.trans('升级受理中')+'</span>');
                }).fail(function(xhr, textStatus) {
                    Notify.danger(xhr.responseJSON.error.message);
                }).always(function(xhr, textStatus) {
                    $btn.button('reset');
                });
            })

        });
    }

    var onlineNum = function() {
        return $.post($('#onlineNum').data('url'),function(res){
            $('#onlineNum').html(Translator.trans('当前在线：%res%人',{res:res.onlineCount}));
        });
    }

    var loginNum = function() {
        return $.post($('#loginNum').data('url'),function(res){
            $('#loginNum').html(Translator.trans('登录人数：%res%人',{res:res.loginCount}));
        });
    }


    function noticeModal() {
        var noticeUrl = $('#admin-notice').val();
        return $.post(noticeUrl, function(data){
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