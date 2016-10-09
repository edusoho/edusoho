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
        // step1();
        getData();

    };

    var getData = function() {
        
        popularCoursesInit()
        .then(operationData())
        .then(systemStatusData())
        .then(onlineNum())
        .then(loginNum())
    }

    var popularCoursesInit = function() {
        var $popularCoursesType = $("#popular-courses-type");
        var ajax = $.get($popularCoursesType.data('url'), {dateType: $popularCoursesType.val()}, function(html) {
            $('#popular-courses-table').html(html);
        });

        return ajax;
    }

    var popularCoursesData = function () {
        $("#popular-courses-type").on('change', function() {
            $.get($(this).data('url'), {dateType: this.value}, function(html) {
                $('#popular-courses-table').html(html);
            });
        });
    }

    var operationData = function() {
        $.post($('#operation-analysis-title').data('url'),function(html){
            $('#operation-analysis-table').html(html);
        });
    }

    var systemStatusData = function() {
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
        $.post($('#onlineNum').data('url'),function(res){
            $('#onlineNum').html(Translator.trans('当前在线：%res%人',{res:res.onlineCount}));
        });
    }

    var loginNum = function() {
        $.post($('#loginNum').data('url'),function(res){
            $('#loginNum').html(Translator.trans('登录人数：%res%人',{res:res.loginCount}));
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