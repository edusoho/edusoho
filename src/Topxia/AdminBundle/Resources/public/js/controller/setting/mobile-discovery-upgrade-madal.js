define(function (require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function () {

        $("#next").click(function(){
            $(".step1").addClass('hide');
            $(".step2").removeClass('hide');
        });

        $("#upgrade").click(function(){
            $(this).button('loading');
            var url = $(this).data('url');
            var successUrl = $(this).data('successUrl');
            $.post(url, function (res) {
                if ('successed' == res.status) {
                    Notify.success(Translator.trans('admin.mobile_manage.discovery_upgrade_madal.notice_successed'));
                    window.location.href = successUrl;
                } else if ('upgraded' == res.status) {
                    Notify.danger(Translator.trans('admin.mobile_manage.discovery_upgrade_madal.notice_upgraded'));
                    window.location.href = successUrl;
                } else if ('failed' == res.status) {
                    Notify.danger(Translator.trans('admin.mobile_manage.discovery_upgrade_madal.notice_failed'));
                }
            }).error(function () {
                Notify.danger(Translator.trans('admin.mobile_manage.discovery_upgrade_madal.notice_failed'));
            });
        });

    };

});