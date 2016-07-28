define(function(require, exports, module){
    var Notify = require('common/bootstrap-notify');
    exports.run = function () {
        $('.js-attachment-delete').on('click', function () {
            var attachment_remove =confirm("确定要删除附件吗?")
            if(attachment_remove){
                $.post($(this).data('url'), function (result) {
                    if(result.msg == 'ok'){
                        Notify.success('附件已删除');
                    }
                })
            }
        })
    }
});