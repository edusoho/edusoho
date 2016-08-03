define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        $('.js-attachment-delete').on('click', function() {
            if (confirm("确定要删除附件吗?")) {
                $.post($(this).data('url'))
                    .done(function(result) {
                        if (result.msg == 'ok') {
                            Notify.success('附件已删除');
                            $(this).parents('li').remove();
                        } else {
                            Notify.danger('附件删除失败,请稍后再试');
                        }
                    }).fail(function(ajaxFailed) {
                        Notify.danger('附件删除失败,请稍稍后再试');
                    })
            }
        })
    }
});