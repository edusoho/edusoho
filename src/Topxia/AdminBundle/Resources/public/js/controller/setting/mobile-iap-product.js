define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $("[data-role=delete]").on('click', function(){
            if (!confirm(Translator.trans('admin.setting.mobile_iap_delete_hint'))) return false;
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.setting.mobile_iap_delete_success_hint'));
                window.location.reload();
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.mobile_iap_delete_fail_hint'));
            });
        });

    };

});
