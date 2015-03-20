define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm('确认要删除此导航吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    Notify.success('删除成功!');
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                } else {
                    alert('服务器错误!');
                }
            }, 'json');

        });
    };

});