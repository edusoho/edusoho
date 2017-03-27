define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $("[data-role=delete]").on('click', function(){
            if (!confirm(Translator.trans('确认要删除吗？'))) return false;
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('删除成功'));
                window.location.reload();
            }).error(function(){
                Notify.danger(Translator.trans('删除失败'));
            });
        });

    };

});
