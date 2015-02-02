define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        $('body').on('click', '.js-topLink-delete-btn', function() {
            if (!confirm('确认要删除顶部链接设置吗？')){
                return false;
            }
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response) {
                    Notify.success('删除成功!');
                    window.location.reload();
                } else {
                    alert('服务器错误!');
                }
            }, 'json');
        });
    };

});